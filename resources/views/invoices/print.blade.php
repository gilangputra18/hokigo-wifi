<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Tagihan - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f1f5f9;
            color: #111;
            margin: 0;
            padding: 20px;
            font-size: 13px;
        }
        .receipt-container {
            max-width: 380px;
            margin: 0 auto;
            background: #fff;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-radius: 8px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #666; margin: 12px 0; }
        .double-line { border-bottom: 2px double #333; margin: 12px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 4px;
        }
        .badge-paid { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-unpaid { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .print-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #1055CC;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 16px;
        }
        @media print {
            body { background: none; padding: 0; }
            .receipt-container { box-shadow: none; padding: 10px; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        
        <div class="no-print">
            <button class="print-btn" onclick="window.print()">🖨️ Cetak Struk (Print / PDF)</button>
        </div>

        <div class="text-center">
            <h2 style="margin: 0; font-size: 20px;">{{ $companyName }}</h2>
            <div style="font-size: 11px; color: #444; margin-top: 2px;">{{ $companyTagline }}</div>
            <div style="font-size: 11px; color: #666;">{{ $companyAddress }}</div>
            <div style="font-size: 11px; color: #666;">WA: {{ $companyPhone }}</div>
        </div>

        <div class="double-line"></div>

        <div class="row">
            <span>No. Faktur:</span>
            <span class="font-bold">{{ $invoice->invoice_number }}</span>
        </div>
        <div class="row">
            <span>Tanggal Cetak:</span>
            <span>{{ now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span>Periode Tagihan:</span>
            <span class="font-bold">{{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->period)->translatedFormat('F Y') }}</span>
        </div>

        <div class="line"></div>

        <div class="row">
            <span>ID Pelanggan:</span>
            <span class="font-bold">{{ $invoice->customer->customer_code }}</span>
        </div>
        <div class="row">
            <span>Nama Pelanggan:</span>
            <span class="font-bold">{{ $invoice->customer->name }}</span>
        </div>
        <div class="row">
            <span>Desa / Dusun:</span>
            <span>{{ $invoice->customer->village?->name ?? 'Luar Wilayah' }}</span>
        </div>
        <div class="row">
            <span>Paket WiFi:</span>
            <span>{{ $invoice->customer->package?->name ?? 'Internet' }} ({{ $invoice->customer->package?->speed ?? '-' }})</span>
        </div>
        <div class="row">
            <span>Jatuh Tempo:</span>
            <span>{{ $invoice->due_date->format('d/m/Y') }}</span>
        </div>

        <div class="line"></div>

        <div class="row" style="font-size: 15px;">
            <span class="font-bold">TOTAL TAGIHAN:</span>
            <span class="font-bold">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
        </div>

        <div class="line"></div>

        <div class="text-center" style="margin: 12px 0;">
            <span>STATUS PEMBAYARAN:</span><br>
            @if($invoice->status === 'paid')
                <div class="badge badge-paid">*** LUNAS ***</div>
                <div style="font-size: 11px; margin-top: 4px; color: #444;">
                    Metode: {{ strtoupper($invoice->payment_method ?? 'Cash') }} &bull; 
                    Tgl: {{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : '-' }}
                </div>
            @else
                <div class="badge badge-unpaid">*** BELUM LUNAS ***</div>
            @endif
        </div>

        @if($invoice->status !== 'paid')
            <div style="font-size: 11px; background: #f8fafc; padding: 8px; border-radius: 4px; margin-top: 10px;">
                <div class="font-bold">Rekening Pembayaran:</div>
                <pre style="margin: 4px 0; font-family: inherit; font-size: 11px; white-space: pre-wrap;">{{ $bankAccounts }}</pre>
            </div>
        @endif

        <div class="double-line"></div>

        <div class="text-center" style="font-size: 11px; color: #555;">
            Terima kasih atas kepercayaan Anda menggunakan layanan internet <strong>{{ $companyName }}</strong>.<br>
            Simpan struk ini sebagai bukti pembayaran yang sah.
        </div>

    </div>

</body>
</html>
