<?php

namespace App\Http\Controllers;

use App\Models\BroadcastLog;
use App\Models\Customer;
use App\Models\Village;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    /**
     * Display broadcast dashboard with villages, detailed customers, stats, and logs.
     */
    public function index()
    {
        $villages = Village::with([
            'customers' => function ($q) {
                $q->select('id', 'name', 'customer_code', 'phone', 'village_id', 'status', 'address', 'package_id')
                    ->with('package:id,name')
                    ->orderBy('name');
            }
        ])
        ->withCount([
            'customers',
            'customers as active_customers_count' => function ($q) {
                $q->where('status', 'active');
            }
        ])->orderBy('name')->get();

        // Check if there are any customers without village
        $noVillageCustomers = Customer::whereNull('village_id')
            ->select('id', 'name', 'customer_code', 'phone', 'village_id', 'status', 'address', 'package_id')
            ->with('package:id,name')
            ->orderBy('name')
            ->get();

        $stats = [
            'total_villages' => $villages->count(),
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where('status', 'active')->count(),
            'total_broadcasts' => BroadcastLog::count(),
        ];

        $logs = BroadcastLog::with('creator')->latest()->take(20)->get();
        $isGatewayConfigured = WhatsAppGatewayService::isConfigured();
        $gatewayProvider = WhatsAppGatewayService::getProvider();

        return view('broadcast.index', compact(
            'villages',
            'noVillageCustomers',
            'stats',
            'logs',
            'isGatewayConfigured',
            'gatewayProvider'
        ));
    }

    /**
     * Send targeted WhatsApp broadcast to specific customers or selected villages.
     */
    public function send(Request $request)
    {
        // 1. Validate Request
        $request->validate([
            'customer_ids' => ['required_without:village_ids', 'array'],
            'customer_ids.*' => ['integer', 'exists:customers,id'],
            'village_ids' => ['nullable', 'array'],
            'village_ids.*' => ['integer', 'exists:villages,id'],
            'message' => ['required', 'string', 'min:5'],
            'title' => ['nullable', 'string', 'max:150'],
        ], [
            'customer_ids.required_without' => 'Silakan pilih minimal satu orang pelanggan penerima broadcast.',
            'customer_ids.array' => 'Daftar pelanggan tidak valid.',
            'message.required' => 'Kolom pesan WhatsApp wajib diisi.',
            'message.min' => 'Pesan minimal terdiri dari 5 karakter.',
        ]);

        // 2. Check WhatsApp Gateway Configuration
        if (!WhatsAppGatewayService::isConfigured()) {
            return back()->withInput()->withErrors([
                'gateway' => 'Token WhatsApp Gateway belum diatur. Silakan atur token di menu Pengaturan terlebih dahulu agar pesan dapat dikirim.'
            ]);
        }

        // 3. Determine target customer IDs
        $customerIds = (array) $request->input('customer_ids', []);

        if (empty($customerIds) && !empty($request->input('village_ids'))) {
            // Fallback: fetch active customers from selected villages
            $customerIds = Customer::whereIn('village_id', $request->input('village_ids'))
                ->when($request->input('customer_status', 'active') === 'active', fn($q) => $q->where('status', 'active'))
                ->pluck('id')
                ->toArray();
        }

        if (empty($customerIds)) {
            return back()->withInput()->withErrors([
                'customers' => 'Pilih minimal satu orang pelanggan sasaran untuk mengirim broadcast.'
            ]);
        }

        $customers = Customer::whereIn('id', $customerIds)
            ->with(['village', 'package'])
            ->get();

        if ($customers->isEmpty()) {
            return back()->withInput()->withErrors([
                'customers' => 'Tidak ditemukan data pelanggan yang valid.'
            ]);
        }

        // 4. Group village summary for logs
        $groupedByVillage = $customers->groupBy(fn($c) => $c->village?->name ?? 'Luar Wilayah');
        $villageSummaryParts = [];
        foreach ($groupedByVillage as $vName => $custsInVillage) {
            $villageSummaryParts[] = "{$vName} ({$custsInVillage->count()} org)";
        }
        $villageNamesStr = implode(', ', $villageSummaryParts);

        $rawMessage = trim($request->input('message'));
        $title = trim((string) $request->input('title'));
        if (empty($title)) {
            $title = count($groupedByVillage) === 1
                ? 'Broadcast ' . $groupedByVillage->keys()->first() . ' (' . $customers->count() . ' Orang)'
                : 'Broadcast ' . count($groupedByVillage) . ' Desa (' . $customers->count() . ' Orang)';
        }

        // 5. Send Broadcast Loop
        $sentCount = 0;
        $failedCount = 0;
        $failedDetails = [];

        foreach ($customers as $customer) {
            $phone = trim((string) $customer->phone);
            if (empty($phone)) {
                $failedCount++;
                $failedDetails[] = "{$customer->name} (Nomor HP belum terdaftar)";
                continue;
            }

            // Replace dynamic placeholders
            $personalizedMessage = str_replace(
                ['{nama}', '{desa}', '{kode}', '{paket}'],
                [
                    $customer->name,
                    $customer->village?->name ?? 'Wilayah Layanan',
                    $customer->customer_code ?? '-',
                    $customer->package?->name ?? 'Internet WiFi',
                ],
                $rawMessage
            );

            try {
                $res = WhatsAppGatewayService::sendMessage($customer->phone, $personalizedMessage);
                if (!empty($res['success'])) {
                    $sentCount++;
                } else {
                    $failedCount++;
                    $failedDetails[] = "{$customer->name}: " . ($res['message'] ?? 'Gagal');
                }
            } catch (\Throwable $e) {
                $failedCount++;
                $failedDetails[] = "{$customer->name}: " . $e->getMessage();
            }

            // Sleep 250ms between sends to avoid rate-limiting
            usleep(250000);
        }

        // 6. Store Broadcast History Log
        try {
            BroadcastLog::create([
                'title' => $title,
                'message' => $rawMessage,
                'target_type' => 'specific_customers',
                'village_names' => $villageNamesStr,
                'village_ids' => json_encode($customers->pluck('village_id')->unique()->filter()->values()->toArray()),
                'customer_status' => 'custom_selection',
                'total_recipients' => $customers->count(),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'created_by' => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save BroadcastLog: ' . $e->getMessage());
        }

        // 7. Return response
        if ($sentCount > 0) {
            $statusMsg = "Broadcast WhatsApp berhasil diproses! Berhasil terkirim ke {$sentCount} pelanggan di [{$villageNamesStr}].";
            if ($failedCount > 0) {
                $statusMsg .= " (Catatan: {$failedCount} nomor gagal/tidak valid).";
            }
            return redirect()->route('broadcast.index')->with('success', $statusMsg);
        } else {
            return back()->withInput()->withErrors([
                'send_error' => "Gagal mengirim broadcast ke {$failedCount} pelanggan. Detail kesalahan: " . implode(', ', array_slice($failedDetails, 0, 3))
            ]);
        }
    }

    /**
     * Delete a broadcast history log.
     */
    public function destroy(BroadcastLog $log)
    {
        $log->delete();
        return back()->with('success', 'Riwayat broadcast berhasil dihapus.');
    }
}
