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
     * Display the PPPoE Accounts Directory & Search page
     */
    public function index(Request $request)
    {
        $this->ensureTableExists();

        $villages = Village::orderBy('name')->get();
        $selectedVillageId = $request->get('village_id');
        $statusFilter = $request->get('status', 'available'); // default: available
        $search = trim($request->get('search', ''));

        $query = PppoeAccount::with([
            'village:id,name,code',
            'customer' => function ($q) {
                $q->select('id', 'customer_code', 'name', 'phone', 'village_id', 'status', 'pppoe_user');
            }
        ]);

        if ($selectedVillageId) {
            $query->where('village_id', $selectedVillageId);
        }

        if ($search !== '') {
            $query->where('username', 'like', "%{$search}%");
        }

        if ($statusFilter === 'available') {
            $query->whereDoesntHave('customer');
        } elseif ($statusFilter === 'used') {
            $query->whereHas('customer');
        }

        // Natural sort by length of username, then alphanumeric
        $pppoeAccounts = $query->orderByRaw('LENGTH(username) ASC, username ASC')
            ->paginate(30)
            ->withQueryString();

        // Statistics counts
        $baseCountsQuery = PppoeAccount::query();
        if ($selectedVillageId) {
            $baseCountsQuery->where('village_id', $selectedVillageId);
        }
        if ($search !== '') {
            $baseCountsQuery->where('username', 'like', "%{$search}%");
        }

        $totalCount = (clone $baseCountsQuery)->count();
        $usedCount = (clone $baseCountsQuery)->whereHas('customer')->count();
        $availableCount = $totalCount - $usedCount;

        // Auto-seed initial accounts if database is empty for selected village
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
        $currentUsername = $request->get('current_username');

        $query = PppoeAccount::with('village:id,name');

        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        // Available = doesn't have a customer, OR is the current customer's own username (for edit mode)
        $query->where(function ($q) use ($currentUsername) {
            $q->whereDoesntHave('customer');
            if ($currentUsername) {
                $q->orWhere('username', $currentUsername);
            }
        });

        $accounts = $query->orderByRaw('LENGTH(username) ASC, username ASC')
            ->limit(100)
            ->get(['id', 'village_id', 'username', 'password'])
            ->map(function ($item) use ($currentUsername) {
                return [
                    'id' => $item->id,
                    'username' => $item->username,
                    'password' => $item->password,
                    'village_name' => $item->village?->name ?? 'Semua Desa',
                    'is_current' => $item->username === $currentUsername,
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
            ->where('username', $validated['username'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['username' => "Username PPPoE '{$validated['username']}' sudah terdaftar di desa ini."])->withInput();
        }

        PppoeAccount::create([
            'village_id' => $validated['village_id'],
            'username' => $validated['username'],
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

        // Limit range to 500 per batch
        if (($to - $from) > 500) {
            return back()->withErrors(['to_number' => 'Maksimal 500 akun per pembuatan massal.'])->withInput();
        }

        $createdCount = 0;
        $skippedCount = 0;

        for ($i = $from; $i <= $to; $i++) {
            $username = $prefix . $i;

            $exists = PppoeAccount::where('village_id', $villageId)
                ->where('username', $username)
                ->exists();

            if (!$exists) {
                PppoeAccount::create([
                    'village_id' => $villageId,
                    'username' => $username,
                    'password' => $password,
                ]);
                $createdCount++;
            } else {
                $skippedCount++;
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
                ->where('username', $cust->pppoe_user)
                ->exists();

            if (!$exists) {
                PppoeAccount::create([
                    'village_id' => $cust->village_id,
                    'username' => $cust->pppoe_user,
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
        if ($pppoeAccount->isUsed()) {
            return back()->withErrors(['error' => "Akun '{$pppoeAccount->username}' tidak dapat dihapus karena sedang aktif digunakan oleh pelanggan {$pppoeAccount->customer?->name} ({$pppoeAccount->customer?->customer_code})."]);
        }

        $username = $pppoeAccount->username;
        $pppoeAccount->delete();

        return back()->with('success', "Akun PPPoE '{$username}' berhasil dihapus.");
    }
}
