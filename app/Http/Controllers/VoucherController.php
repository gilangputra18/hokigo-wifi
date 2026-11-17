<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Models\Village;
use App\Models\VoucherAllocation;
use App\Models\VoucherSale;
use App\Models\VoucherSeller;
use App\Models\VoucherStock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Display voucher dashboard, central village stocks, seller distribution, and sales records.
     */
    public function index(Request $request)
    {
        $currentPeriod = $request->get('period', Carbon::now()->format('Y-m'));
        $villageId = $request->get('village_id');
        $status = $request->get('status');

        // Auto-seed existing sellers from historical voucher_sales if not yet created
        $this->syncHistoricalSellers();

        // 1. Voucher-enabled Villages (voucher or hybrid)
        $villages = Village::orderBy('name')->get();
        $voucherVillages = Village::whereIn('system_type', ['voucher', 'hybrid'])
            ->orWhere('name', 'Hampul')
            ->orderBy('name')
            ->get();

        // If a specific village is filtered, narrow down
        $activeVillagesForMonitoring = $villageId 
            ? $voucherVillages->where('id', $villageId) 
            : $voucherVillages;

        // 2. Prepare Detailed Village Voucher Inventory & Seller Tracking for the selected Period
        $villageMonitoring = [];
        foreach ($activeVillagesForMonitoring as $v) {
            // Central stocks dropped to this village in this period
            $stocks = VoucherStock::where('village_id', $v->id)
                ->where('period', $currentPeriod)
                ->get();

            $totalCentralStock = (int) $stocks->sum('initial_stock');
            $totalAllocatedStock = (int) $stocks->sum('allocated_stock');
            $remainingCentralStock = max(0, $totalCentralStock - $totalAllocatedStock);

            // Sellers registered in this village
            $sellers = VoucherSeller::where('village_id', $v->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            $sellersData = [];
            $villageTotalGiven = 0;
            $villageTotalSold = 0;
            $villageTotalGross = 0;
            $villageTotalFee = 0;
            $villageTotalNet = 0;

            foreach ($sellers as $s) {
                // Allocations for this seller in this period
                $allocations = VoucherAllocation::where('voucher_seller_id', $s->id)
                    ->where('period', $currentPeriod)
                    ->get();

                $initialAllocs = $allocations->filter(fn($a) => ($a->allocation_type ?? 'initial') === 'initial');
                $additionalAllocs = $allocations->filter(fn($a) => ($a->allocation_type ?? 'initial') === 'additional');

                $givenInitial = (int) $initialAllocs->sum('quantity_given');
                $givenAdditional = (int) $additionalAllocs->sum('quantity_given');
                $given = $givenInitial + $givenAdditional;

                $soldFromAlloc = (int) $allocations->sum('quantity_sold');

                // Sales records for this seller in this period
                $sellerSales = VoucherSale::where(function ($q) use ($s, $v) {
                        $q->where('voucher_seller_id', $s->id)
                          ->orWhere(function ($q2) use ($s, $v) {
                              $q2->where('village_id', $v->id)
                                 ->where('seller_name', $s->name);
                          });
                    })
                    ->where('period', $currentPeriod)
                    ->get();

                $totalSalesQty = (int) $sellerSales->sum('quantity_sold');
                $sold = max($soldFromAlloc, $totalSalesQty);

                // Sisa di tangan per kategori (Awal/Lama vs Tambahan)
                $remainingInitial = (int) $initialAllocs->sum(function ($a) {
                    return max(0, $a->quantity_given - $a->quantity_sold);
                });
                $remainingAdditional = (int) $additionalAllocs->sum(function ($a) {
                    return max(0, $a->quantity_given - $a->quantity_sold);
                });
                $remainingInHand = $remainingInitial + $remainingAdditional;

                // Perhitungan progress habis:
                // Jika penjual memegang alokasi baru, hitung progress terhadap stok aktif yang belum selesai
                // agar jika sudah habis 100% lalu diberikan stok baru, progress TIDAK nyangkut di 100% dari riwayat lama
                if ($allocations->isNotEmpty()) {
                    $activeAllocs = $allocations->filter(function ($a) {
                        return $a->quantity_sold < $a->quantity_given;
                    });

                    if ($activeAllocs->isNotEmpty()) {
                        $activeGiven = (int) $activeAllocs->sum('quantity_given');
                        $activeSold = (int) $activeAllocs->sum('quantity_sold');
                        $percentage = $activeGiven > 0 ? min(100, round(($activeSold / $activeGiven) * 100)) : 0;
                    } else {
                        // Semua alokasi yang diberikan telah lunas terjual 100%
                        $percentage = 100;
                    }
                } else {
                    // Belum ada alokasi resmi (data penjualan historis langsung)
                    $percentage = $sold > 0 ? 100 : 0;
                }

                $gross = (float) $sellerSales->sum('total_gross_amount');
                $fee = (float) $sellerSales->sum('total_seller_fee');
                $net = (float) $sellerSales->sum('net_amount');

                $villageTotalGiven += $given;
                $villageTotalSold += $sold;
                $villageTotalGross += $gross;
                $villageTotalFee += $fee;
                $villageTotalNet += $net;

                // Breakdown per specific voucher type (e.g. 3 jam, 12 jam, 24 jam)
                $sellerVoucherTypes = collect()
                    ->merge($allocations->pluck('voucher_type'))
                    ->merge($sellerSales->pluck('voucher_type'))
                    ->unique()
                    ->filter()
                    ->values();

                $typesBreakdown = [];
                foreach ($sellerVoucherTypes as $vType) {
                    $typeAlloc = $allocations->where('voucher_type', $vType);
                    $typeInitAlloc = $initialAllocs->where('voucher_type', $vType);
                    $typeAddAlloc = $additionalAllocs->where('voucher_type', $vType);
                    $typeSales = $sellerSales->where('voucher_type', $vType);

                    $tGivenInit = (int) $typeInitAlloc->sum('quantity_given');
                    $tGivenAdd = (int) $typeAddAlloc->sum('quantity_given');
                    $tGiven = $tGivenInit + $tGivenAdd;

                    $tSoldAlloc = (int) $typeAlloc->sum('quantity_sold');
                    $tSoldSales = (int) $typeSales->sum('quantity_sold');
                    $tSold = max($tSoldAlloc, $tSoldSales);

                    // Sisa per tipe di tangan penjual (Stok Lama vs Tambahan)
                    $tRemainingInit = (int) $typeInitAlloc->sum(function ($a) {
                        return max(0, $a->quantity_given - $a->quantity_sold);
                    });
                    $tRemainingAdd = (int) $typeAddAlloc->sum(function ($a) {
                        return max(0, $a->quantity_given - $a->quantity_sold);
                    });
                    $tRemaining = $tRemainingInit + $tRemainingAdd;

                    $tGross = (float) $typeSales->sum('total_gross_amount');
                    $tFee = (float) $typeSales->sum('total_seller_fee');
                    $tNet = (float) $typeSales->sum('net_amount');

                    $unitPrice = $typeSales->first()?->price_per_unit 
                        ?? $typeAlloc->first()?->price_per_unit 
                        ?? 0;

                    // Buat nama tampilan lengkap (misal: "3 jam (Rp 5.000)")
                    $displayName = $vType;
                    if ($unitPrice > 0) {
                        $priceFormatted = 'Rp ' . number_format($unitPrice, 0, ',', '.');
                        $hasRp = stripos($vType, 'rp') !== false || stripos($vType, number_format($unitPrice, 0, ',', '.')) !== false;
                        if (!$hasRp) {
                            $displayName = "{$vType} ({$priceFormatted})";
                        }
                    }

                    $typesBreakdown[] = [
                        'voucher_type' => $vType,
                        'display_name' => $displayName,
                        'unit_price' => $unitPrice,
                        'given' => $tGiven,
                        'given_initial' => $tGivenInit,
                        'given_additional' => $tGivenAdd,
                        'sold' => $tSold,
                        'remaining' => $tRemaining,
                        'remaining_initial' => $tRemainingInit,
                        'remaining_additional' => $tRemainingAdd,
                        'gross' => $tGross,
                        'fee' => $tFee,
                        'net' => $tNet,
                    ];
                }

                usort($typesBreakdown, function ($a, $b) {
                    return $b['sold'] <=> $a['sold'];
                });

                $sellersData[] = [
                    'seller' => $s,
                    'given' => $given,
                    'given_initial' => $givenInitial,
                    'given_additional' => $givenAdditional,
                    'sold' => $sold,
                    'remaining' => $remainingInHand,
                    'remaining_initial' => $remainingInitial,
                    'remaining_additional' => $remainingAdditional,
                    'gross' => $gross,
                    'fee' => $fee,
                    'net' => $net,
                    'sales_count' => $sellerSales->count(),
                    'percentage' => $percentage,
                    'types_breakdown' => $typesBreakdown,
                ];
            }

            $villageTotalRemaining = (int) collect($sellersData)->sum('remaining');

            $villageMonitoring[] = [
                'village' => $v,
                'stocks' => $stocks,
                'total_central' => $totalCentralStock,
                'total_allocated' => $totalAllocatedStock,
                'remaining_central' => $remainingCentralStock,
                'total_given' => $villageTotalGiven,
                'total_sold' => $villageTotalSold,
                'remaining_field' => $villageTotalRemaining,
                'total_gross' => $villageTotalGross,
                'total_fee' => $villageTotalFee,
                'total_net' => $villageTotalNet,
                'sellers' => $sellersData,
            ];
        }

        // 3. Voucher Sales Table Query
        $query = VoucherSale::with(['village', 'seller', 'creator'])
            ->where('period', $currentPeriod);

        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $sales = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        // 4. Period Statistics
        $statsQuery = VoucherSale::where('period', $currentPeriod);
        if ($villageId) {
            $statsQuery->where('village_id', $villageId);
        }

        $stats = $statsQuery->selectRaw("
            COALESCE(SUM(quantity_sold), 0) as total_sold,
            COALESCE(SUM(total_gross_amount), 0) as total_gross,
            COALESCE(SUM(total_seller_fee), 0) as total_fee,
            COALESCE(SUM(net_amount), 0) as total_net,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN net_amount ELSE 0 END), 0) as total_pending
        ")->first();

        // Overall Central Drop & Allocation Totals for Period
        $periodStockTotals = VoucherStock::where('period', $currentPeriod)
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->selectRaw("
                COALESCE(SUM(initial_stock), 0) as total_drop,
                COALESCE(SUM(allocated_stock), 0) as total_allocated
            ")->first();

        $periodAllocationTotals = VoucherAllocation::where('period', $currentPeriod)
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->selectRaw("
                COALESCE(SUM(quantity_given), 0) as total_given,
                COALESCE(SUM(quantity_sold), 0) as total_sold
            ")->first();

        // Grand Total Sepanjang Waktu
        $allTimeStats = VoucherSale::selectRaw("
            COALESCE(SUM(quantity_sold), 0) as total_sold,
            COALESCE(SUM(total_gross_amount), 0) as total_gross,
            COALESCE(SUM(total_seller_fee), 0) as total_fee,
            COALESCE(SUM(net_amount), 0) as total_net,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN net_amount ELSE 0 END), 0) as total_pending
        ")->first();

        return view('vouchers.index', compact(
            'sales',
            'stats',
            'allTimeStats',
            'villages',
            'voucherVillages',
            'villageMonitoring',
            'periodStockTotals',
            'periodAllocationTotals',
            'currentPeriod',
            'villageId',
            'status'
        ));
    }

    /**
     * Store Central Stock Drop for a Village (e.g. 500 pcs voucher 5000 ke Desa Hampul).
     */
    public function storeStock(Request $request)
    {
        $validated = $request->validate([
            'village_id' => ['required', 'exists:villages,id'],
            'period' => ['required', 'string', 'max:7'],
            'voucher_type' => ['required', 'string', 'max:100'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'initial_stock' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $village = Village::findOrFail($validated['village_id']);

        $stock = VoucherStock::create([
            'village_id' => $validated['village_id'],
            'period' => $validated['period'],
            'voucher_type' => $validated['voucher_type'],
            'price_per_unit' => $validated['price_per_unit'],
            'initial_stock' => $validated['initial_stock'],
            'allocated_stock' => 0,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', "Stok sentral sebanyak {$stock->initial_stock} pcs ({$stock->voucher_type}) berhasil di-drop ke {$village->name} untuk periode {$stock->period}!");
    }

    /**
     * Destroy a Central Stock Drop.
     */
    public function destroyStock(VoucherStock $stock)
    {
        if ($stock->allocated_stock > 0) {
            return back()->withErrors(['error' => "Stok sentral ini tidak dapat dihapus karena sudah dibagikan sebanyak {$stock->allocated_stock} pcs ke penjual."]);
        }

        $villageName = $stock->village?->name ?? 'Desa';
        $stock->delete();

        return back()->with('success', "Catatan stok sentral {$villageName} berhasil dihapus.");
    }

    /**
     * Store a new Voucher Seller / Reseller for a Village.
     */
    public function storeSeller(Request $request)
    {
        $validated = $request->validate([
            'village_id' => ['required', 'exists:villages,id'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string', 'max:255'],
            'default_fee_per_unit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $village = Village::findOrFail($validated['village_id']);

        $seller = VoucherSeller::firstOrCreate(
            [
                'village_id' => $validated['village_id'],
                'name' => trim($validated['name']),
            ],
            [
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'default_fee_per_unit' => $validated['default_fee_per_unit'] ?? 1000,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]
        );

        return back()->with('success', "Mitra penjual {$seller->name} di {$village->name} berhasil disimpan!");
    }

    /**
     * Destroy a Voucher Seller.
     */
    public function destroySeller(VoucherSeller $seller)
    {
        $name = $seller->name;
        $seller->delete();

        return back()->with('success', "Mitra penjual {$name} berhasil dihapus.");
    }

    /**
     * Distribute stock from central village to a specific seller (e.g. Si A dikasih 150 pcs).
     */
    public function storeAllocation(Request $request)
    {
        $validated = $request->validate([
            'village_id' => ['required', 'exists:villages,id'],
            'voucher_seller_id' => ['required_without:new_seller_name', 'nullable', 'exists:voucher_sellers,id'],
            'new_seller_name' => ['nullable', 'string', 'max:150'],
            'period' => ['required', 'string', 'max:7'],
            'allocation_type' => ['nullable', 'in:initial,additional'],
            'voucher_stock_id' => ['nullable', 'exists:voucher_stocks,id'],
            'voucher_type' => ['required', 'string', 'max:100'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'quantity_given' => ['required', 'integer', 'min:1'],
            'allocation_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $village = Village::findOrFail($validated['village_id']);
        $qtyGiven = (int) $validated['quantity_given'];
        $allocationType = $validated['allocation_type'] ?? 'initial';

        // If new seller name is provided, create it on the fly
        if (!empty($validated['new_seller_name'])) {
            $seller = VoucherSeller::firstOrCreate(
                [
                    'village_id' => $village->id,
                    'name' => trim($validated['new_seller_name']),
                ],
                [
                    'default_fee_per_unit' => 1000,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]
            );
            $sellerId = $seller->id;
            $sellerName = $seller->name;
        } else {
            $seller = VoucherSeller::findOrFail($validated['voucher_seller_id']);
            $sellerId = $seller->id;
            $sellerName = $seller->name;
        }

        // Link with central stock if available
        $stockId = $validated['voucher_stock_id'] ?? null;
        if (!$stockId) {
            $stock = VoucherStock::where('village_id', $village->id)
                ->where('period', $validated['period'])
                ->where('voucher_type', $validated['voucher_type'])
                ->first();
            $stockId = $stock?->id;
        } else {
            $stock = VoucherStock::find($stockId);
        }

        if ($stock) {
            $stock->increment('allocated_stock', $qtyGiven);
        }

        $allocation = VoucherAllocation::create([
            'village_id' => $village->id,
            'voucher_seller_id' => $sellerId,
            'voucher_stock_id' => $stockId,
            'period' => $validated['period'],
            'voucher_type' => $validated['voucher_type'],
            'allocation_type' => $allocationType,
            'price_per_unit' => $validated['price_per_unit'],
            'quantity_given' => $qtyGiven,
            'quantity_sold' => 0,
            'allocation_date' => $validated['allocation_date'] ?? Carbon::now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $typeLabel = $allocationType === 'additional' ? 'Voucher Tambahan' : 'Stok Awal/Lama';
        return back()->with('success', "Berhasil mencatat {$typeLabel} sebanyak {$qtyGiven} pcs ({$allocation->voucher_type}) untuk {$sellerName} di {$village->name}!");
    }

    /**
     * Destroy a stock allocation to seller and return the stock back to central.
     */
    public function destroyAllocation(VoucherAllocation $allocation)
    {
        if ($allocation->quantity_sold > 0) {
            return back()->withErrors(['error' => "Pembagian voucher ini tidak dapat dihapus karena sudah ada {$allocation->quantity_sold} pcs yang terjual."]);
        }

        if ($allocation->stock) {
            $allocation->stock->decrement('allocated_stock', $allocation->quantity_given);
        }

        $sellerName = $allocation->seller?->name ?? 'Penjual';
        $qty = $allocation->quantity_given;
        $allocation->delete();

        return back()->with('success', "Pembagian {$qty} pcs voucher kepada {$sellerName} dibatalkan & dikembalikan ke stok sentral.");
    }

    /**
     * Store a voucher sales record (Laporan Penjualan Voucher & Potong Stok).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'village_id' => ['nullable', 'exists:villages,id'],
            'voucher_seller_id' => ['nullable', 'exists:voucher_sellers,id'],
            'seller_name' => ['required', 'string', 'max:150'],
            'seller_phone' => ['nullable', 'string', 'max:25'],
            'period' => ['required', 'string', 'max:7'],
            'voucher_type' => ['required', 'string', 'max:100'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'quantity_sold' => ['required', 'integer', 'min:1'],
            'fee_per_unit' => ['nullable', 'numeric', 'min:0'],
            'total_seller_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:deposited,pending'],
            'sale_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $qty = (int) $validated['quantity_sold'];
        $price = (float) $validated['price_per_unit'];
        $totalGross = $qty * $price;

        $feePerUnit = isset($validated['fee_per_unit']) && $validated['fee_per_unit'] > 0 
            ? (float) $validated['fee_per_unit'] 
            : 0;

        $totalFee = $feePerUnit > 0 
            ? ($qty * $feePerUnit) 
            : (float) ($validated['total_seller_fee'] ?? 0);

        $netAmount = max(0, $totalGross - $totalFee);

        // Resolve or create seller
        $sellerId = $validated['voucher_seller_id'] ?? null;
        if (!$sellerId && !empty($validated['village_id']) && !empty($validated['seller_name'])) {
            $seller = VoucherSeller::firstOrCreate(
                [
                    'village_id' => $validated['village_id'],
                    'name' => trim($validated['seller_name']),
                ],
                [
                    'phone' => $validated['seller_phone'] ?? null,
                    'default_fee_per_unit' => $feePerUnit > 0 ? $feePerUnit : 1000,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]
            );
            $sellerId = $seller->id;
        }

        // Deduct from seller's active allocation if exists
        $allocationId = null;
        $remainingInHand = null;

        if ($sellerId) {
            // Find active allocations where quantity_sold < quantity_given
            $activeAllocQuery = VoucherAllocation::where('voucher_seller_id', $sellerId)
                ->where('period', $validated['period'])
                ->whereColumn('quantity_sold', '<', 'quantity_given');

            // Prefer allocation with matching voucher_type and prioritize initial (stok lama) before additional (stok tambahan)
            $allocation = (clone $activeAllocQuery)
                ->where('voucher_type', $validated['voucher_type'])
                ->orderByRaw("CASE WHEN allocation_type = 'initial' THEN 0 ELSE 1 END")
                ->orderBy('id', 'asc')
                ->first();

            // If not found by exact type, find any active allocation (initial first)
            if (!$allocation) {
                $allocation = (clone $activeAllocQuery)
                    ->orderByRaw("CASE WHEN allocation_type = 'initial' THEN 0 ELSE 1 END")
                    ->orderBy('id', 'asc')
                    ->first();
            }

            if ($allocation) {
                $allocationId = $allocation->id;
                $available = max(0, $allocation->quantity_given - $allocation->quantity_sold);
                $toDeduct = min($qty, $available);
                $allocation->increment('quantity_sold', $toDeduct);

                $remainingInHand = max(0, $allocation->quantity_given - $allocation->quantity_sold);

                // If sale quantity exceeded this allocation's remaining, cascade to next active allocations (initial first)
                $remainingToDeduct = $qty - $toDeduct;
                if ($remainingToDeduct > 0) {
                    $otherAllocs = VoucherAllocation::where('voucher_seller_id', $sellerId)
                        ->where('period', $validated['period'])
                        ->where('id', '!=', $allocation->id)
                        ->whereColumn('quantity_sold', '<', 'quantity_given')
                        ->orderByRaw("CASE WHEN allocation_type = 'initial' THEN 0 ELSE 1 END")
                        ->orderBy('id', 'asc')
                        ->get();

                    foreach ($otherAllocs as $other) {
                        if ($remainingToDeduct <= 0) break;
                        $otherAvail = max(0, $other->quantity_given - $other->quantity_sold);
                        $d = min($remainingToDeduct, $otherAvail);
                        $other->increment('quantity_sold', $d);
                        $remainingToDeduct -= $d;
                    }
                }
            }
        }

        $sale = VoucherSale::create([
            'village_id' => $validated['village_id'] ?? null,
            'voucher_seller_id' => $sellerId,
            'voucher_allocation_id' => $allocationId,
            'seller_name' => $validated['seller_name'],
            'seller_phone' => $validated['seller_phone'] ?? null,
            'period' => $validated['period'],
            'voucher_type' => $validated['voucher_type'],
            'price_per_unit' => $price,
            'quantity_sold' => $qty,
            'total_gross_amount' => $totalGross,
            'fee_per_unit' => $feePerUnit,
            'total_seller_fee' => $totalFee,
            'net_amount' => $netAmount,
            'status' => $validated['status'],
            'sale_date' => $validated['sale_date'] ?? Carbon::now()->toDateString(),
            'deposited_at' => $validated['status'] === 'deposited' ? Carbon::now() : null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // If marked deposited directly, record income in financial transactions
        if ($sale->status === 'deposited' && $netAmount > 0) {
            $code = 'VCR-' . Carbon::now()->format('Ymd') . '-' . rand(100, 999);
            FinancialTransaction::create([
                'transaction_code' => $code,
                'type' => 'income',
                'category' => 'Voucher Hotspot',
                'amount' => $netAmount,
                'transaction_date' => $sale->sale_date ?? Carbon::now()->toDateString(),
                'payment_method' => 'tunai',
                'description' => "Setoran voucher {$sale->voucher_type} ({$qty} pcs) dari {$sale->seller_name}",
                'user_id' => auth()->id(),
            ]);
        }

        $stockInfo = $remainingInHand !== null ? " (Sisa stok di tangan penjual: {$remainingInHand} pcs)" : "";
        return back()->with('success', "Rekap penjualan {$qty} pcs voucher {$sale->seller_name} berhasil dicatat! Laba Bersih: Rp " . number_format($netAmount, 0, ',', '.') . $stockInfo);
    }

    public function markDeposited(VoucherSale $voucher)
    {
        if ($voucher->status === 'deposited') {
            return back()->with('info', 'Status voucher ini sudah berstatus disetor.');
        }

        $voucher->update([
            'status' => 'deposited',
            'deposited_at' => Carbon::now(),
        ]);

        // Record income transaction
        if ($voucher->net_amount > 0) {
            $code = 'VCR-' . Carbon::now()->format('Ymd') . '-' . rand(100, 999);
            FinancialTransaction::create([
                'transaction_code' => $code,
                'type' => 'income',
                'category' => 'Voucher Hotspot',
                'amount' => $voucher->net_amount,
                'transaction_date' => Carbon::now()->toDateString(),
                'payment_method' => 'tunai',
                'description' => "Setoran voucher {$voucher->voucher_type} ({$voucher->quantity_sold} pcs) dari {$voucher->seller_name}",
                'user_id' => auth()->id(),
            ]);
        }

        return back()->with('success', "Setoran voucher {$voucher->seller_name} sebesar Rp " . number_format($voucher->net_amount, 0, ',', '.') . " berhasil diterima!");
    }

    public function destroy(VoucherSale $voucher)
    {
        $seller = $voucher->seller_name;
        $qty = $voucher->quantity_sold;

        // Restore allocation if linked
        if ($voucher->voucher_allocation_id && $voucher->allocation) {
            $voucher->allocation->decrement('quantity_sold', min($qty, $voucher->allocation->quantity_sold));
        }

        $voucher->delete();

        return back()->with('success', "Data penjualan voucher {$seller} ({$qty} pcs) berhasil dihapus & stok dikembalikan.");
    }

    /**
     * Helper to sync existing distinct sellers from voucher_sales table.
     */
    protected function syncHistoricalSellers(): void
    {
        try {
            $historical = VoucherSale::whereNotNull('village_id')
                ->whereNotNull('seller_name')
                ->select('village_id', 'seller_name', 'seller_phone')
                ->distinct()
                ->get();

            foreach ($historical as $h) {
                VoucherSeller::firstOrCreate(
                    [
                        'village_id' => $h->village_id,
                        'name' => trim($h->seller_name),
                    ],
                    [
                        'phone' => $h->seller_phone,
                        'default_fee_per_unit' => 1000,
                        'is_active' => true,
                    ]
                );
            }
        } catch (\Throwable $e) {}
    }
}
