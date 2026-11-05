<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);
        $tab = $request->get('tab', 'all'); // 'all', 'income', 'expense'

        // Check custom date range
        $useRange = $request->filled('start_date') && $request->filled('end_date');
        if ($useRange) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        } else {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        }

        // Query transactions within date range
        $query = FinancialTransaction::with(['user', 'invoice.customer'])
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($tab === 'income') {
            $query->where('type', 'income');
        } elseif ($tab === 'expense') {
            $query->where('type', 'expense');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->paginate(30)->withQueryString();

        // Calculate totals for selected period in ONE single query
        $totals = FinancialTransaction::whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense
            ")->first();

        $totalIncome = (float) ($totals->total_income ?? 0);
        $totalExpense = (float) ($totals->total_expense ?? 0);
        $netProfit = $totalIncome - $totalExpense;
        $profitMargin = $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 1) : 0;

        // Group by categories for breakdown
        $incomeByCategory = FinancialTransaction::income()
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $expenseByCategory = FinancialTransaction::expense()
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        // Years list for filter
        $years = range($now->year, $now->year - 3);

        return view('finance.index', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'netProfit',
            'profitMargin',
            'incomeByCategory',
            'expenseByCategory',
            'month',
            'year',
            'years',
            'startDate',
            'endDate',
            'useRange',
            'tab'
        ));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:60'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        $code = 'EXP-' . Carbon::parse($validated['transaction_date'])->format('Ymd') . '-' . rand(100, 999);

        FinancialTransaction::create([
            'transaction_code' => $code,
            'type' => 'expense',
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'description' => $validated['description'],
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Pengeluaran sebesar Rp ' . number_format($validated['amount'], 0, ',', '.') . ' berhasil dicatat!');
    }

    public function storeIncome(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:60'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        $code = 'INC-' . Carbon::parse($validated['transaction_date'])->format('Ymd') . '-' . rand(100, 999);

        FinancialTransaction::create([
            'transaction_code' => $code,
            'type' => 'income',
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'description' => $validated['description'],
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Pemasukan tambahan sebesar Rp ' . number_format($validated['amount'], 0, ',', '.') . ' berhasil dicatat!');
    }

    public function destroy(FinancialTransaction $transaction)
    {
        // Don't allow deleting invoice-linked transactions directly; must unmark invoice or explicitly remove
        if ($transaction->invoice_id) {
            $invoice = $transaction->invoice;
            if ($invoice) {
                $invoice->update(['status' => 'unpaid', 'paid_at' => null]);
            }
        }

        $code = $transaction->transaction_code;
        $transaction->delete();

        return back()->with('success', "Transaksi {$code} berhasil dihapus.");
    }

    public function exportCsv(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $transactions = FinancialTransaction::with(['user', 'invoice.customer'])
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('transaction_date', 'asc')
            ->get();

        $filename = "laporan-keuangan-hokigo-{$year}-{$month}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Tanggal', 'Kode Transaksi', 'Tipe', 'Kategori', 'Keterangan', 'Metode', 'Nominal (Rp)', 'Pencatat']);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->transaction_date->format('Y-m-d'),
                    $t->transaction_code,
                    $t->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                    $t->category,
                    $t->description,
                    strtoupper($t->payment_method ?? '-'),
                    $t->amount,
                    $t->user?->name ?? 'Sistem',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function resetData(Request $request)
    {
        if (!auth()->user()->isOwner()) {
            abort(403, 'Hanya Owner yang memiliki wewenang untuk mereset data keuangan.');
        }

        $request->validate([
            'confirmation' => ['required', 'string', 'in:RESET,reset,RESET KEUANGAN'],
        ], [
            'confirmation.in' => 'Ketik kata "RESET" untuk mengonfirmasi pengosongan data keuangan.',
        ]);

        // 1. Delete all financial transactions
        FinancialTransaction::query()->delete();

        // 2. Clear or reset invoices
        if ($request->boolean('delete_invoices', true)) {
            \App\Models\Invoice::query()->delete();
        } else {
            \App\Models\Invoice::query()->update([
                'status' => 'unpaid',
                'paid_at' => null,
                'payment_method' => null,
            ]);
        }

        // 3. Reset voucher sales if requested
        if ($request->boolean('delete_vouchers', false)) {
            \App\Models\VoucherSale::query()->delete();
        }

        return redirect()->route('finance.index')->with('success', 'Semua data transaksi dan keuangan berhasil di-0 kan! Omzet, pengeluaran, dan laba bersih kini mulai murni dari Rp 0.');
    }
}
