<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Models\Village;
use App\Models\VoucherSale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $currentPeriod = $request->get('period', Carbon::now()->format('Y-m'));
        $villageId = $request->get('village_id');
        $status = $request->get('status');

        $query = VoucherSale::with(['village', 'creator'])
            ->where('period', $currentPeriod);

        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $sales = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        // Calculate period statistics
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

        // Villages that have voucher enabled (voucher or hybrid)
        $villages = Village::orderBy('name')->get();

        return view('vouchers.index', compact('sales', 'stats', 'villages', 'currentPeriod', 'villageId', 'status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'village_id' => ['nullable', 'exists:villages,id'],
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

        $sale = VoucherSale::create([
            'village_id' => $validated['village_id'] ?? null,
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

        return back()->with('success', "Rekap penjualan voucher {$sale->seller_name} berhasil dicatat! Laba Bersih: Rp " . number_format($netAmount, 0, ',', '.'));
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
        $voucher->delete();

        return back()->with('success', "Data penjualan voucher {$seller} berhasil dihapus.");
    }
}
