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
     * Generate WhatsApp reminder URL
     */
    public function getWhatsappUrlAttribute(): string
    {
        $customer = $this->customer;
        if (!$customer || empty($customer->clean_phone)) {
            return '#';
        }

        $companyName = Setting::get('company_name', 'Hokigo WiFi');
        $bankInfo = Setting::get('bank_accounts', "BCA: 1234567890 a.n Hokigo\nBRI: 9876543210 a.n Hokigo\nDANA/GoPay: 081234567890");
        $dueDate = $this->due_date ? $this->due_date->isoFormat('D MMMM Y') : '-';
        $formattedAmount = 'Rp ' . number_format($this->amount, 0, ',', '.');
        $periodName = \Carbon\Carbon::createFromFormat('Y-m', $this->period)->isoFormat('MMMM Y');

        $message = "Halo Kak *{$customer->name}*,\n\n"
            . "Kami dari *{$companyName}* menginformasikan tagihan internet WiFi Anda untuk periode *{$periodName}*:\n\n"
            . "🆔 ID Pelanggan: *{$customer->customer_code}*\n"
            . "📦 Paket: *" . ($customer->package?->name ?? 'Internet Bulanan') . "*\n"
            . "💰 Total Tagihan: *{$formattedAmount}*\n"
            . "📅 Jatuh Tempo: *{$dueDate}*\n"
            . "📌 Status: *" . ($this->status === 'paid' ? 'LUNAS ✅' : 'BELUM BAYAR ⏳') . "*\n\n"
            . "Pembayaran dapat ditransfer melalui:\n"
            . "{$bankInfo}\n\n"
            . "Mohon kirimkan bukti transfer setelah melakukan pembayaran ya Kak.\n"
            . "Terima kasih atas kepercayaannya menggunakan layanan *{$companyName}*! 🙏✨";

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

        $companyName = Setting::get('company_name', 'Hokigo WiFi');
        $formattedAmount = 'Rp ' . number_format($this->amount, 0, ',', '.');
        $paidDate = $this->paid_at ? $this->paid_at->isoFormat('D MMMM Y HH:mm') : now()->isoFormat('D MMMM Y HH:mm');
        $periodName = \Carbon\Carbon::createFromFormat('Y-m', $this->period)->isoFormat('MMMM Y');
        $method = strtoupper($this->payment_method ?? 'TUNAI');

        $message = "Halo Kak *{$customer->name}*,\n\n"
            . "✅ *BUKTI PEMBAYARAN TAGIHAN WIFI - LUNAS*\n"
            . "Terima kasih, pembayaran tagihan Anda telah kami terima:\n\n"
            . "🧾 No. Faktur: *{$this->invoice_number}*\n"
            . "🆔 ID Pelanggan: *{$customer->customer_code}*\n"
            . "📦 Paket: *" . ($customer->package?->name ?? 'Internet Bulanan') . "*\n"
            . "🗓️ Periode: *{$periodName}*\n"
            . "💳 Metode: *{$method}*\n"
            . "💰 Jumlah: *{$formattedAmount}*\n"
            . "⏰ Waktu Bayar: *{$paidDate}*\n\n"
            . "Layanan internet Anda tetap aktif dan lancar. Terima kasih telah setia bersama *{$companyName}*! 🚀🌐";

        return 'https://api.whatsapp.com/send?phone=' . $customer->clean_phone . '&text=' . rawurlencode($message);
    }
}
