<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Village;
use App\Models\VoucherSale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $selectedPeriod = $request->get('period', $now->format('Y-m'));

        try {
            $periodDate = Carbon::createFromFormat('Y-m', $selectedPeriod)->startOfMonth();
        } catch (\Exception $e) {
            $periodDate = $now->copy()->startOfMonth();
            $selectedPeriod = $now->format('Y-m');
        }

        $currentMonth = $periodDate->month;
        $currentYear = $periodDate->year;
        $currentPeriod = $selectedPeriod;
        $prevMonthDate = $periodDate->copy()->subMonth();

        $villageId = $request->get('village_id');
        $selectedVillage = null;
        if ($villageId) {
            $selectedVillage = Village::find($villageId);
            if (!$selectedVillage) {
                $villageId = null;
            }
        }

        // 1. Fetch 6-Month Transactions in ONE SINGLE QUERY
        $sixMonthsAgo = $periodDate->copy()->subMonths(5)->startOfMonth()->toDateString();
        $endOfPeriod = $periodDate->copy()->endOfMonth()->toDateString();
        $transactions6Months = FinancialTransaction::whereBetween('transaction_date', [$sixMonthsAgo, $endOfPeriod])
            ->select('type', 'amount', 'category', 'transaction_date')
            ->get();

        // 2. Customer Counts
        $customerStatsQuery = Customer::query();
        if ($villageId) {
            $customerStatsQuery->where('village_id', $villageId);
        }
        $customerStats = $customerStatsQuery->selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
            COUNT(CASE WHEN status = 'isolated' THEN 1 END) as isolated,
            COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive
        ")->first();

        $totalCustomers = $customerStats->total ?? 0;
        $activeCustomers = $customerStats->active ?? 0;
        $isolatedCustomers = $customerStats->isolated ?? 0;
        $inactiveCustomers = $customerStats->inactive ?? 0;

        // 3. Invoices in Selected Period
        $invoicesQuery = Invoice::where('period', $currentPeriod);
        if ($villageId) {
            $invoicesQuery->whereHas('customer', fn($q) => $q->where('village_id', $villageId));
        }
        $invoicesCurrentMonth = $invoicesQuery->get();
        $totalTagihanNominal = $invoicesCurrentMonth->sum('amount');

        $tagihanLunas = $invoicesCurrentMonth->where('status', 'paid');
        $tagihanLunasNominal = $tagihanLunas->sum('amount');
        $tagihanLunasCount = $tagihanLunas->count();

        $tagihanBelumBayar = $invoicesCurrentMonth->whereIn('status', ['unpaid', 'overdue']);
        $tagihanBelumBayarNominal = $tagihanBelumBayar->sum('amount');
        $tagihanBelumBayarCount = $tagihanBelumBayar->count();

        // 4. Metrik Lengkap Khusus Finansial Voucher Hotspot
        $voucherStatsQuery = VoucherSale::where('period', $currentPeriod);
        if ($villageId) {
            $voucherStatsQuery->where('village_id', $villageId);
        }
        $voucherStatsBulanIni = $voucherStatsQuery->selectRaw("
            COALESCE(SUM(quantity_sold), 0) as total_sold,
            COALESCE(SUM(total_gross_amount), 0) as total_gross,
            COALESCE(SUM(total_seller_fee), 0) as total_fee,
            COALESCE(SUM(net_amount), 0) as total_net,
            COALESCE(SUM(CASE WHEN status = 'deposited' THEN net_amount ELSE 0 END), 0) as total_deposited,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN net_amount ELSE 0 END), 0) as total_pending
        ")->first();

        $voucherOmzetKotor = (float) ($voucherStatsBulanIni->total_gross ?? 0);
        $voucherPengeluaranFee = (float) ($voucherStatsBulanIni->total_fee ?? 0);
        $voucherLabaBersih = (float) ($voucherStatsBulanIni->total_net ?? 0);
        $voucherTotalSold = (int) ($voucherStatsBulanIni->total_sold ?? 0);
        $voucherDeposited = (float) ($voucherStatsBulanIni->total_deposited ?? 0);
        $voucherPending = (float) ($voucherStatsBulanIni->total_pending ?? 0);

        // Grand Total Kumulatif Sepanjang Waktu khusus Voucher
        $voucherAllTimeQuery = VoucherSale::query();
        if ($villageId) {
            $voucherAllTimeQuery->where('village_id', $villageId);
        }
        $voucherAllTimeStats = $voucherAllTimeQuery->selectRaw("
            COALESCE(SUM(quantity_sold), 0) as total_sold,
            COALESCE(SUM(total_gross_amount), 0) as total_gross,
            COALESCE(SUM(total_seller_fee), 0) as total_fee,
            COALESCE(SUM(net_amount), 0) as total_net
        ")->first();

        $voucherAllTimeGross = (float) ($voucherAllTimeStats->total_gross ?? 0);
        $voucherAllTimeFee = (float) ($voucherAllTimeStats->total_fee ?? 0);
        $voucherAllTimeNet = (float) ($voucherAllTimeStats->total_net ?? 0);
        $voucherAllTimeSold = (int) ($voucherAllTimeStats->total_sold ?? 0);

        // 5. Kalkulasi Omzet & Pengeluaran
        if ($villageId) {
            // Khusus filter desa
            $omzetLanggananBulanIni = (float) $tagihanLunasNominal;
            $omzetVoucherBulanIni = (float) $voucherDeposited;
            $omzetBulanIni = $omzetLanggananBulanIni + $omzetVoucherBulanIni;
            $pengeluaranBulanIni = 0; // Pengeluaran umum/sentral ISP
            $labaBersihBulanIni = $omzetBulanIni - $pengeluaranBulanIni;
            $growthOmzet = 0;
        } else {
            // Semua Desa (Keseluruhan)
            $omzetBulanIni = $transactions6Months->filter(function ($t) use ($currentYear, $currentMonth) {
                $d = Carbon::parse($t->transaction_date);
                return $t->type === 'income' && $d->year === $currentYear && $d->month === $currentMonth;
            })->sum('amount');

            $omzetVoucherBulanIni = $transactions6Months->filter(function ($t) use ($currentYear, $currentMonth) {
                $d = Carbon::parse($t->transaction_date);
                return $t->type === 'income' && $t->category === 'Voucher Hotspot' && $d->year === $currentYear && $d->month === $currentMonth;
            })->sum('amount');

            $omzetLanggananBulanIni = $omzetBulanIni - $omzetVoucherBulanIni;

            $pengeluaranBulanIni = $transactions6Months->filter(function ($t) use ($currentYear, $currentMonth) {
                $d = Carbon::parse($t->transaction_date);
                return $t->type === 'expense' && $d->year === $currentYear && $d->month === $currentMonth;
            })->sum('amount');

            $labaBersihBulanIni = $omzetBulanIni - $pengeluaranBulanIni;

            $omzetBulanLalu = $transactions6Months->filter(function ($t) use ($prevMonthDate) {
                $d = Carbon::parse($t->transaction_date);
                return $t->type === 'income' && $d->year === $prevMonthDate->year && $d->month === $prevMonthDate->month;
            })->sum('amount');

            $growthOmzet = 0;
            if ($omzetBulanLalu > 0) {
                $growthOmzet = round((($omzetBulanIni - $omzetBulanLalu) / $omzetBulanLalu) * 100, 1);
            }
        }

        // Overdue count
        $overdueQuery = Invoice::whereIn('status', ['unpaid', 'overdue'])
            ->whereDate('due_date', '<', $now->toDateString());
        if ($villageId) {
            $overdueQuery->whereHas('customer', fn($q) => $q->where('village_id', $villageId));
        }
        $overdueCount = $overdueQuery->count();

        // 6. WhatsApp Due Date Reminders
        $dueTodayQuery = Invoice::with(['customer.village', 'customer.package'])
            ->where('period', $currentPeriod)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->whereDate('due_date', '<=', $now->toDateString());
        if ($villageId) {
            $dueTodayQuery->whereHas('customer', fn($q) => $q->where('village_id', $villageId));
        }
        $dueTodayInvoices = $dueTodayQuery->orderBy('due_date', 'asc')->take(10)->get();

        // 7. Build 6-Month Chart Data
        $chartMonths = [];
        $chartIncome = [];
        $chartExpense = [];
        $chartProfit = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = $periodDate->copy()->subMonths($i);
            $y = $date->year;
            $m = $date->month;
            $label = $date->translatedFormat('M Y');

            $inc = $transactions6Months->filter(function ($t) use ($y, $m) {
                $d = Carbon::parse($t->transaction_date);
                return $t->type === 'income' && $d->year === $y && $d->month === $m;
            })->sum('amount');

            $exp = $transactions6Months->filter(function ($t) use ($y, $m) {
                $d = Carbon::parse($t->transaction_date);
                return $t->type === 'expense' && $d->year === $y && $d->month === $m;
            })->sum('amount');

            $prf = $inc - $exp;

            $chartMonths[] = $label;
            $chartIncome[] = (float) $inc;
            $chartExpense[] = (float) $exp;
            $chartProfit[] = (float) $prf;
        }

        // 6. Customer Distribution by Village (1 query)
        $villages = Village::withCount('customers')->get();
        $villageLabels = $villages->pluck('name')->toArray();
        $villageCounts = $villages->pluck('customers_count')->toArray();

        // 7. Recent Transactions (1 query)
        $recentTransactions = FinancialTransaction::with(['user', 'invoice.customer'])
            ->latest('transaction_date')
            ->latest('id')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'omzetBulanIni',
            'omzetLanggananBulanIni',
            'omzetVoucherBulanIni',
            'voucherOmzetKotor',
            'voucherPengeluaranFee',
            'voucherLabaBersih',
            'voucherTotalSold',
            'voucherDeposited',
            'voucherPending',
            'voucherAllTimeGross',
            'voucherAllTimeFee',
            'voucherAllTimeNet',
            'voucherAllTimeSold',
            'pengeluaranBulanIni',
            'labaBersihBulanIni',
            'growthOmzet',
            'totalCustomers',
            'activeCustomers',
            'isolatedCustomers',
            'inactiveCustomers',
            'totalTagihanNominal',
            'tagihanLunasNominal',
            'tagihanLunasCount',
            'tagihanBelumBayarNominal',
            'tagihanBelumBayarCount',
            'overdueCount',
            'dueTodayInvoices',
            'chartMonths',
            'chartIncome',
            'chartExpense',
            'chartProfit',
            'villageLabels',
            'villageCounts',
            'villages',
            'villageId',
            'selectedVillage',
            'recentTransactions',
            'currentPeriod',
            'periodDate'
        ));
    }
}
