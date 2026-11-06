@extends('layouts.app')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Bisnis & Operasional')

@section('content')
<div class="space-y-8">

    <!-- 1. Top Quick Action Banner -->
    <div class="bg-gradient-to-r from-brand-dark via-slate-900 to-brand-blue rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-blue-200 text-xs font-semibold uppercase tracking-wider backdrop-blur-md mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Periode Berjalan: {{ now()->translatedFormat('F Y') }}
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Hokigo WiFi Control Center</h2>
                <p class="text-slate-300 text-sm mt-1 max-w-xl">
                    Pantau omzet harian, konfirmasi pembayaran pelanggan, kirim reminder jatuh tempo via WhatsApp, dan kelola jaringan desa secara real-time.
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('customers.create') }}" class="px-4 py-2.5 rounded-xl bg-brand-blue hover:bg-blue-600 font-bold text-xs flex items-center gap-2 shadow-lg shadow-blue-500/25 transition">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Tambah Pelanggan</span>
                </a>
                <a href="{{ route('invoices.reminders') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/25 transition">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>WA Jatuh Tempo ({{ $overdueCount }})</span>
                </a>
                <a href="{{ route('finance.index') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 font-bold text-xs flex items-center gap-2 backdrop-blur-sm transition">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Catat Keuangan</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Financial & Core Metrics Cards (Requirements 3, 4, 5) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Omzet Bulan Ini -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Omzet Bulan Ini</span>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-blue flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 tracking-tight">
                    Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-xs">
                    @if($growthOmzet >= 0)
                        <span class="text-emerald-600 font-bold flex items-center">
                            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 inline"></i> +{{ $growthOmzet }}%
                        </span>
                    @else
                        <span class="text-rose-600 font-bold flex items-center">
                            <i data-lucide="arrow-down-right" class="w-3.5 h-3.5 inline"></i> {{ $growthOmzet }}%
                        </span>
                    @endif
                    <span class="text-slate-400">vs bulan lalu</span>
                </div>
            </div>

            <!-- Pemisah Khusus: Langganan vs Voucher -->
            <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5 text-xs">
                <div class="flex items-center justify-between text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span> Pelanggan:
                    </span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($omzetLanggananBulanIni, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Voucher:
                    </span>
                    <span class="font-bold text-amber-700">Rp {{ number_format($omzetVoucherBulanIni, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Pengeluaran Bulan Ini -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pengeluaran Bulan Ini</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-2">Biaya bandwidth, ODP & operasional</p>
        </div>

        <!-- Laba Bersih Bulan Ini -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Laba Bersih (Profit)</span>
                <div class="w-10 h-10 rounded-xl {{ $labaBersihBulanIni >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold {{ $labaBersihBulanIni >= 0 ? 'text-emerald-600' : 'text-rose-600' }} tracking-tight">
                Rp {{ number_format($labaBersihBulanIni, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                <span class="px-2 py-0.5 rounded font-bold {{ $labaBersihBulanIni >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $labaBersihBulanIni >= 0 ? 'Surplus' : 'Defisit' }}
                </span>
                <span class="text-slate-400">(Omzet - Biaya)</span>
            </div>
        </div>

        <!-- Pelanggan Aktif -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pelanggan</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 tracking-tight">
                {{ $activeCustomers }} <span class="text-sm font-normal text-slate-500">/ {{ $totalCustomers }} Total</span>
            </div>
            <div class="flex items-center gap-2 mt-2 text-xs font-medium">
                <span class="text-emerald-600 font-bold">{{ $activeCustomers }} Aktif</span> &bull; 
                <span class="text-amber-600">{{ $isolatedCustomers }} Isolir</span> &bull; 
                <span class="text-slate-400">{{ $inactiveCustomers }} Nonaktif</span>
            </div>
        </div>

    </div>

    <!-- 3. Status Tagihan Langganan Bulanan Periode Ini -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Total Tagihan Terbit -->
        <div class="bg-slate-900 text-white rounded-2xl p-5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Tagihan Langganan Terbit</span>
                <div class="text-xl font-bold text-white mt-1">Rp {{ number_format($totalTagihanNominal, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ $tagihanLunasCount + $tagihanBelumBayarCount }} Lembar Tagihan Pelanggan</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-blue-400">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Sudah Lunas -->
        <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-xs text-emerald-700 uppercase font-bold tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tagihan Sudah Lunas
                </span>
                <div class="text-xl font-bold text-emerald-900 mt-1">Rp {{ number_format($tagihanLunasNominal, 0, ',', '.') }}</div>
                <div class="text-xs text-emerald-700 font-semibold mt-0.5">{{ $tagihanLunasCount }} Pelanggan Sudah Bayar</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Belum Bayar / Menunggak -->
        <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-xs text-amber-800 uppercase font-bold tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Belum Bayar (Piutang)
                </span>
                <div class="text-xl font-bold text-amber-950 mt-1">Rp {{ number_format($tagihanBelumBayarNominal, 0, ',', '.') }}</div>
                <div class="text-xs text-amber-800 font-semibold mt-0.5">{{ $tagihanBelumBayarCount }} Pelanggan Menunggu Bayar</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-700 flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- 3.1. Grand Total Finansial Khusus Voucher Hotspot -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100 shrink-0">
                    <i data-lucide="ticket" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                        <span>Finansial Khusus Voucher Hotspot</span>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                            {{ now()->translatedFormat('F Y') }}
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Grand total omzet kotor, pengeluaran bagi hasil reseller & laba bersih voucher</p>
                </div>
            </div>
            <a href="{{ route('vouchers.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-xs font-bold text-white transition self-start sm:self-auto shadow-xs">
                <i data-lucide="list" class="w-3.5 h-3.5 text-amber-400"></i>
                <span>Buka Rekap Penjualan Voucher</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
            <!-- 1. Grand Total Omzet Kotor Voucher -->
            <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200/70 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800 flex items-center gap-1.5">
                        <i data-lucide="coins" class="w-4 h-4 text-amber-600"></i>
                        <span>Grand Total Omzet Kotor</span>
                    </span>
                    <span class="text-[10px] font-semibold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">Bruto</span>
                </div>
                <div class="text-2xl font-black text-amber-900 tracking-tight">
                    Rp {{ number_format($voucherOmzetKotor, 0, ',', '.') }}
                </div>
                <div class="pt-2 border-t border-amber-200/50 flex items-center justify-between text-[11px] text-amber-800 font-medium">
                    <span>Terjual: <strong>{{ number_format($voucherTotalSold, 0, ',', '.') }} lembar</strong></span>
                    <span class="text-slate-500">All-Time: Rp {{ number_format($voucherAllTimeGross, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- 2. Pengeluaran / Bagi Hasil Mitra Warung -->
            <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200/70 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-800 flex items-center gap-1.5">
                        <i data-lucide="handshake" class="w-4 h-4 text-rose-600"></i>
                        <span>Pengeluaran (Fee Reseller)</span>
                    </span>
                    <span class="text-[10px] font-semibold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full">Bagi Hasil</span>
                </div>
                <div class="text-2xl font-black text-rose-900 tracking-tight">
                    Rp {{ number_format($voucherPengeluaranFee, 0, ',', '.') }}
                </div>
                <div class="pt-2 border-t border-rose-200/50 flex items-center justify-between text-[11px] text-rose-800 font-medium">
                    <span>Komisi Mitra Warung</span>
                    <span class="text-slate-500">All-Time: Rp {{ number_format($voucherAllTimeFee, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- 3. Laba Bersih Voucher Hokigo -->
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/70 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 flex items-center gap-1.5">
                        <i data-lucide="trending-up" class="w-4 h-4 text-emerald-600"></i>
                        <span>Laba Bersih Voucher</span>
                    </span>
                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">Kas Masuk</span>
                </div>
                <div class="text-2xl font-black text-emerald-900 tracking-tight">
                    Rp {{ number_format($voucherLabaBersih, 0, ',', '.') }}
                </div>
                <div class="pt-2 border-t border-emerald-200/50 flex items-center justify-between text-[11px] font-medium">
                    <span class="text-emerald-800">
                        Disetor: <strong>Rp {{ number_format($voucherDeposited, 0, ',', '.') }}</strong>
                        @if($voucherPending > 0)
                            &bull; <span class="text-amber-700 font-bold">Pending: Rp {{ number_format($voucherPending, 0, ',', '.') }}</span>
                        @endif
                    </span>
                    <span class="text-slate-500">All-Time: Rp {{ number_format($voucherAllTimeNet, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Interactive Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Chart 1: Trend Keuangan 6 Bulan Terakhir -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-brand-blue"></i>
                        <span>Tren Omzet & Pengeluaran 6 Bulan Terakhir</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Perbandingan pendapatan, pengeluaran & laba bersih</p>
                </div>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-600">
                    IDR (Rupiah)
                </span>
            </div>
            <div class="h-72 w-full">
                <canvas id="financeTrendChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Status Pembayaran Donut & Pelanggan per Desa -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-5 h-5 text-brand-blue"></i>
                        <span>Rasio Pembayaran Tagihan</span>
                    </h3>
                    <span class="text-xs text-slate-400 font-medium">{{ now()->translatedFormat('F Y') }}</span>
                </div>
                <div class="h-56 w-full flex items-center justify-center">
                    <canvas id="paymentStatusChart"></canvas>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-600 font-medium">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Lunas
                    </span>
                    <span class="font-bold text-slate-800">{{ $tagihanLunasCount }} Pelanggan</span>
                </div>
                <div class="flex items-center justify-between text-slate-600 font-medium">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span> Belum Bayar
                    </span>
                    <span class="font-bold text-slate-800">{{ $tagihanBelumBayarCount }} Pelanggan</span>
                </div>
            </div>
        </div>

    </div>

    <!-- 5. Chart 3: Distribusi Pelanggan per Desa (Requirement 1) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-brand-blue"></i>
                    <span>Sebaran Pelanggan Berdasarkan Desa</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Jumlah pelanggan aktif yang terdaftar di masing-masing desa</p>
            </div>
            <a href="{{ route('villages.index') }}" class="text-xs font-bold text-brand-blue hover:underline flex items-center gap-1">
                <span>Kelola Desa</span> &rarr;
            </a>
        </div>
        <div class="h-60 w-full">
            <canvas id="villageChart"></canvas>
        </div>
    </div>

    <!-- 6. Tagihan Jatuh Tempo Hari Ini & Overdue (Requirement 2: Chat otomatis WhatsApp) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                    <h3 class="font-bold text-slate-900 text-base">Tagihan Jatuh Tempo & Perlu Dikirim WhatsApp</h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Daftar pelanggan yang jatuh tempo hari ini atau sudah lewat dan belum melunasi tagihan</p>
            </div>
            <a href="{{ route('invoices.reminders') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Buka Pusat Pengingat WA</span>
            </a>
        </div>

        @if($dueTodayInvoices->isEmpty())
            <div class="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Semua Tagihan Aman!</h4>
                <p class="text-xs text-slate-500 mt-1">Tidak ada tagihan yang menunggak atau jatuh tempo hari ini yang belum dibayar.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-3 pl-2">Pelanggan</th>
                            <th class="pb-3">Desa</th>
                            <th class="pb-3">Paket</th>
                            <th class="pb-3">Jatuh Tempo</th>
                            <th class="pb-3">Nominal</th>
                            <th class="pb-3 text-right pr-2">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($dueTodayInvoices as $inv)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 pl-2">
                                    <div class="font-bold text-slate-900">{{ $inv->customer->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $inv->customer->customer_code }} &bull; {{ $inv->customer->phone }}</div>
                                </td>
                                <td class="py-3.5 text-xs text-slate-600 font-medium">
                                    {{ $inv->customer->village?->name ?? '-' }}
                                </td>
                                <td class="py-3.5 text-xs text-slate-600">
                                    {{ $inv->customer->package?->name ?? 'Internet' }}
                                </td>
                                <td class="py-3.5 text-xs">
                                    <span class="font-bold text-rose-600">
                                        {{ $inv->due_date->isoFormat('D MMM Y') }}
                                    </span>
                                </td>
                                <td class="py-3.5 font-bold text-slate-900 text-xs">
                                    Rp {{ number_format($inv->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 pr-2 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Direct Click to WhatsApp (Requirement 2) -->
                                        <a href="{{ $inv->whatsapp_url }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs transition shadow-xs">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                            <span>Chat WA</span>
                                        </a>

                                        <!-- Quick Mark Paid -->
                                        <form action="{{ route('invoices.mark-paid', $inv) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="payment_method" value="cash">
                                            <button type="submit" onclick="return confirm('Tandai tagihan {{ $inv->customer->name }} lunas tunai?')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs transition shadow-xs">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                <span>Lunas</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Chart Tren Finansial 6 Bulan
        const ctxFinance = document.getElementById('financeTrendChart').getContext('2d');
        new Chart(ctxFinance, {
            type: 'bar',
            data: {
                labels: @json($chartMonths),
                datasets: [
                    {
                        label: 'Omzet / Pemasukan',
                        data: @json($chartIncome),
                        backgroundColor: '#1055CC',
                        borderRadius: 8,
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json($chartExpense),
                        backgroundColor: '#f43f5e',
                        borderRadius: 8,
                    },
                    {
                        label: 'Laba Bersih',
                        data: @json($chartProfit),
                        type: 'line',
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 5,
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Plus Jakarta Sans', size: 12, weight: 600 },
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + ' Jt';
                                if (value >= 1000) return (value / 1000) + ' Rb';
                                return value;
                            }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // 2. Chart Status Pembayaran Donut
        const ctxStatus = document.getElementById('paymentStatusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Lunas', 'Belum Bayar'],
                datasets: [{
                    data: [{{ $tagihanLunasCount }}, {{ $tagihanBelumBayarCount }}],
                    backgroundColor: ['#10b981', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // 3. Chart Desa
        const ctxVillage = document.getElementById('villageChart').getContext('2d');
        new Chart(ctxVillage, {
            type: 'bar',
            data: {
                labels: @json($villageLabels),
                datasets: [{
                    label: 'Jumlah Pelanggan',
                    data: @json($villageCounts),
                    backgroundColor: '#1055CC',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
