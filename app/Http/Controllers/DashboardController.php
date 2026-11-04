<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $currentPeriod = $now->format('Y-m');

        // 1. Financial Metrics (Current Month)
        $omzetBulanIni = FinancialTransaction::income()
            ->forMonth($currentYear, $currentMonth)
            ->sum('amount');

        $pengeluaranBulanIni = FinancialTransaction::expense()
            ->forMonth($currentYear, $currentMonth)
            ->sum('amount');

        $labaBersihBulanIni = $omzetBulanIni - $pengeluaranBulanIni;

        // Previous month comparison
        $prevMonthDate = $now->copy()->subMonth();
        $omzetBulanLalu = FinancialTransaction::income()
            ->forMonth($prevMonthDate->year, $prevMonthDate->month)
            ->sum('amount');

        $growthOmzet = 0;
        if ($omzetBulanLalu > 0) {
            $growthOmzet = round((($omzetBulanIni - $omzetBulanLalu) / $omzetBulanLalu) * 100, 1);
        }

        // 2. Customer Metrics
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $isolatedCustomers = Customer::where('status', 'isolated')->count();
        $inactiveCustomers = Customer::where('status', 'inactive')->count();

        // 3. Current Month Invoices Metrics
        $invoicesCurrentMonth = Invoice::where('period', $currentPeriod)->get();
        $totalTagihanNominal = $invoicesCurrentMonth->sum('amount');
        
        $tagihanLunas = $invoicesCurrentMonth->where('status', 'paid');
        $tagihanLunasNominal = $tagihanLunas->sum('amount');
        $tagihanLunasCount = $tagihanLunas->count();

        $tagihanBelumBayar = $invoicesCurrentMonth->whereIn('status', ['unpaid', 'overdue']);
        $tagihanBelumBayarNominal = $tagihanBelumBayar->sum('amount');
        $tagihanBelumBayarCount = $tagihanBelumBayar->count();

        // Overdue invoices overall (unpaid past due_date)
        $overdueCount = Invoice::whereIn('status', ['unpaid', 'overdue'])
            ->whereDate('due_date', '<', $now->toDateString())
            ->count();

        // 4. WhatsApp Due Date Reminders (Due today or past due and unpaid)
        $todayDay = $now->day;
        $dueTodayInvoices = Invoice::with(['customer.village', 'customer.package'])
            ->where('period', $currentPeriod)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->whereDate('due_date', '<=', $now->toDateString())
            ->orderBy('due_date', 'asc')
            ->take(10)
            ->get();

        // 5. Chart 1: 6-Month Income vs Expense Trend
        $chartMonths = [];
        $chartIncome = [];
        $chartExpense = [];
        $chartProfit = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $label = $date->translatedFormat('M Y');

            $inc = FinancialTransaction::income()->forMonth($year, $month)->sum('amount');
            $exp = FinancialTransaction::expense()->forMonth($year, $month)->sum('amount');
            $prf = $inc - $exp;

            $chartMonths[] = $label;
            $chartIncome[] = (float) $inc;
            $chartExpense[] = (float) $exp;
            $chartProfit[] = (float) $prf;
        }

        // 6. Chart 2: Customer Distribution by Village
        $villages = Village::withCount('customers')->get();
        $villageLabels = $villages->pluck('name')->toArray();
        $villageCounts = $villages->pluck('customers_count')->toArray();

        // 7. Recent Invoices / Transactions
        $recentTransactions = FinancialTransaction::with(['user', 'invoice.customer'])
            ->latest('transaction_date')
            ->latest('id')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'omzetBulanIni',
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
