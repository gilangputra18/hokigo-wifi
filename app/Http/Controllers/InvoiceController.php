<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $currentPeriod = Carbon::now()->format('Y-m');
        $selectedPeriod = $request->get('period', $currentPeriod);
        $villages = Village::orderBy('name')->get();

        $query = Invoice::with([
            'customer' => function ($q) {
                $q->select('id', 'customer_code', 'name', 'phone', 'village_id', 'package_id');
            },
            'customer.village:id,name',
            'customer.package:id,name',
            'creator:id,name'
        ])->where('period', $selectedPeriod);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter village
        if ($request->filled('village_id')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('village_id', $request->village_id);
            });
        }

        // Search customer name / code / phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderBy('due_date', 'asc')->paginate(25)->withQueryString();

        // Statistics for current selected period in ONE single query
        $statsRow = Invoice::where('period', $selectedPeriod)
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(amount), 0) as total_amount,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as paid_amount,
                COUNT(CASE WHEN status IN ('unpaid', 'overdue') THEN 1 END) as unpaid_count,
                COALESCE(SUM(CASE WHEN status IN ('unpaid', 'overdue') THEN amount ELSE 0 END), 0) as unpaid_amount
            ")->first();

        $stats = [
            'total_amount' => (float) ($statsRow->total_amount ?? 0),
            'total_count' => (int) ($statsRow->total_count ?? 0),
            'paid_amount' => (float) ($statsRow->paid_amount ?? 0),
            'paid_count' => (int) ($statsRow->paid_count ?? 0),
            'unpaid_amount' => (float) ($statsRow->unpaid_amount ?? 0),
            'unpaid_count' => (int) ($statsRow->unpaid_count ?? 0),
        ];

        // Available periods for dropdown (last 12 months)
        $periods = [];
        for ($i = 0; $i < 12; $i++) {
            $d = Carbon::now()->subMonths($i);
            $periods[$d->format('Y-m')] = $d->translatedFormat('F Y');
        }

        return view('invoices.index', compact('invoices', 'selectedPeriod', 'periods', 'villages', 'stats'));
    }

    /**
     * Generate bulk invoices for all active customers in a period
     */
    public function generate(Request $request)
    {
        $request->validate([
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $period = $request->period;
        $activeCustomers = Customer::whereIn('status', ['active', 'isolated'])->get();
        $createdCount = 0;

        foreach ($activeCustomers as $customer) {
            $exists = Invoice::where('customer_id', $customer->id)
                ->where('period', $period)
                ->exists();

            if (!$exists) {
                $day = min($customer->due_date_day ?: 10, 28);
                $dueDate = Carbon::createFromFormat('Y-m', $period)->setDay($day);

                $status = Carbon::now()->gt($dueDate) ? 'overdue' : 'unpaid';

                Invoice::create([
                    'invoice_number' => 'INV-' . str_replace('-', '', $period) . '-' . str_pad($customer->id, 4, '0', STR_PAD_LEFT),
                    'customer_id' => $customer->id,
                    'period' => $period,
                    'amount' => $customer->monthly_fee,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'status' => $status,
                    'notes' => 'Tagihan bulanan otomatis',
                    'created_by' => auth()->id(),
                ]);

                $createdCount++;
            }
        }

        return redirect()->route('invoices.index', ['period' => $period])
            ->with('success', "Berhasil membuat {$createdCount} tagihan untuk periode {$period}!");
    }

    /**
     * Mark invoice as paid and record income transaction
     */
    public function markPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'paid_at' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $paidAt = $request->filled('paid_at') ? Carbon::parse($request->paid_at) : now();

            $invoice->update([
                'status' => 'paid',
                'payment_method' => $request->payment_method,
                'paid_at' => $paidAt,
                'notes' => $request->notes ?: $invoice->notes,
            ]);

            // Create or update FinancialTransaction
            $transactionCode = 'TRX-' . $invoice->invoice_number;
            FinancialTransaction::updateOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'transaction_code' => $transactionCode,
                    'type' => 'income',
                    'category' => 'Tagihan WiFi',
                    'amount' => $invoice->amount,
                    'transaction_date' => $paidAt->format('Y-m-d'),
                    'user_id' => auth()->id(),
                    'payment_method' => $request->payment_method,
                    'description' => "Pembayaran tagihan {$invoice->customer->name} ({$invoice->customer->customer_code}) periode {$invoice->period}",
                ]
            );
        });

        return back()->with('success', "Tagihan {$invoice->invoice_number} berhasil ditandai LUNAS! Omzet bulan ini otomatis diperbarui.");
    }

    /**
     * Revert invoice payment
     */
    public function markUnpaid(Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            // Delete associated transaction
            FinancialTransaction::where('invoice_id', $invoice->id)->delete();

            $dueDate = Carbon::parse($invoice->due_date);
            $status = Carbon::now()->gt($dueDate) ? 'overdue' : 'unpaid';

            $invoice->update([
                'status' => $status,
                'paid_at' => null,
                'payment_method' => null,
            ]);
        });

        return back()->with('success', "Status tagihan {$invoice->invoice_number} dikembalikan menjadi BELUM BAYAR.");
    }

    /**
     * Mark WhatsApp sent timestamp
     */
    public function markWaSent(Invoice $invoice)
    {
        $invoice->update([
            'wa_sent_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status pengiriman WhatsApp dicatat',
            'sent_at' => $invoice->wa_sent_at->isoFormat('D MMM Y HH:mm'),
        ]);
    }

    /**
     * Due date WhatsApp reminders center
     */
    public function reminders(Request $request)
    {
        $now = Carbon::now();
        $currentPeriod = $now->format('Y-m');
        $filter = $request->get('filter', 'today_or_overdue');

        $query = Invoice::with(['customer.village', 'customer.package'])
            ->whereIn('status', ['unpaid', 'overdue']);

        if ($filter === 'today') {
            $query->whereDate('due_date', $now->toDateString());
        } elseif ($filter === 'overdue') {
            $query->whereDate('due_date', '<', $now->toDateString());
        } else {
            // today or overdue
            $query->whereDate('due_date', '<=', $now->toDateString());
        }

        $invoices = $query->orderBy('due_date', 'asc')->paginate(30)->withQueryString();

        $todayCount = Invoice::whereIn('status', ['unpaid', 'overdue'])
            ->whereDate('due_date', $now->toDateString())
            ->count();

        $overdueCount = Invoice::whereIn('status', ['unpaid', 'overdue'])
            ->whereDate('due_date', '<', $now->toDateString())
            ->count();

        return view('invoices.reminders', compact('invoices', 'todayCount', 'overdueCount', 'filter', 'currentPeriod'));
    }

    /**
     * Print view for receipt / invoice (Thermal & A4)
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['customer.village', 'customer.package', 'creator']);
        $companyName = Setting::get('company_name', 'Hokigo WiFi');
        $companyTagline = Setting::get('company_tagline', 'Internet Cepat & Hemat');
        $companyPhone = Setting::get('company_phone', '081234567890');
        $companyAddress = Setting::get('company_address', 'Kantor Hokigo');
        $bankAccounts = Setting::get('bank_accounts', 'BCA / BRI / DANA');

        return view('invoices.print', compact(
            'invoice',
            'companyName',
            'companyTagline',
            'companyPhone',
            'companyAddress',
            'bankAccounts'
        ));
    }

    public function destroy(Invoice $invoice)
    {
        // Delete related transaction first
        FinancialTransaction::where('invoice_id', $invoice->id)->delete();
        $num = $invoice->invoice_number;
        $invoice->delete();

        return back()->with('success', "Tagihan {$num} berhasil dihapus.");
    }
}
