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
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $currentPeriod = $now->format('Y-m');
        $prevMonthDate = $now->copy()->subMonth();

        // 1. Fetch 6-Month Transactions in ONE SINGLE QUERY (Avoids 12 cross-continent roundtrips!)
        $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth()->toDateString();
        $transactions6Months = FinancialTransaction::where('transaction_date', '>=', $sixMonthsAgo)
            ->select('type', 'amount', 'category', 'transaction_date')
            ->get();

        // Calculate this month & previous month totals in memory
        $omzetBulanIni = $transactions6Months->filter(function ($t) use ($currentYear, $currentMonth) {
            $d = Carbon::parse($t->transaction_date);
            return $t->type === 'income' && $d->year === $currentYear && $d->month === $currentMonth;
        })->sum('amount');

        // Khusus pemisah omzet: Voucher Hotspot vs Langganan Bulanan
        $omzetVoucherBulanIni = $transactions6Months->filter(function ($t) use ($currentYear, $currentMonth) {
            $d = Carbon::parse($t->transaction_date);
            return $t->type === 'income' && $t->category === 'Voucher Hotspot' && $d->year === $currentYear && $d->month === $currentMonth;
        })->sum('amount');

        $omzetLanggananBulanIni = $omzetBulanIni - $omzetVoucherBulanIni;

        // Metrik setoran voucher di reseller bulan ini
        $voucherPendingNominal = VoucherSale::where('period', $currentPeriod)
            ->where('status', 'pending')
            ->sum('net_amount');

        $voucherPcsTerjual = VoucherSale::where('period', $currentPeriod)
            ->sum('quantity_sold');

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

        // 2. Customer Counts in ONE SINGLE QUERY
        $customerStats = Customer::selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
            COUNT(CASE WHEN status = 'isolated' THEN 1 END) as isolated,
            COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive
        ")->first();

        $totalCustomers = $customerStats->total ?? 0;
        $activeCustomers = $customerStats->active ?? 0;
        $isolatedCustomers = $customerStats->isolated ?? 0;
        $inactiveCustomers = $customerStats->inactive ?? 0;

        // 3. Current Month Invoices in ONE QUERY
        $invoicesCurrentMonth = Invoice::where('period', $currentPeriod)->get();
        $totalTagihanNominal = $invoicesCurrentMonth->sum('amount');

        $tagihanLunas = $invoicesCurrentMonth->where('status', 'paid');
        $tagihanLunasNominal = $tagihanLunas->sum('amount');
        $tagihanLunasCount = $tagihanLunas->count();

        $tagihanBelumBayar = $invoicesCurrentMonth->whereIn('status', ['unpaid', 'overdue']);
        $tagihanBelumBayarNominal = $tagihanBelumBayar->sum('amount');
        $tagihanBelumBayarCount = $tagihanBelumBayar->count();

        // Overdue count
        $overdueCount = Invoice::whereIn('status', ['unpaid', 'overdue'])
            ->whereDate('due_date', '<', $now->toDateString())
            ->count();

        // 4. WhatsApp Due Date Reminders (Due today or past due and unpaid)
        $dueTodayInvoices = Invoice::with(['customer.village', 'customer.package'])
            ->where('period', $currentPeriod)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->whereDate('due_date', '<=', $now->toDateString())
            ->orderBy('due_date', 'asc')
            ->take(10)
            ->get();

        // 5. Build 6-Month Chart Data from memory (Instant calculation, 0 DB roundtrips)
        $chartMonths = [];
        $chartIncome = [];
        $chartExpense = [];
        $chartProfit = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
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
            'voucherPendingNominal',
            'voucherPcsTerjual',
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
            'recentTransactions',
            'currentPeriod'
        ));
    }
}
