<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayService
{
    /**
     * Check if WhatsApp Gateway is configured
     */
    public static function isConfigured(): bool
    {
        $token = Setting::get('wa_gateway_token');
        return !empty($token);
    }

    /**
     * Get active provider
     */
    public static function getProvider(): string
    {
        return Setting::get('wa_gateway_provider', 'fonnte');
    }

    /**
     * Format Indonesian phone number to international 62 format
     */
    public static function formatPhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }
        return $clean;
    }

    /**
     * Send arbitrary WhatsApp message via configured Gateway
     */
    public static function sendMessage(string $phone, string $message): array
    {
        $token = trim((string) Setting::get('wa_gateway_token', ''));
        $provider = static::getProvider();

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token WhatsApp Gateway belum diatur. Silakan atur token di menu Pengaturan.',
            ];
        }

        $formattedPhone = static::formatPhone($phone);
        if (empty($formattedPhone) || strlen($formattedPhone) < 9) {
            return [
                'success' => false,
                'message' => "Nomor WhatsApp tidak valid: {$phone}",
            ];
        }

        try {
            if ($provider === 'fonnte') {
                $response = Http::timeout(15)
                    ->withHeaders([
                        'Authorization' => $token,
                    ])
                    ->asForm()
                    ->post('https://api.fonnte.com/send', [
                        'target' => $formattedPhone,
                        'message' => $message,
                        'countryCode' => '62',
                    ]);

                $data = $response->json();
                $isSuccess = is_array($data) && (($data['status'] ?? false) === true);

                $reason = $data['reason'] ?? ($data['message'] ?? 'Gagal mengirim pesan via Fonnte.');

                return [
                    'success' => $isSuccess,
                    'message' => $isSuccess ? 'Pesan WhatsApp berhasil dikirim via Fonnte!' : $reason,
                    'response' => $data,
                ];
            } elseif ($provider === 'wablas') {
                $domain = Setting::get('wa_wablas_domain', 'https://kudus.wablas.com');
                $url = rtrim($domain, '/') . '/api/send-message';

                $response = Http::timeout(15)
                    ->withHeaders([
                        'Authorization' => $token,
                    ])
                    ->asForm()
                    ->post($url, [
                        'phone' => $formattedPhone,
                        'message' => $message,
                    ]);

                $data = $response->json();
                $isSuccess = is_array($data) && (($data['status'] ?? false) === true || ($data['status'] ?? '') === 'success');
                $reason = $data['message'] ?? 'Gagal mengirim pesan via Wablas.';

                return [
                    'success' => $isSuccess,
                    'message' => $isSuccess ? 'Pesan WhatsApp berhasil dikirim via Wablas!' : $reason,
                    'response' => $data,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Provider '{$provider}' belum didukung.",
                ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke WhatsApp Gateway: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send bill reminder for a single invoice
     */
    public static function sendInvoiceReminder(Invoice $invoice): array
    {
        $customer = $invoice->customer;
        if (!$customer || empty($customer->clean_phone)) {
            return [
                'success' => false,
                'message' => 'Pelanggan tidak memiliki nomor WhatsApp yang valid.',
            ];
        }

        $companyName = Setting::get('company_name', 'Hokigo WiFi');
        $bankInfo = Setting::get('bank_accounts', "MUHAMMAD IRZAQ IRAWAN\n7233297792 BSI");
        $dueDate = $invoice->due_date ? $invoice->due_date->isoFormat('D MMMM Y') : '-';
        $formattedAmount = 'Rp ' . number_format($invoice->amount, 0, ',', '.');
        $packageName = $customer->package?->name ?? 'Internet Bulanan';

        $message = "Yth. Pelanggan {$companyName}\n"
            . "*{$customer->name}* (ID: *{$customer->customer_code}*)\n\n"
            . "Sebagai pengingat, hari ini adalah jadwal pembayaran WiFi:\n\n"
            . "📦 Paket: *{$packageName}*\n"
            . "💰 Total Tagihan: *{$formattedAmount}*\n"
            . "📅 Jatuh Tempo: *{$dueDate}*\n\n"
            . "Bisa dibayarkan melalui transfer rekening:\n\n"
            . "{$bankInfo}\n\n"
            . "Konfirmasi & kirim bukti transfer ke WA Admin:\n"
            . "https://wa.me/6281227391262\n\n"
            . "Terima kasih telah berlangganan di Hokigo! 🙏";

        $res = static::sendMessage($customer->phone, $message);
        if ($res['success']) {
            $invoice->update(['wa_sent_at' => now()]);
        }
        return $res;
    }

    /**
     * Send batch reminders for invoices due today or overdue
     */
    public static function sendBatchReminders(string $filter = 'today'): array
    {
        if (!static::isConfigured()) {
            return [
                'success' => false,
                'message' => 'Token WhatsApp Gateway belum diatur di menu Pengaturan.',
                'sent_count' => 0,
                'failed_count' => 0,
            ];
        }

        $now = Carbon::now();
        $query = Invoice::with(['customer.package', 'customer.village'])
            ->whereIn('status', ['unpaid', 'overdue']);

        if ($filter === 'today') {
            $query->whereDate('due_date', $now->toDateString());
        } elseif ($filter === 'overdue') {
            $query->whereDate('due_date', '<', $now->toDateString());
        } else {
            // today or overdue
            $query->whereDate('due_date', '<=', $now->toDateString());
        }

        $invoices = $query->get();

        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($invoices as $inv) {
            $res = static::sendInvoiceReminder($inv);
            if ($res['success']) {
                $sentCount++;
            } else {
                $failedCount++;
                $errors[] = "{$inv->customer?->name}: {$res['message']}";
            }
            // Sleep 500ms between sends to avoid rate-limiting
            usleep(500000);
        }

        return [
            'success' => $sentCount > 0 || $failedCount === 0,
            'message' => "Proses selesai: {$sentCount} pesan terkirim, {$failedCount} gagal.",
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'total_target' => $invoices->count(),
            'errors' => array_slice($errors, 0, 5),
        ];
    }
}
