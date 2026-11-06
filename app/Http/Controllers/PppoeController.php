<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PppoeAccount;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PppoeController extends Controller
{
    /**
     * Ensure pppoe_accounts table exists in production cloud
     */
    protected function ensureTableExists(): void
    {
        static $checked = false;
        if ($checked) return;
        
        try {
            if (!Schema::hasTable('pppoe_accounts')) {
                Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {}
        
        $checked = true;
    }

    /**
     * Auto-populate sequential PPPoE accounts (Hokigo1, Hokigo2... password Irzaq09042003)
     * isolated per village.
     */
    public function autoPopulateVillageAccounts(?int $villageId = null): void
    {
        try {
            $villageIds = $villageId ? [$villageId] : Village::pluck('id')->toArray();
            if (empty($villageIds)) {
                return;
            }

            foreach ($villageIds as $vId) {
                $existingCount = PppoeAccount::where('village_id', $vId)->count();

                // Find highest Hokigo number among existing customers in this specific village
                $customerUsers = Customer::where('village_id', $vId)
                    ->whereNotNull('pppoe_user')
                    ->where('pppoe_user', '!=', '')
                    ->pluck('pppoe_user');

                $highestNum = 0;
                foreach ($customerUsers as $cu) {
                    if (preg_match('/(?:hokigo)?(\d+)/i', trim((string)$cu), $m)) {
                        $num = (int)$m[1];
                        if ($num > $highestNum) {
                            $highestNum = $num;
                        }
                    }
                }

                $target = max(50, $highestNum + 30);

                if ($existingCount >= $target) {
                    continue;
                }

                $existing = PppoeAccount::where('village_id', $vId)->pluck('username')->all();
                $existingMap = array_change_key_case(array_flip($existing), CASE_LOWER);

                $inserts = [];
                $now = now();

                for ($i = 1; $i <= $target; $i++) {
                    $cand = 'Hokigo' . $i;
                    if (!isset($existingMap[strtolower($cand)])) {
                        $inserts[] = [
                            'village_id' => $vId,
                            'username' => $cand,
                            'password' => 'Irzaq09042003',
                            'notes' => 'Otomatis dibuat sistem',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (!empty($inserts)) {
                    foreach (array_chunk($inserts, 100) as $chunk) {
                        PppoeAccount::insert($chunk);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore to avoid breaking main requests
        }
    }

    /**
     * Display the PPPoE Accounts Directory & Search page
     */
    public function index(Request $request)
    {
        $this->ensureTableExists();

        $villages = Village::orderBy('name')->get();
        $selectedVillageId = $request->get('village_id');
        $statusFilter = $request->get('status', 'available'); // default: available
        $search = trim($request->get('search', ''));

        // Auto-seed initial accounts so user NEVER has to manually click "Buat Massal"
        $this->autoPopulateVillageAccounts($selectedVillageId ? (int)$selectedVillageId : null);

        // Fetch all customers with pppoe_user to map strictly by village_id + username
        $customerQuery = Customer::whereNotNull('pppoe_user')
            ->where('pppoe_user', '!=', '');
        
        if ($selectedVillageId) {
            $customerQuery->where('village_id', $selectedVillageId);
        }

        $allCustomers = $customerQuery->get([
            'id', 'customer_code', 'name', 'phone', 'village_id', 'status', 'pppoe_user'
        ]);

        $villageCustomerMap = [];
        $usedKeys = [];
        foreach ($allCustomers as $cust) {
            $key = $cust->village_id . '_' . strtolower(trim($cust->pppoe_user));
            $villageCustomerMap[$key] = $cust;
            $usedKeys[$key] = true;
        }

        // Build Account Query
        $query = PppoeAccount::with('village:id,name,code');

        if ($selectedVillageId) {
            $query->where('village_id', $selectedVillageId);

            $usedUsernamesInVillage = $allCustomers->pluck('pppoe_user')->map(fn($u) => strtolower(trim($u)))->all();
            
            if ($statusFilter === 'available') {
                if (!empty($usedUsernamesInVillage)) {
                    $query->whereNotIn(DB::raw('LOWER(username)'), $usedUsernamesInVillage);
                }
            } elseif ($statusFilter === 'used') {
                if (!empty($usedUsernamesInVillage)) {
                    $query->whereIn(DB::raw('LOWER(username)'), $usedUsernamesInVillage);
                } else {
                    $query->whereRaw('1 = 0'); // No used accounts
                }
            }
        } else {
            // If viewing all villages and filtering by status
            if ($statusFilter === 'available' || $statusFilter === 'used') {
                // Subquery to ensure village-specific customer matching
                $query->where(function ($q) use ($statusFilter) {
                    if ($statusFilter === 'available') {
                        $q->whereNotExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('customers')
                                ->whereColumn('customers.village_id', 'pppoe_accounts.village_id')
                                ->whereRaw('LOWER(customers.pppoe_user) = LOWER(pppoe_accounts.username)');
                        });
                    } else {
                        $q->whereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('customers')
                                ->whereColumn('customers.village_id', 'pppoe_accounts.village_id')
                                ->whereRaw('LOWER(customers.pppoe_user) = LOWER(pppoe_accounts.username)');
                        });
                    }
                });
            }
        }

        if ($search !== '') {
            $query->where('username', 'like', "%{$search}%");
        }

        // Natural sort by length of username, then alphanumeric
        $pppoeAccounts = $query->orderByRaw('LENGTH(username) ASC, username ASC')
            ->paginate(30)
            ->withQueryString();

        // Attach exact village-specific customer to each account in paginated list
        foreach ($pppoeAccounts as $acc) {
            $key = $acc->village_id . '_' . strtolower(trim($acc->username));
            $acc->setRelation('customer', $villageCustomerMap[$key] ?? null);
        }

        // Accurate statistics counts per village
        $baseCountsQuery = PppoeAccount::query();
        if ($selectedVillageId) {
            $baseCountsQuery->where('village_id', $selectedVillageId);
            $totalCount = (clone $baseCountsQuery)->count();
            
            $usedUsernamesInVillage = $allCustomers->pluck('pppoe_user')->map(fn($u) => strtolower(trim($u)))->all();
            $usedCount = PppoeAccount::where('village_id', $selectedVillageId)
                ->whereIn(DB::raw('LOWER(username)'), $usedUsernamesInVillage)
                ->count();
        } else {
            $totalCount = (clone $baseCountsQuery)->count();
            $usedCount = PppoeAccount::whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('customers')
                    ->whereColumn('customers.village_id', 'pppoe_accounts.village_id')
                    ->whereRaw('LOWER(customers.pppoe_user) = LOWER(pppoe_accounts.username)');
            })->count();
        }

        $availableCount = max(0, $totalCount - $usedCount);
        $selectedVillage = $selectedVillageId ? $villages->firstWhere('id', $selectedVillageId) : null;

        return view('pppoe.index', compact(
            'pppoeAccounts',
            'villages',
            'selectedVillageId',
            'selectedVillage',
            'statusFilter',
            'search',
            'totalCount',
            'availableCount',
            'usedCount'
        ));
    }

    /**
     * API for AJAX fetch: returns unused PPPoE accounts for a given village
     */
    public function availableByVillage(Request $request)
    {
        $this->ensureTableExists();

        $villageId = $request->get('village_id');
        $currentUsername = trim((string) $request->get('current_username', ''));

        if ($villageId) {
            $this->autoPopulateVillageAccounts((int)$villageId);
        }

        $query = PppoeAccount::with('village:id,name');

        if ($villageId) {
            $query->where('village_id', $villageId);

            // Find used usernames strictly within THIS village
            $usedQuery = Customer::where('village_id', $villageId)
                ->whereNotNull('pppoe_user')
                ->where('pppoe_user', '!=', '');

            if ($currentUsername !== '') {
                $usedQuery->whereRaw('LOWER(pppoe_user) != ?', [strtolower($currentUsername)]);
            }

            $usedArray = $usedQuery->pluck('pppoe_user')->map(fn($u) => strtolower(trim($u)))->all();

            if (!empty($usedArray)) {
                $query->whereNotIn(DB::raw('LOWER(username)'), $usedArray);
            }
        } else {
            $query->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('customers')
                    ->whereColumn('customers.village_id', 'pppoe_accounts.village_id')
                    ->whereRaw('LOWER(customers.pppoe_user) = LOWER(pppoe_accounts.username)');
            });
        }

        $accounts = $query->orderByRaw('LENGTH(username) ASC, username ASC')
            ->limit(100)
            ->get(['id', 'village_id', 'username', 'password'])
            ->map(function ($item) use ($currentUsername) {
                return [
                    'id' => $item->id,
                    'username' => $item->username,
                    'password' => $item->password,
                    'village_name' => $item->village?->name ?? 'Semua Desa',
                    'is_current' => strtolower(trim($item->username)) === strtolower($currentUsername),
                ];
            });

        return response()->json($accounts);
    }

    /**
     * Store single PPPoE account
     */
    public function store(Request $request)
    {
        $this->ensureTableExists();

        $validated = $request->validate([
            'village_id' => ['required', 'exists:villages,id'],
            'username' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $password = !empty($validated['password']) ? $validated['password'] : 'Irzaq09042003';

        $exists = PppoeAccount::where('village_id', $validated['village_id'])
            ->whereRaw('LOWER(username) = ?', [strtolower(trim($validated['username']))])
            ->exists();

        if ($exists) {
            return back()->withErrors(['username' => "Username PPPoE '{$validated['username']}' sudah terdaftar di desa ini."])->withInput();
        }

        PppoeAccount::create([
            'village_id' => $validated['village_id'],
            'username' => trim($validated['username']),
            'password' => $password,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Akun PPPoE '{$validated['username']}' berhasil ditambahkan!");
    }

    /**
     * Bulk generate PPPoE accounts for a village in a number range
     * e.g., Hokigo1 to Hokigo50 with password Irzaq09042003
     */
    public function bulkGenerate(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'village_id' => ['required', 'exists:villages,id'],
            'prefix' => ['required', 'string', 'max:50'],
            'from_number' => ['required', 'integer', 'min:1'],
            'to_number' => ['required', 'integer', 'min:1', 'gte:from_number'],
            'password' => ['nullable', 'string', 'max:100'],
        ]);

        $villageId = $request->village_id;
        $prefix = trim($request->prefix);
        $from = (int) $request->from_number;
        $to = (int) $request->to_number;
        $password = !empty($request->password) ? $request->password : 'Irzaq09042003';

        if (($to - $from) > 500) {
            return back()->withErrors(['to_number' => 'Maksimal 500 akun per pembuatan massal.'])->withInput();
        }

        $existing = PppoeAccount::where('village_id', $villageId)->pluck('username')->all();
        $existingMap = array_change_key_case(array_flip($existing), CASE_LOWER);

        $inserts = [];
        $now = now();
        $createdCount = 0;
        $skippedCount = 0;

        for ($i = $from; $i <= $to; $i++) {
            $username = $prefix . $i;

            if (!isset($existingMap[strtolower($username)])) {
                $inserts[] = [
                    'village_id' => $villageId,
                    'username' => $username,
                    'password' => $password,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $existingMap[strtolower($username)] = true;
                $createdCount++;
            } else {
                $skippedCount++;
            }
        }

        if (!empty($inserts)) {
            foreach (array_chunk($inserts, 100) as $chunk) {
                PppoeAccount::insert($chunk);
            }
        }

        $village = Village::find($villageId);
        $msg = "Berhasil membuat {$createdCount} akun PPPoE untuk {$village->name} ({$prefix}{$from} s/d {$prefix}{$to}).";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} akun dilewati karena sudah ada).";
        }

        return back()->with('success', $msg);
    }

    /**
     * Sync existing customer PPPoE accounts into the pppoe_accounts catalog
     */
    public function syncFromCustomers()
    {
        $this->ensureTableExists();

        $customersWithPppoe = Customer::whereNotNull('pppoe_user')
            ->where('pppoe_user', '!=', '')
            ->get(['id', 'village_id', 'pppoe_user', 'pppoe_password']);

        $imported = 0;
        foreach ($customersWithPppoe as $cust) {
            if (!$cust->village_id) continue;

            $exists = PppoeAccount::where('village_id', $cust->village_id)
                ->whereRaw('LOWER(username) = ?', [strtolower(trim($cust->pppoe_user))])
                ->exists();

            if (!$exists) {
                PppoeAccount::create([
                    'village_id' => $cust->village_id,
                    'username' => trim($cust->pppoe_user),
                    'password' => $cust->pppoe_password ?: 'Irzaq09042003',
                ]);
                $imported++;
            }
        }

        return back()->with('success', "Sinkronisasi selesai! {$imported} akun PPPoE dari data pelanggan telah didaftarkan ke katalog.");
    }

    /**
     * Delete a PPPoE account
     */
    public function destroy(PppoeAccount $pppoeAccount)
    {
        $isUsedInVillage = Customer::where('village_id', $pppoeAccount->village_id)
            ->whereRaw('LOWER(pppoe_user) = ?', [strtolower(trim($pppoeAccount->username))])
            ->first();

        if ($isUsedInVillage) {
            return back()->withErrors(['error' => "Akun '{$pppoeAccount->username}' tidak dapat dihapus karena sedang aktif digunakan oleh pelanggan {$isUsedInVillage->name} ({$isUsedInVillage->customer_code}) di desa {$pppoeAccount->village?->name}."]);
        }

        $username = $pppoeAccount->username;
        $pppoeAccount->delete();

        return back()->with('success', "Akun PPPoE '{$username}' berhasil dihapus.");
    }
}
