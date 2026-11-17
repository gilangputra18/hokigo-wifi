@extends('layouts.app')

@section('title', 'Manajemen Voucher Hotspot & Stok Desa')
@section('header_title', 'Manajemen Voucher Hotspot')

@section('content')
<div class="space-y-6" x-data="vouchersPage()">

    <!-- Header & Action Buttons -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="p-2.5 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 shadow-xs">
                    <i data-lucide="ticket" class="w-6 h-6"></i>
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Voucher Hotspot & Stok Desa</h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                    Voucheran & Hybrid
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 max-w-2xl leading-relaxed">
                Kelola stok sentral per desa (misal: drop 500 pcs ke Desa Hampul), pembagian jatah ke masing-masing penjual (Si A, Si B, Si C), dan pemotongan stok otomatis saat laporan penjualan disetor.
            </p>
        </div>

        <!-- Action Buttons Group -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <!-- 1. Drop Stok Sentral -->
            <button type="button" @click="openStockModal()"
                    class="px-3.5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition">
                <i data-lucide="package-plus" class="w-4 h-4 text-amber-400"></i>
                <span>Drop Stok Sentral</span>
            </button>

            <!-- 2. Bagi ke Penjual -->
            <button type="button" @click="openAllocModal()"
                    class="px-3.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-2 shadow-sm shadow-indigo-600/30 transition">
                <i data-lucide="share-2" class="w-4 h-4"></i>
                <span>Bagi ke Penjual</span>
            </button>

            <!-- 3. Catat Penjualan -->
            <button type="button" @click="openSaleModal()"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/30 transition">
                <i data-lucide="coins" class="w-4 h-4"></i>
                <span>Catat Penjualan & Setoran</span>
            </button>
        </div>
    </div>

    <!-- Alert Success / Errors -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-xs">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
            <div class="text-sm font-medium leading-relaxed">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-xs">
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
            <div class="text-sm font-medium space-y-1">
                <div class="font-bold">Terjadi kesalahan:</div>
                <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Period & Village Filter Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <form action="{{ route('vouchers.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <!-- Periode Bulan -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">Periode:</label>
                <input type="month" name="period" value="{{ $currentPeriod }}" onchange="this.form.submit()"
                       class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
            </div>

            <!-- Filter Desa -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">Desa:</label>
                <select name="village_id" onchange="this.form.submit()" 
                        class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    <option value="">Semua Wilayah Voucher</option>
                    @foreach($voucherVillages as $v)
                        <option value="{{ $v->id }}" {{ $villageId == $v->id ? 'selected' : '' }}>
                            {{ $v->name }} ({{ $v->system_type === 'voucher' ? 'Voucheran' : 'Hybrid' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Setoran -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">Setoran:</label>
                <select name="status" onchange="this.form.submit()" 
                        class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    <option value="">Semua Status</option>
                    <option value="deposited" {{ $status === 'deposited' ? 'selected' : '' }}>Sudah Disetor</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Belum Disetor (Pending)</option>
                </select>
            </div>
        </form>

        <span class="text-xs text-slate-400 font-medium">
            Periode: <strong class="text-slate-800">{{ \Carbon\Carbon::createFromFormat('Y-m', $currentPeriod)->isoFormat('MMMM Y') }}</strong>
        </span>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Drop Sentral Desa -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <i data-lucide="package" class="w-4 h-4 text-slate-700"></i>
                <span>Drop Stok Sentral</span>
            </span>
            <p class="text-xl sm:text-2xl font-black text-slate-900">
                {{ number_format($periodStockTotals->total_drop ?? 0, 0, ',', '.') }} <span class="text-xs text-slate-400 font-normal">pcs</span>
            </p>
            <p class="text-[11px] text-slate-500">
                Terbagi ke penjual: <strong class="text-indigo-600">{{ number_format($periodStockTotals->total_allocated ?? 0, 0, ',', '.') }} pcs</strong>
            </p>
        </div>

        <!-- 2. Terjual & Habis -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <i data-lucide="ticket" class="w-4 h-4 text-emerald-600"></i>
                <span>Total Terjual</span>
            </span>
            <p class="text-xl sm:text-2xl font-black text-emerald-600">
                {{ number_format($stats->total_sold ?? 0, 0, ',', '.') }} <span class="text-xs text-slate-400 font-normal">pcs</span>
            </p>
            <p class="text-[11px] text-slate-500">Laku terjual bulan ini</p>
        </div>

        <!-- 3. Omset Kotor -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <i data-lucide="coins" class="w-4 h-4 text-amber-500"></i>
                <span>Omset Kotor</span>
            </span>
            <p class="text-xl sm:text-2xl font-black text-amber-600">
                Rp {{ number_format($stats->total_gross ?? 0, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-slate-500">
                Fee mitra: <span class="text-rose-500 font-semibold">- Rp {{ number_format($stats->total_fee ?? 0, 0, ',', '.') }}</span>
            </p>
        </div>

        <!-- 4. Laba Bersih Hokigo -->
        <div class="bg-gradient-to-br from-slate-900 to-indigo-950 p-5 rounded-2xl text-white shadow-md space-y-1 border border-indigo-900/60">
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-300 flex items-center gap-1.5">
                <i data-lucide="trending-up" class="w-4 h-4 text-emerald-400"></i>
                <span>Bersih Hokigo</span>
            </span>
            <p class="text-xl sm:text-2xl font-black text-emerald-400">
                Rp {{ number_format($stats->total_net ?? 0, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-indigo-200/70">
                Masuk kas &bull; <span class="text-emerald-300 font-semibold">All-Time: Rp {{ number_format($allTimeStats->total_net ?? 0, 0, ',', '.') }}</span>
            </p>
        </div>
    </div>

    <!-- SECTION 1: MONITORING STOK DESA & MITRA PENJUAL -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                    <i data-lucide="store" class="w-4 h-4"></i>
                </span>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Monitoring Stok Sentral & Penjual Desa</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Periode: {{ $currentPeriod }}</span>
        </div>

        @if(empty($villageMonitoring))
            <div class="bg-white rounded-3xl p-8 border border-slate-200/80 text-center text-slate-400">
                Belum ada desa yang diatur sebagai tipe Voucheran atau Hybrid. Silakan ubah tipe desa di menu Kelompok Desa.
            </div>
        @else
            <div class="space-y-5">
                @foreach($villageMonitoring as $item)
                    @php
                        $v = $item['village'];
                        $sellers = $item['sellers'];
                    @endphp
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden transition-all">
                        
                        <!-- Header Kartu Desa -->
                        <div class="p-5 sm:p-6 bg-slate-50/80 border-b border-slate-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-700 flex items-center justify-center font-black text-base shrink-0 border border-amber-200/60">
                                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                                </span>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900">{{ $v->name }}</h3>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $v->system_type === 'voucher' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $v->system_type === 'voucher' ? 'Voucheran' : 'Hybrid' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                        <span>Drop Sentral: <strong class="text-slate-800 font-bold">{{ number_format($item['total_central']) }} pcs</strong></span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span>Terbagi: <strong class="text-indigo-600 font-bold">{{ number_format($item['total_given']) }} pcs</strong></span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span>Terjual: <strong class="text-emerald-600 font-bold">{{ number_format($item['total_sold']) }} pcs</strong></span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span>Sisa Beredar: <strong class="text-amber-700 font-extrabold">{{ number_format($item['remaining_field']) }} pcs</strong></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons for this Village -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" @click="openStockModal({{ $v->id }})" 
                                        class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200 shadow-2xs transition flex items-center gap-1.5">
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-amber-500"></i>
                                    <span>Drop Sentral</span>
                                </button>
                                <button type="button" @click="openAllocModal({{ $v->id }})" 
                                        class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition flex items-center gap-1.5">
                                    <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                                    <span>Bagi ke Penjual</span>
                                </button>
                                <button type="button" @click="openSellerModal({{ $v->id }})" 
                                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition flex items-center gap-1">
                                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                    <span>+ Mitra</span>
                                </button>
                            </div>
                        </div>

                        <!-- Central Stocks Bar if present -->
                        @if($item['stocks']->isNotEmpty())
                            <div class="px-5 py-2.5 bg-amber-50/50 border-b border-amber-100/80 text-xs flex items-center justify-between gap-3 flex-wrap">
                                <div class="flex items-center gap-2 text-amber-900">
                                    <i data-lucide="box" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                    <span><strong>Drop Sentral Periode Ini:</strong></span>
                                    @foreach($item['stocks'] as $stk)
                                        <span class="inline-flex items-center gap-1 bg-white px-2 py-0.5 rounded-lg border border-amber-200 font-mono text-[11px] text-slate-800">
                                            {{ $stk->voucher_type }}: <strong>{{ $stk->initial_stock }} pcs</strong>
                                            <span class="text-slate-400">(@ Rp {{ number_format($stk->price_per_unit) }})</span>
                                            <span class="text-indigo-600 font-bold">Terbagi {{ $stk->allocated_stock }}</span>
                                            @if($stk->remaining_stock > 0)
                                                <span class="text-amber-700 font-extrabold">(Sisa {{ $stk->remaining_stock }} di sentral)</span>
                                            @endif
                                            <form action="{{ route('vouchers.stocks.destroy', $stk) }}" method="POST" onsubmit="return confirm('Hapus drop stok sentral ini?')" class="inline ml-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-slate-300 hover:text-rose-600" title="Hapus Drop Stok">&times;</button>
                                            </form>
                                        </span>
                                    @endforeach
                                </div>
                                <span class="text-[11px] text-amber-800 font-semibold">
                                    Sisa di Sentral Belum Dibagi: <strong>{{ number_format($item['remaining_central']) }} pcs</strong>
                                </span>
                            </div>
                        @endif

                        <!-- Sellers Breakdown Table inside this Village -->
                        @if(empty($sellers))
                            <div class="p-6 text-center text-slate-400 space-y-2">
                                <i data-lucide="users" class="w-8 h-8 mx-auto text-slate-300"></i>
                                <div class="text-xs font-semibold text-slate-500">Belum ada mitra penjual terdaftar di {{ $v->name }}.</div>
                                <button type="button" @click="openSellerModal({{ $v->id }})" class="text-xs font-bold text-indigo-600 hover:underline">
                                    + Tambah Penjual Pertama di {{ $v->name }}
                                </button>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-white border-b border-slate-100 text-slate-400 uppercase tracking-wider font-bold">
                                        <tr>
                                            <th class="py-3 px-5">Nama Mitra / Penjual</th>
                                            <th class="py-3 px-4 text-center">Diberikan (Pcs)</th>
                                            <th class="py-3 px-4 text-center">Terjual / Habis</th>
                                            <th class="py-3 px-4 text-center">Sisa di Tangan</th>
                                            <th class="py-3 px-4">Progress Habis</th>
                                            <th class="py-3 px-4 text-right">Laba Bersih Hokigo</th>
                                            <th class="py-3 px-5 text-right">Aksi Cepat</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-700">
                                        @foreach($sellers as $sData)
                                            @php
                                                $s = $sData['seller'];
                                            @endphp
                                            <tr class="hover:bg-slate-50/70 transition">
                                                <!-- Nama Penjual -->
                                                <td class="py-3.5 px-5">
                                                    <div class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                                                        <span>{{ $s->name }}</span>
                                                        @if($s->phone)
                                                            <a href="https://api.whatsapp.com/send?phone={{ $s->clean_phone }}" target="_blank"
                                                               class="text-emerald-600 hover:text-emerald-700" title="Chat WhatsApp">
                                                                <i data-lucide="message-circle" class="w-3.5 h-3.5 inline"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                                        {{ $s->address ? $s->address . ' &bull; ' : '' }} Fee: Rp {{ number_format($s->default_fee_per_unit, 0, ',', '.') }}/pcs
                                                    </div>
                                                </td>

                                                <!-- Diberikan (Pcs) -->
                                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                    <span class="font-bold text-slate-800 text-xs bg-slate-100 px-2.5 py-1 rounded-xl">
                                                        {{ number_format($sData['given']) }} pcs
                                                    </span>
                                                </td>

                                                <!-- Terjual (Pcs) -->
                                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                    <span class="font-extrabold text-emerald-700 text-xs bg-emerald-50 px-2.5 py-1 rounded-xl border border-emerald-100">
                                                        {{ number_format($sData['sold']) }} pcs
                                                    </span>
                                                </td>

                                                <!-- Sisa di Tangan (Pcs) -->
                                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                    <span class="font-black text-xs px-2.5 py-1 rounded-xl {{ $sData['remaining'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-400' }}">
                                                        {{ number_format($sData['remaining']) }} pcs
                                                    </span>
                                                </td>

                                                <!-- Progress Bar -->
                                                <td class="py-3.5 px-4 min-w-[130px]">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                                            <div class="bg-emerald-500 h-2 rounded-full transition-all duration-300" style="width: {{ $sData['percentage'] }}%"></div>
                                                        </div>
                                                        <span class="text-[10px] font-bold text-slate-600 font-mono shrink-0">{{ $sData['percentage'] }}%</span>
                                                    </div>
                                                </td>

                                                <!-- Laba Bersih Disetor -->
                                                <td class="py-3.5 px-4 text-right whitespace-nowrap font-extrabold text-emerald-600">
                                                    Rp {{ number_format($sData['net']) }}
                                                </td>

                                                <!-- Action Buttons per Seller -->
                                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                                    <div class="flex items-center justify-end gap-1.5">
                                                        <!-- Tambah Stok / Jatah -->
                                                        <button type="button" 
                                                                @click="openAllocModal({{ $v->id }}, {{ $s->id }}, '{{ addslashes($s->name) }}')"
                                                                title="Bagi / Tambah Stok ke {{ $s->name }}"
                                                                class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition">
                                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                                        </button>

                                                        <!-- Catat Penjualan & Potong Stok -->
                                                        <button type="button" 
                                                                @click="openSaleModal({{ $v->id }}, {{ $s->id }}, '{{ addslashes($s->name) }}', {{ $s->default_fee_per_unit }})"
                                                                title="Catat Penjualan / Setoran dari {{ $s->name }}"
                                                                class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1 shadow-xs transition">
                                                            <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                                            <span>Setor</span>
                                                        </button>

                                                        <!-- Hapus Penjual -->
                                                        <form action="{{ route('vouchers.sellers.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus mitra penjual {{ $s->name }}?')" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" title="Hapus Penjual" class="p-1.5 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition">
                                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
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
                @endforeach
            </div>
        @endif
    </div>

    <!-- SECTION 2: TABEL DETAIL RIWAYAT PENJUALAN VOUCHER -->
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <div class="p-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-amber-500"></i>
                <span>Riwayat Laporan & Setoran Penjualan Voucher</span>
            </h3>
            <span class="text-xs text-slate-400">{{ $sales->total() }} catatan penjualan</span>
        </div>

        @if($sales->isEmpty())
            <div class="py-14 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="ticket" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-700">Belum Ada Rekap Penjualan Voucher pada Periode Ini</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Klik tombol "Catat Penjualan & Setoran" di atas untuk memasukkan rekap penjualan voucher dari mitra desa.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Tgl / Periode</th>
                            <th class="py-3 px-4">Desa / Lokasi</th>
                            <th class="py-3 px-4">Penjual (Mitra)</th>
                            <th class="py-3 px-4">Paket Voucher</th>
                            <th class="py-3 px-4 text-center">Qty Terjual</th>
                            <th class="py-3 px-4 text-right">Omset Kotor</th>
                            <th class="py-3 px-4 text-right">Fee Mitra</th>
                            <th class="py-3 px-4 text-right">Bersih Hokigo</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($sales as $s)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ $s->sale_date ? $s->sale_date->isoFormat('D MMM Y') : '-' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $s->period }}</div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800">{{ $s->village?->name ?? 'Luar Wilayah' }}</span>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $s->seller_name }}</div>
                                    @if($s->seller_phone)
                                        <a href="https://api.whatsapp.com/send?phone={{ $s->clean_phone }}" target="_blank"
                                           class="text-[11px] text-emerald-600 hover:text-emerald-700 flex items-center gap-1 font-medium mt-0.5">
                                            <i data-lucide="message-circle" class="w-3 h-3"></i>
                                            <span>{{ $s->seller_phone }}</span>
                                        </a>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-800 font-bold border border-blue-100">
                                        {{ $s->voucher_type }}
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-1">@ Rp {{ number_format($s->price_per_unit, 0, ',', '.') }}</div>
                                </td>

                                <td class="py-3.5 px-4 text-center font-bold text-slate-900 whitespace-nowrap">
                                    {{ number_format($s->quantity_sold, 0, ',', '.') }} pcs
                                </td>

                                <td class="py-3.5 px-4 text-right font-bold text-slate-800 whitespace-nowrap">
                                    Rp {{ number_format($s->total_gross_amount, 0, ',', '.') }}
                                </td>

                                <td class="py-3.5 px-4 text-right font-semibold text-rose-600 whitespace-nowrap">
                                    - Rp {{ number_format($s->total_seller_fee, 0, ',', '.') }}
                                </td>

                                <td class="py-3.5 px-4 text-right font-extrabold text-emerald-600 whitespace-nowrap">
                                    Rp {{ number_format($s->net_amount, 0, ',', '.') }}
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($s->status === 'deposited')
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold flex items-center justify-center gap-1 mx-auto w-max">
                                            <i data-lucide="check" class="w-3 h-3"></i> Sudah Disetor
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-extrabold flex items-center justify-center gap-1 mx-auto w-max">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Belum Setor
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($s->status === 'pending')
                                            <form action="{{ route('vouchers.mark-deposited', $s) }}" method="POST">
                                                @csrf
                                                <button type="submit" title="Tandai Sudah Disetor (Masuk ke Kas)"
                                                        class="p-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white transition shadow-xs">
                                                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('vouchers.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus catatan voucher ini? Stok penjual akan dikembalikan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Data"
                                                    class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 transition">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $sales->links() }}
            </div>
        @endif

    </div>

    <!-- ==================== MODALS ==================== -->

    <!-- MODAL 1: DROP STOK SENTRAL DESA -->
    <div x-show="modalStockOpen" x-cloak
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden" @click.away="modalStockOpen = false">
            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                    <i data-lucide="package-plus" class="w-5 h-5 text-amber-500"></i>
                    <span>Drop Stok Sentral ke Desa</span>
                </h3>
                <button type="button" @click="modalStockOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('vouchers.stocks.store') }}" method="POST" class="p-5 sm:p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Desa Tujuan Sentral *</label>
                        <select name="village_id" x-model="stockForm.village_id" required 
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                            <option value="">-- Pilih Wilayah Desa --</option>
                            @foreach($voucherVillages as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Periode Bulan *</label>
                        <input type="month" name="period" value="{{ $currentPeriod }}" required 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe / Masa Aktif Voucher *</label>
                        <input type="text" name="voucher_type" value="Voucher Rp 5.000" required 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual per Pcs (Rp) *</label>
                        <input type="number" name="price_per_unit" value="5000" required min="0" 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Voucher yang Di-Drop (Pcs) *</label>
                    <input type="number" name="initial_stock" placeholder="Misal: 500" required min="1" 
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm font-black text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    <p class="text-[11px] text-slate-400 mt-1">Stok ini akan masuk ke sentral utama desa dan siap dibagi ke penjual.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Pasokan awal bulan batch 1" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="modalStockOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm">Simpan Drop Sentral</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: BAGI STOK KE PENJUAL -->
    <div x-show="modalAllocOpen" x-cloak
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden" @click.away="modalAllocOpen = false">
            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                    <i data-lucide="share-2" class="w-5 h-5 text-indigo-600"></i>
                    <span>Bagi Stok Sentral ke Penjual</span>
                </h3>
                <button type="button" @click="modalAllocOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('vouchers.allocations.store') }}" method="POST" class="p-5 sm:p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Desa *</label>
                        <select name="village_id" x-model="allocForm.village_id" required 
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                            <option value="">-- Pilih Desa --</option>
                            @foreach($voucherVillages as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Periode Bulan *</label>
                        <input type="month" name="period" value="{{ $currentPeriod }}" required 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Pilih Mitra Penjual -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-700">Mitra / Penjual Penerima *</label>
                        <span class="text-[11px] text-indigo-600 font-semibold cursor-pointer hover:underline" @click="allocForm.new_seller = !allocForm.new_seller">
                            <span x-text="allocForm.new_seller ? 'Pilih Dari Daftar Penjual' : '+ Ketik Nama Penjual Baru'"></span>
                        </span>
                    </div>

                    <div x-show="!allocForm.new_seller">
                        <select name="voucher_seller_id" x-model="allocForm.seller_id" :required="!allocForm.new_seller"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                            <option value="">-- Pilih Penjual di Desa Ini --</option>
                            @foreach($voucherVillages as $v)
                                <optgroup label="Desa {{ $v->name }}">
                                    @foreach($v->voucherSellers as $sel)
                                        <option value="{{ $sel->id }}">{{ $sel->name }} ({{ $v->name }})</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="allocForm.new_seller" x-cloak>
                        <input type="text" name="new_seller_name" x-model="allocForm.new_seller_name" placeholder="Ketik nama penjual baru (misal: Si A / Warung Siti)"
                               class="w-full px-3 py-2 rounded-xl border border-indigo-300 text-xs focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Voucher *</label>
                        <input type="text" name="voucher_type" value="Voucher Rp 5.000" required 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual (Rp) *</label>
                        <input type="number" name="price_per_unit" value="5000" required min="0" 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Qty yang Diberikan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Voucher Diberikan (Pcs) *</label>
                    <input type="number" name="quantity_given" placeholder="Misal: 150" required min="1" 
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm font-black text-indigo-700 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    <p class="text-[11px] text-slate-400 mt-1">Stok ini akan dicatat dipegang oleh penjual tersebut dan berkurang saat ia menyetor penjualan.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Distribusi</label>
                    <input type="date" name="allocation_date" value="{{ date('Y-m-d') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="modalAllocOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm">Bagi ke Penjual</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: TAMBAH MITRA PENJUAL BARU -->
    <div x-show="modalSellerOpen" x-cloak
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden" @click.away="modalSellerOpen = false">
            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5 text-indigo-600"></i>
                    <span>Tambah Mitra Penjual Desa</span>
                </h3>
                <button type="button" @click="modalSellerOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('vouchers.sellers.store') }}" method="POST" class="p-5 sm:p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Desa Wilayah *</label>
                    <select name="village_id" x-model="sellerForm.village_id" required 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                        <option value="">-- Pilih Wilayah Desa --</option>
                        @foreach($voucherVillages as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Penjual / Mitra / Warung *</label>
                    <input type="text" name="name" placeholder="Contoh: Si A / Warung Bu Siti / Konter Agus" required 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp (Opsional)</label>
                    <input type="text" name="phone" placeholder="0812xxxxxxxx" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat / Lokasi Warung (Opsional)</label>
                    <input type="text" name="address" placeholder="Contoh: Depan Gapura Dusun 2" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Default Fee Penjual per Lembar (Rp)</label>
                    <input type="number" name="default_fee_per_unit" value="1000" min="0" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="modalSellerOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm">Simpan Mitra</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: CATAT LAPORAN PENJUALAN VOUCHER (POTONG STOK) -->
    <div x-show="modalSaleOpen" x-cloak 
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
         style="padding-top: max(24px, calc(env(safe-area-inset-top, 0px) + 16px)); padding-bottom: max(24px, calc(env(safe-area-inset-bottom, 0px) + 16px));">
        
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 relative my-auto flex flex-col max-h-[88vh] sm:max-h-[85vh] overflow-hidden"
             @click.away="modalSaleOpen = false" x-data="saleCalculator()">
            
            <!-- Sticky Modal Header -->
            <div class="flex items-center justify-between p-5 sm:p-6 pb-4 border-b border-slate-100 shrink-0 bg-white">
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                        <i data-lucide="ticket" class="w-5 h-5 text-emerald-600"></i>
                        <span>Catat Rekap Penjualan Voucher</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Stok voucher di tangan penjual akan otomatis terpotong saat disimpan</p>
                </div>
                <button type="button" @click="modalSaleOpen = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Scrollable Modal Body Form -->
            <form id="voucherSaleForm" action="{{ route('vouchers.store') }}" method="POST" class="overflow-y-auto p-5 sm:p-6 space-y-4 flex-1">
                @csrf

                <!-- Pilih Desa & Periode -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Desa / Titik Lokasi *</label>
                        <select name="village_id" x-model="saleForm.village_id" required 
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                            <option value="">-- Pilih Wilayah Desa --</option>
                            @foreach($villages as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->system_type === 'voucher' ? 'Voucher' : ($v->system_type === 'hybrid' ? 'Hybrid' : 'PPPoE') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Periode Bulan *</label>
                        <input type="month" name="period" value="{{ $currentPeriod }}" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>
                </div>

                <!-- Penjual & No WA -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Penjual / Mitra Warung *</label>
                        <input type="text" name="seller_name" x-model="saleForm.seller_name" placeholder="Contoh: Si A / Wiranto" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                        <input type="hidden" name="voucher_seller_id" :value="saleForm.seller_id">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp Penjual (Opsional)</label>
                        <input type="text" name="seller_phone" placeholder="0812xxxxxxxx"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>
                </div>

                <!-- Tipe Voucher & Harga Jual Satuan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe / Masa Aktif Voucher *</label>
                        <input type="text" name="voucher_type" x-model="saleForm.voucher_type" placeholder="Contoh: Voucher Rp 5.000 / 3 Jam / 24 Jam" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual per Pcs (Rp) *</label>
                        <input type="number" name="price_per_unit" x-model.number="price" required min="0" placeholder="5000"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>
                </div>

                <!-- Qty Terjual & Fee Penjual -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Terjual (Pcs) *</label>
                        <input type="number" name="quantity_sold" x-model.number="qty" required min="1" placeholder="100"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-black text-slate-900 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fee Penjual per Pcs (Rp) *</label>
                        <input type="number" name="fee_per_unit" x-model.number="feePerUnit" required min="0" placeholder="1000"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>
                </div>

                <!-- Real-time Automatic Calculation Box -->
                <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Total Omset Kotor:</span>
                        <strong class="text-slate-800 font-mono text-sm" x-text="formatRupiah(grossAmount)">Rp 0</strong>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Total Fee Mitra Warung:</span>
                        <strong class="text-rose-600 font-mono text-sm" x-text="'- ' + formatRupiah(totalFee)">- Rp 0</strong>
                    </div>
                    <div class="pt-2 border-t border-emerald-200/60 flex items-center justify-between text-xs">
                        <span class="font-extrabold text-emerald-950">Pendapatan Bersih Hokigo:</span>
                        <strong class="text-emerald-700 font-mono text-base font-black" x-text="formatRupiah(netAmount)">Rp 0</strong>
                    </div>
                </div>

                <!-- Status Setoran & Tanggal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Setoran Uang *</label>
                        <select name="status" required
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                            <option value="deposited">✅ Sudah Disetor (Masuk Kas)</option>
                            <option value="pending">⏳ Belum Disetor (Tertunda)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Rekap</label>
                        <input type="date" name="sale_date" value="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Setoran uang tunai / no seri 001-100"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                </div>

            </form>

            <!-- Sticky Modal Footer -->
            <div class="p-4 sm:px-6 border-t border-slate-100 bg-slate-50/90 shrink-0 flex items-center justify-end gap-2">
                <button type="button" @click="modalSaleOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition">
                    Batal
                </button>
                <button type="button" @click="document.getElementById('voucherSaleForm').requestSubmit()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Penjualan & Potong Stok</span>
                </button>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
function vouchersPage() {
    return {
        modalSaleOpen: false,
        modalStockOpen: false,
        modalAllocOpen: false,
        modalSellerOpen: false,

        stockForm: {
            village_id: '',
        },

        allocForm: {
            village_id: '',
            seller_id: '',
            seller_name: '',
            new_seller: false,
            new_seller_name: '',
        },

        sellerForm: {
            village_id: '',
        },

        saleForm: {
            village_id: '',
            seller_id: '',
            seller_name: '',
            voucher_type: 'Voucher Rp 5.000',
        },

        openStockModal(villageId = '') {
            this.stockForm.village_id = villageId || '';
            this.modalStockOpen = true;
        },

        openAllocModal(villageId = '', sellerId = '', sellerName = '') {
            this.allocForm.village_id = villageId || '';
            this.allocForm.seller_id = sellerId || '';
            this.allocForm.seller_name = sellerName || '';
            this.allocForm.new_seller = false;
            this.modalAllocOpen = true;
        },

        openSellerModal(villageId = '') {
            this.sellerForm.village_id = villageId || '';
            this.modalSellerOpen = true;
        },

        openSaleModal(villageId = '', sellerId = '', sellerName = '', feePerUnit = 1000) {
            this.saleForm.village_id = villageId || '';
            this.saleForm.seller_id = sellerId || '';
            this.saleForm.seller_name = sellerName || '';
            this.modalSaleOpen = true;
        }
    };
}

function saleCalculator() {
    return {
        price: 5000,
        qty: 100,
        feePerUnit: 1000,

        get grossAmount() {
            return (this.price || 0) * (this.qty || 0);
        },

        get totalFee() {
            return (this.feePerUnit || 0) * (this.qty || 0);
        },

        get netAmount() {
            return Math.max(0, this.grossAmount - this.totalFee);
        },

        formatRupiah(num) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(num || 0);
        }
    };
}
</script>
@endpush
@endsection
