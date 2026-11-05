<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'period',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'payment_method',
        'notes',
        'wa_sent_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'wa_sent_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(FinancialTransaction::class);
    }

    /**
     * Generate WhatsApp reminder URL matching the exact format:
     * Yth, Pelanggan Hokigo Starlink..
     * Sebagai pengingat, Hari ini pembayaran wifi🙏🏻
     * Bisa dibayarkan melalui rekening berikut:
     * MUHAMMAD IRZAQ IRAWAN 
     * 7233297792 BSI
     */
    public function getWhatsappUrlAttribute(): string
    {
        $customer = $this->customer;
        if (!$customer || empty($customer->clean_phone)) {
            return '#';
        }

        $companyName = Setting::get('company_name', 'Hokigo Starlink');
        $bankInfo = Setting::get('bank_accounts', "MUHAMMAD IRZAQ IRAWAN\n7233297792 BSI");
        $dueDate = $this->due_date ? $this->due_date->isoFormat('D MMMM Y') : '-';
        $formattedAmount = 'Rp ' . number_format($this->amount, 0, ',', '.');
        $packageName = $customer->package?->name ?? 'Internet Bulanan';

        $message = "Yth, Pelanggan {$companyName}..\n"
            . "*{$customer->name}* (ID: *{$customer->customer_code}*)\n\n"
            . "Sebagai pengingat, Hari ini pembayaran wifi🙏🏻\n\n"
            . "📦 Paket: *{$packageName}*\n"
            . "💰 Total Tagihan: *{$formattedAmount}*\n"
            . "📅 Jatuh Tempo: *{$dueDate}*\n\n"
            . "Bisa dibayarkan melalui rekening berikut:\n\n"
            . "{$bankInfo}\n\n"
            . "Konfirmasi & Bukti Transfer kirim ke WA Admin:\n"
            . "https://wa.me/6281227391262\n\n"
            . "Terima kasih! 🙏✨";

        return 'https://api.whatsapp.com/send?phone=' . $customer->clean_phone . '&text=' . rawurlencode($message);
    }

    /**
     * Generate WhatsApp receipt URL after payment
     */
    public function getWhatsappReceiptUrlAttribute(): string
    {
        $customer = $this->customer;
        if (!$customer || empty($customer->clean_phone)) {
            return '#';
        }

        $companyName = Setting::get('company_name', 'Hokigo Starlink');
        $formattedAmount = 'Rp ' . number_format($this->amount, 0, ',', '.');
        $paidDate = $this->paid_at ? $this->paid_at->isoFormat('D MMMM Y HH:mm') : now()->isoFormat('D MMMM Y HH:mm');
        $periodName = \Carbon\Carbon::createFromFormat('Y-m', $this->period)->isoFormat('MMMM Y');
        $method = strtoupper($this->payment_method ?? 'TUNAI');
        $packageName = $customer->package?->name ?? 'Internet Bulanan';

        $message = "Yth, Pelanggan {$companyName}..\n"
            . "*{$customer->name}* (ID: *{$customer->customer_code}*)\n\n"
            . "✅ *BUKTI PEMBAYARAN WIFI - LUNAS*\n"
            . "Terima kasih, pembayaran tagihan Anda telah kami terima:\n\n"
            . "🧾 No. Faktur: *{$this->invoice_number}*\n"
            . "📦 Paket: *{$packageName}*\n"
            . "🗓️ Periode: *{$periodName}*\n"
            . "💳 Metode: *{$method}*\n"
            . "💰 Jumlah: *{$formattedAmount}*\n"
            . "⏰ Waktu: *{$paidDate}*\n\n"
            . "Layanan internet Anda tetap aktif dan lancar. Terima kasih telah setia bersama *{$companyName}*! 🚀🌐";

        return 'https://api.whatsapp.com/send?phone=' . $customer->clean_phone . '&text=' . rawurlencode($message);
    }
}
