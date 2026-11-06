<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $currentPeriod = Carbon::now()->format('Y-m');
        $villages = Village::withCount('customers')->orderBy('name')->get();
        $packages = Package::where('is_active', true)->get();

        $query = Customer::with([
            'village:id,name',
            'package:id,name,speed,price',
            'invoices' => function ($q) use ($currentPeriod) {
                $q->where('period', $currentPeriod)->select('id', 'customer_id', 'period', 'amount', 'status');
            }
        ])->select([
            'id', 'customer_code', 'name', 'phone', 'village_id', 'package_id',
            'monthly_fee', 'status', 'notes', 'pppoe_user', 'pppoe_password',
            'wifi_ssid', 'wifi_password', 'house_number', 'installation_date', 'ip_address'
        ]);

        // Filter Desa
        if ($request->filled('village_id')) {
            $query->where('village_id', $request->village_id);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('pppoe_user', 'like', "%{$search}%")
                  ->orWhere('pppoe_password', 'like', "%{$search}%")
                  ->orWhere('wifi_ssid', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // View Mode: 'list' or 'grouped' (grouped by village)
        $viewMode = $request->get('view', 'grouped');

        if ($viewMode === 'grouped') {
            $customers = $query->orderBy('village_id')->orderBy('name')->get();
            $groupedCustomers = $customers->groupBy(function ($item) {
                return $item->village ? $item->village->name : 'Tanpa Desa / Luar Wilayah';
            });
            $paginatedCustomers = null;
        } else {
            $customers = $query->latest()->paginate(20)->withQueryString();
            $groupedCustomers = null;
            $paginatedCustomers = $customers;
        }

        return view('customers.index', compact(
            'villages',
            'packages',
            'groupedCustomers',
            'paginatedCustomers',
            'viewMode',
            'currentPeriod'
        ));
    }

    public function create()
    {
        $villages = Village::orderBy('name')->get();
        $packages = Package::where('is_active', true)->get();

        // Generate next customer code automatically based on highest existing number
        $suggestedCode = Customer::generateNextCustomerCode();

        return view('customers.create', compact('villages', 'packages', 'suggestedCode'));
    }

    public function store(Request $request)
    {
        if (empty($request->customer_code)) {
            $request->merge(['customer_code' => Customer::generateNextCustomerCode()]);
        }

        $validated = $request->validate([
            'customer_code' => ['required', 'string', 'max:30', 'unique:customers,customer_code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'address' => ['nullable', 'string'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'due_date_day' => ['required', 'integer', 'between:1,31'],
            'installation_date' => ['nullable', 'date'],
            'router_model' => ['nullable', 'string', 'max:100'],
            'redaman' => ['nullable', 'string', 'max:30'],
            'house_number' => ['nullable', 'string', 'max:100'],
            'pppoe_user' => ['nullable', 'string', 'max:100'],
            'pppoe_password' => ['nullable', 'string', 'max:100'],
            'wifi_ssid' => ['nullable', 'string', 'max:100'],
            'wifi_password' => ['nullable', 'string', 'max:100'],
            'ip_address' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,isolated,inactive'],
            'notes' => ['nullable', 'string'],
            'house_photo' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($request->hasFile('house_photo_file')) {
            $file = $request->file('house_photo_file');
            if ($file->isValid()) {
                $mime = $file->getMimeType();
                $base64 = base64_encode(file_get_contents($file->getRealPath()));
                $validated['house_photo'] = "data:{$mime};base64,{$base64}";
            }
        }

        $customer = Customer::create($validated);

        // Auto-register PPPoE into catalog if not exists
        try {
            if (!empty($customer->pppoe_user) && !empty($customer->village_id)) {
                \App\Models\PppoeAccount::firstOrCreate(
                    [
                        'village_id' => $customer->village_id,
                        'username' => $customer->pppoe_user,
                    ],
                    [
                        'password' => $customer->pppoe_password ?: 'Irzaq09042003',
                    ]
                );
            }
        } catch (\Throwable $e) {}

        // Auto create invoice for current month if active
        if ($customer->status === 'active') {
            $currentPeriod = Carbon::now()->format('Y-m');
            $dueDate = Carbon::createFromFormat('Y-m', $currentPeriod)->setDay(min($customer->due_date_day, 28));

            Invoice::firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'period' => $currentPeriod,
                ],
                [
                    'invoice_number' => 'INV-' . str_replace('-', '', $currentPeriod) . '-' . str_pad($customer->id, 4, '0', STR_PAD_LEFT),
                    'amount' => $customer->monthly_fee,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'status' => Carbon::now()->gt($dueDate) ? 'overdue' : 'unpaid',
                    'notes' => 'Tagihan perdana setelah pemasangan',
                    'created_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', "Pelanggan {$customer->name} ({$customer->customer_code}) berhasil ditambahkan!");
    }

    public function show(Customer $customer)
    {
        $customer->load(['village', 'package', 'invoices' => function ($q) {
            $q->latest('period');
        }]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $villages = Village::orderBy('name')->get();
        $packages = Package::where('is_active', true)->get();

        return view('customers.edit', compact('customer', 'villages', 'packages'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'customer_code' => ['required', 'string', 'max:30', 'unique:customers,customer_code,' . $customer->id],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'address' => ['nullable', 'string'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'due_date_day' => ['required', 'integer', 'between:1,31'],
            'installation_date' => ['nullable', 'date'],
            'router_model' => ['nullable', 'string', 'max:100'],
            'redaman' => ['nullable', 'string', 'max:30'],
            'house_number' => ['nullable', 'string', 'max:100'],
            'pppoe_user' => ['nullable', 'string', 'max:100'],
            'pppoe_password' => ['nullable', 'string', 'max:100'],
            'wifi_ssid' => ['nullable', 'string', 'max:100'],
            'wifi_password' => ['nullable', 'string', 'max:100'],
            'ip_address' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,isolated,inactive'],
            'notes' => ['nullable', 'string'],
            'house_photo' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($request->hasFile('house_photo_file')) {
            $file = $request->file('house_photo_file');
            if ($file->isValid()) {
                $mime = $file->getMimeType();
                $base64 = base64_encode(file_get_contents($file->getRealPath()));
                $validated['house_photo'] = "data:{$mime};base64,{$base64}";
            }
        }

        $customer->update($validated);

        // Auto-register PPPoE into catalog if not exists
        try {
            if (!empty($customer->pppoe_user) && !empty($customer->village_id)) {
                \App\Models\PppoeAccount::firstOrCreate(
                    [
                        'village_id' => $customer->village_id,
                        'username' => $customer->pppoe_user,
                    ],
                    [
                        'password' => $customer->pppoe_password ?: 'Irzaq09042003',
                    ]
                );
            }
        } catch (\Throwable $e) {}

        return redirect()->route('customers.show', $customer)
            ->with('success', "Data pelanggan {$customer->name} berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, Customer $customer)
    {
        $request->validate([
            'status' => ['required', 'in:active,isolated,inactive'],
        ]);

        $customer->update(['status' => $request->status]);

        $statusLabels = [
            'active' => 'Aktif',
            'isolated' => 'Isolir',
            'inactive' => 'Nonaktif',
        ];

        return back()->with('success', "Status pelanggan {$customer->name} diubah menjadi {$statusLabels[$request->status]}.");
    }

    public function photo(Customer $customer)
    {
        if (empty($customer->house_photo)) {
            abort(404);
        }

        if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $customer->house_photo, $matches)) {
            $mime = $matches[1];
            $data = base64_decode($matches[2]);
            return response($data, 200, [
                'Content-Type' => 'image/' . ($mime === 'jpg' ? 'jpeg' : $mime),
                'Cache-Control' => 'public, max-age=604800, immutable',
            ]);
        }

        abort(404);
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->name;
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Data pelanggan {$name} berhasil dihapus.");
    }
}
