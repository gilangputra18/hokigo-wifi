@extends('layouts.app')

@section('title', 'Manajemen Penjualan Voucher Hotspot')
@section('header_title', 'Manajemen Voucher Hotspot')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false }">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Rekap Penjualan Voucher</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola omset voucher hotspot desa, komisi agen warung/penjual, dan laba bersih</p>
        </div>

        <button type="button" @click="modalOpen = true" 
                class="px-4 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-blue-600/30 transition">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Catat Penjualan Voucher Baru</span>
        </button>
    </div>

    <!-- Period & Village Filter -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <form action="{{ route('vouchers.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <!-- Periode Bulan -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">Periode:</label>
                <input type="month" name="period" value="{{ $currentPeriod }}" onchange="this.form.submit()"
                       class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
            </div>

            <!-- Filter Desa -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">Desa:</label>
                <select name="village_id" onchange="this.form.submit()" 
                        class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <option value="">Semua Desa</option>
                    @foreach($villages as $v)
                        <option value="{{ $v->id }}" {{ $villageId == $v->id ? 'selected' : '' }}>
                            {{ $v->name }} ({{ $v->system_type === 'voucher' ? 'Voucheran' : ($v->system_type === 'hybrid' ? 'Hybrid' : 'PPPoE') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">Setoran:</label>
                <select name="status" onchange="this.form.submit()" 
                        class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
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
        <!-- Total Terjual -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <i data-lucide="ticket" class="w-4 h-4 text-brand-blue"></i>
                <span>Total Terjual</span>
            </span>
            <p class="text-xl sm:text-2xl font-black text-slate-900">
                {{ number_format($stats->total_sold ?? 0, 0, ',', '.') }} <span class="text-xs text-slate-400 font-normal">lembar</span>
            </p>
            <p class="text-[11px] text-slate-500">Volume penjualan voucher</p>
        </div>

        <!-- Omset Kotor -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <i data-lucide="coins" class="w-4 h-4 text-amber-500"></i>
                <span>Omset Kotor</span>
            </span>
            <p class="text-xl sm:text-2xl font-black text-amber-600">
                Rp {{ number_format($stats->total_gross ?? 0, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-slate-500">Sebelum dipotong komisi</p>
        </div>

        <!-- Fee Penjual -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <i data-lucide="handshake" class="w-4 h-4 text-rose-500"></i>
                <span>Total Fee Penjual</span>
            </span>
            <p class="text-xl sm:text-2xl font-black text-rose-600">
                Rp {{ number_format($stats->total_fee ?? 0, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-slate-500">Bagi hasil mitra warung</p>
        </div>

        <!-- Laba Bersih Hokigo -->
        <div class="bg-gradient-to-br from-blue-900 to-indigo-950 p-5 rounded-2xl text-white shadow-md space-y-1 border border-blue-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-200 flex items-center gap-1.5">
                <i data-lucide="trending-up" class="w-4 h-4 text-emerald-400"></i>
                <span>Bersih Hokigo</span>
            </span>
            <p class="text-xl sm:text-2xl font-black text-emerald-400">
                Rp {{ number_format($stats->total_net ?? 0, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-blue-200/70">Pendapatan bersih masuk kas</p>
        </div>
    </div>

    <!-- Table of Voucher Sales Records -->
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-brand-blue"></i>
                <span>Daftar Rekap Penjualan Voucher</span>
            </h3>
            <span class="text-xs text-slate-400">{{ $sales->total() }} catatan</span>
        </div>

        @if($sales->isEmpty())
            <div class="py-14 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="ticket" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-700">Belum Ada Rekap Penjualan Voucher</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Klik tombol "Catat Penjualan Voucher Baru" di atas untuk memasukkan rekap penjualan dari warung atau titik voucher desa.
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
                            <th class="py-3 px-4 text-center">Qty</th>
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
                                    {{ number_format($s->quantity_sold, 0, ',', '.') }}
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

                                        <form action="{{ route('vouchers.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus catatan voucher ini?')">
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

    <!-- Modal Form: Catat Penjualan Voucher -->
    <div x-show="modalOpen" x-cloak 
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
        
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 relative my-auto flex flex-col max-h-[90vh] sm:max-h-[85vh] overflow-hidden"
             @click.away="modalOpen = false" x-data="voucherCalculator()">
            
            <!-- Sticky Modal Header -->
            <div class="flex items-center justify-between p-5 sm:p-6 pb-4 border-b border-slate-100 shrink-0 bg-white">
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                        <i data-lucide="ticket" class="w-5 h-5 text-brand-blue"></i>
                        <span>Catat Rekap Penjualan Voucher</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Hitung otomatis omset kotor, fee mitra, dan laba bersih</p>
                </div>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Scrollable Modal Body Form -->
            <form id="voucherForm" action="{{ route('vouchers.store') }}" method="POST" class="overflow-y-auto p-5 sm:p-6 space-y-4 flex-1">
                @csrf

                <!-- Pilih Desa & Nama Penjual -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Desa / Titik Lokasi *</label>
                        <select name="village_id" required 
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="">-- Pilih Wilayah Desa --</option>
                            @foreach($villages as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->system_type === 'voucher' ? 'Voucher' : ($v->system_type === 'hybrid' ? 'Hybrid' : 'PPPoE') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Periode Bulan *</label>
                        <input type="month" name="period" value="{{ $currentPeriod }}" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <!-- Penjual & No WA -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Penjual / Mitra Warung *</label>
                        <input type="text" name="seller_name" placeholder="Contoh: Warung Bu Siti / Konter Agus" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp Penjual (Opsional)</label>
                        <input type="text" name="seller_phone" placeholder="0812xxxxxxxx"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <!-- Tipe Voucher & Harga Jual Satuan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe / Masa Aktif Voucher *</label>
                        <input type="text" name="voucher_type" placeholder="Contoh: 2 Jam / 24 Jam / 7 Hari" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual per Voucher (Rp) *</label>
                        <input type="number" name="price_per_unit" x-model.number="price" required min="0" placeholder="5000"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <!-- Qty Terjual & Fee Penjual -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Terjual (Lembar) *</label>
                        <input type="number" name="quantity_sold" x-model.number="qty" required min="1" placeholder="100"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fee Penjual per Lembar (Rp) *</label>
                        <input type="number" name="fee_per_unit" x-model.number="feePerUnit" required min="0" placeholder="1000"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <!-- Real-time Automatic Calculation Box -->
                <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Total Omset Kotor:</span>
                        <strong class="text-slate-800 font-mono text-sm" x-text="formatRupiah(grossAmount)">Rp 0</strong>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Total Fee Mitra Warung:</span>
                        <strong class="text-rose-600 font-mono text-sm" x-text="'- ' + formatRupiah(totalFee)">- Rp 0</strong>
                    </div>
                    <div class="pt-2 border-t border-blue-200/60 flex items-center justify-between text-xs">
                        <span class="font-extrabold text-blue-900">Pendapatan Bersih Hokigo:</span>
                        <strong class="text-emerald-700 font-mono text-base font-extrabold" x-text="formatRupiah(netAmount)">Rp 0</strong>
                    </div>
                </div>

                <!-- Status Setoran & Tanggal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Setoran Uang *</label>
                        <select name="status" required
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="deposited">✅ Sudah Disetor (Masuk Kas)</option>
                            <option value="pending">⏳ Belum Disetor (Tertunda)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Rekap</label>
                        <input type="date" name="sale_date" value="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: No. seri voucher 001-100"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

            </form>

            <!-- Sticky Modal Footer -->
            <div class="p-4 sm:px-6 border-t border-slate-100 bg-slate-50/90 shrink-0 flex items-center justify-end gap-2">
                <button type="button" @click="modalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition">
                    Batal
                </button>
                <button type="button" @click="document.getElementById('voucherForm').requestSubmit()" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs shadow-md shadow-blue-600/30 transition flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Rekap Voucher</span>
                </button>
            </div>

        </div>

    </div>

</div>

<script>
    function voucherCalculator() {
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
@endsection
