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
     * Display broadcast dashboard with villages, stats, and logs.
     */
    public function index()
    {
        $villages = Village::withCount([
            'customers',
            'customers as active_customers_count' => function ($q) {
                $q->where('status', 'active');
            }
        ])->orderBy('name')->get();

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
            'stats',
            'logs',
            'isGatewayConfigured',
            'gatewayProvider'
        ));
    }

    /**
     * Send targeted WhatsApp broadcast to selected villages or all villages.
     */
    public function send(Request $request)
    {
        // 1. Validate Request
        $request->validate([
            'target_type' => ['required', 'in:selected,all'],
            'village_ids' => ['required_if:target_type,selected', 'array'],
            'village_ids.*' => ['integer', 'exists:villages,id'],
            'customer_status' => ['required', 'in:active,all'],
            'message' => ['required', 'string', 'min:5'],
            'title' => ['nullable', 'string', 'max:150'],
        ], [
            'village_ids.required_if' => 'Silakan pilih minimal satu desa tujuan broadcast.',
            'message.required' => 'Kolom pesan WhatsApp wajib diisi.',
            'message.min' => 'Pesan minimal terdiri dari 5 karakter.',
        ]);

        // 2. Check WhatsApp Gateway Configuration
        if (!WhatsAppGatewayService::isConfigured()) {
            return back()->withInput()->withErrors([
                'gateway' => 'Token WhatsApp Gateway belum diatur. Silakan atur token di menu Pengaturan terlebih dahulu agar pesan dapat dikirim.'
            ]);
        }

        // 3. Query Target Customers
        $targetType = $request->input('target_type');
        $customerStatus = $request->input('customer_status', 'active');
        $villageIds = (array) $request->input('village_ids', []);

        $query = Customer::with(['village', 'package']);

        if ($customerStatus === 'active') {
            $query->where('status', 'active');
        }

        if ($targetType === 'selected') {
            if (empty($villageIds)) {
                return back()->withInput()->withErrors([
                    'village_ids' => 'Pilih minimal satu desa sasaran untuk mengirim broadcast.'
                ]);
            }
            $query->whereIn('village_id', $villageIds);

            // Get names of selected villages
            $selectedVillages = Village::whereIn('id', $villageIds)->pluck('name')->toArray();
            $villageNamesStr = implode(', ', $selectedVillages);
        } else {
            $totalVillagesCount = Village::count();
            $villageNamesStr = "Semua Desa ({$totalVillagesCount} Wilayah)";
        }

        $customers = $query->get();

        if ($customers->isEmpty()) {
            return back()->withInput()->withErrors([
                'customers' => 'Tidak ditemukan pelanggan pada kriteria atau desa yang Anda pilih.'
            ]);
        }

        // 4. Send Broadcast Loop
        $rawMessage = trim($request->input('message'));
        $title = trim((string) $request->input('title'));
        if (empty($title)) {
            $title = $targetType === 'all' ? 'Broadcast Seluruh Wilayah' : 'Broadcast ' . (count($villageIds) > 1 ? count($villageIds) . ' Desa' : ($selectedVillages[0] ?? 'Desa'));
        }

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

        // 5. Store Broadcast History Log
        try {
            BroadcastLog::create([
                'title' => $title,
                'message' => $rawMessage,
                'target_type' => $targetType,
                'village_names' => $villageNamesStr,
                'village_ids' => $targetType === 'selected' ? json_encode($villageIds) : null,
                'customer_status' => $customerStatus,
                'total_recipients' => $customers->count(),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'created_by' => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save BroadcastLog: ' . $e->getMessage());
        }

        // 6. Return response
        if ($sentCount > 0) {
            $statusMsg = "Siaran WhatsApp berhasil dikirim! Berhasil: {$sentCount} pelanggan di [{$villageNamesStr}].";
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
