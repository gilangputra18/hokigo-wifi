@extends('layouts.app')

@section('title', 'Data Keuangan Lengkap')
@section('header_title', 'Laporan Keuangan & Buku Kas')

@section('content')
<div class="space-y-6" x-data="{ 
    expenseModalOpen: false, 
    incomeModalOpen: false,
    resetModalOpen: false 
}">

    <!-- 1. Top Financial Metrics Cards (Requirements 4 & 5) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Omzet / Pemasukan -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pemasukan (Omzet)</span>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-brand-blue flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">
                Rp {{ number_format($totalIncome, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-2">Tagihan WiFi lunas + jasa pasang</p>
        </div>

        <!-- Total Pengeluaran -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pengeluaran (Biaya)</span>
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="trending-down" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-2">Bandwidth ISP, listrik & teknisi</p>
        </div>

        <!-- Laba Bersih (Net Profit) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Laba Bersih (Net Profit)</span>
                <div class="w-10 h-10 rounded-2xl {{ $netProfit >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }} tracking-tight">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                <span class="px-2 py-0.5 rounded font-bold {{ $netProfit >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $netProfit >= 0 ? 'Untung' : 'Rugi' }}
                </span>
                <span class="text-slate-400">(Omzet dikurangi pengeluaran)</span>
            </div>
        </div>

        <!-- Margin Keuntungan -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Profit Margin</span>
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="percent" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">
                {{ $profitMargin }}%
            </div>
            <p class="text-xs text-slate-400 mt-2">Persentase laba bersih dari omzet</p>
        </div>

    </div>

    <!-- 2. Action Bar & Filter Periode -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="book-open" class="w-6 h-6 text-brand-blue"></i>
                    <span>Buku Kas & Transaksi</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Periode Laporan: <strong>{{ $startDate->isoFormat('D MMM Y') }} s/d {{ $endDate->isoFormat('D MMM Y') }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if(auth()->user()->isOwner())
                    <!-- Reset Data Keuangan Button (Owner Only) -->
                    <button type="button" @click="resetModalOpen = true" 
                            class="px-3.5 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-rose-300 hover:text-rose-200 border border-rose-500/30 font-bold text-xs flex items-center gap-1.5 shadow-sm transition">
                        <i data-lucide="rotate-ccw" class="w-4 h-4 text-rose-400"></i>
                        <span>Reset Data ke Rp 0</span>
                    </button>
                @endif

                <!-- Record Expense Modal Button -->
                <button type="button" @click="expenseModalOpen = true" 
                        class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition">
                    <i data-lucide="minus-circle" class="w-4 h-4"></i>
                    <span>Catat Pengeluaran</span>
                </button>

                <!-- Record Income Modal Button -->
                <button type="button" @click="incomeModalOpen = true" 
                        class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Catat Pemasukan</span>
                </button>

                <!-- Export CSV / Excel -->
                <a href="{{ route('finance.export-csv', ['month' => $month, 'year' => $year]) }}" 
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-2 transition">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Ekspor CSV</span>
                </a>
            </div>
        </div>

        <!-- Filter Form: Month & Year Picker -->
        <form method="GET" action="{{ route('finance.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-4 border-t border-slate-100">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Bulan</label>
                <select name="month" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Tahun</label>
                <select name="year" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori Transaksi</label>
                <input type="text" name="category" value="{{ request('category') }}" placeholder="Contoh: Bandwidth, Listrik..."
                       class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Tampilkan Laporan</span>
                </button>
            </div>
        </form>

    </div>

    <!-- 3. Category Breakdown Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        
        <!-- Pemasukan Berdasarkan Kategori -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2 mb-4">
                <i data-lucide="arrow-down-left" class="w-4 h-4 text-emerald-600"></i>
                <span>Rincian Pemasukan Bulan Ini</span>
            </h3>
            @if($incomeByCategory->isEmpty())
                <p class="text-xs text-slate-400">Belum ada pemasukan yang tercatat di periode ini.</p>
            @else
                <div class="space-y-2.5">
                    @foreach($incomeByCategory as $cat)
                        <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-slate-50">
                            <span class="font-semibold text-slate-700">{{ $cat->category }}</span>
                            <span class="font-bold text-emerald-600">Rp {{ number_format($cat->total, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Pengeluaran Berdasarkan Kategori -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2 mb-4">
                <i data-lucide="arrow-up-right" class="w-4 h-4 text-rose-600"></i>
                <span>Rincian Pengeluaran Bulan Ini</span>
            </h3>
            @if($expenseByCategory->isEmpty())
                <p class="text-xs text-slate-400">Belum ada pengeluaran yang dicatat di periode ini.</p>
            @else
                <div class="space-y-2.5">
                    @foreach($expenseByCategory as $cat)
                        <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-slate-50">
                            <span class="font-semibold text-slate-700">{{ $cat->category }}</span>
                            <span class="font-bold text-rose-600">Rp {{ number_format($cat->total, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <!-- 4. Tabs: Semua, Pemasukan, Pengeluaran -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'all']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'all' ? 'bg-brand-blue text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            Semua Transaksi
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'income']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'income' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            Hanya Pemasukan (Omzet)
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'expense']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'expense' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            Hanya Pengeluaran (Biaya)
        </a>
    </div>

    <!-- 5. Transactions Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-4 pl-6">Tanggal & Kode</th>
                        <th class="py-4">Tipe</th>
                        <th class="py-4">Kategori & Keterangan</th>
                        <th class="py-4">Metode</th>
                        <th class="py-4">Nominal</th>
                        <th class="py-4 pr-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/60 transition">
                            
                            <!-- Date & Code -->
                            <td class="py-4 pl-6">
                                <div class="font-bold text-slate-900">{{ $trx->transaction_date->isoFormat('D MMM Y') }}</div>
                                <div class="font-mono text-[10px] text-slate-400">{{ $trx->transaction_code }}</div>
                            </td>

                            <!-- Type Badge -->
                            <td class="py-4">
                                @if($trx->type === 'income')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold inline-flex items-center gap-1">
                                        <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i> Masuk
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold inline-flex items-center gap-1">
                                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i> Keluar
                                    </span>
                                @endif
                            </td>

                            <!-- Category & Description -->
                            <td class="py-4 max-w-sm">
                                <div class="font-bold text-slate-800">{{ $trx->category }}</div>
                                <div class="text-slate-500 text-[11px] mt-0.5 line-clamp-2">
                                    {{ $trx->description }}
                                </div>
                                @if($trx->user)
                                    <div class="text-[10px] text-slate-400 mt-1">Dicatat oleh: {{ $trx->user->name }}</div>
                                @endif
                            </td>

                            <!-- Payment Method -->
                            <td class="py-4 text-slate-600 font-semibold uppercase text-[11px]">
                                {{ $trx->payment_method ?: 'TUNAI' }}
                            </td>

                            <!-- Amount -->
                            <td class="py-4 font-black text-sm {{ $trx->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $trx->type === 'income' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>

                            <!-- Action -->
                            <td class="py-4 pr-6 text-right">
                                <form action="{{ route('finance.destroy', $trx) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan transaksi {{ $trx->transaction_code }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition" title="Hapus Transaksi">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                Belum ada transaksi yang tercatat untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Modal 1: Catat Pengeluaran Baru -->
    <div x-show="expenseModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="expenseModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">Catat Biaya Pengeluaran</h3>
                    <p class="text-xs text-slate-500">Mencatat beban operasional, perbaikan & bandwidth</p>
                </div>
                <button @click="expenseModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('finance.expense.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Kategori Pengeluaran *</label>
                    <select name="category" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <option value="Bandwidth / ISP Utama">Bandwidth / ISP Utama</option>
                        <option value="Listrik & Biaya ODP">Listrik Sentral & ODP</option>
                        <option value="Gaji Teknisi / Karyawan">Gaji Teknisi / Karyawan</option>
                        <option value="Beli Alat & Kabel">Beli Alat, Router & Kabel Dropcore</option>
                        <option value="Perbaikan & Maintenance">Perbaikan & Maintenance Jalur</option>
                        <option value="Sewa Tiang & Lokasi">Sewa Tiang & Tempat</option>
                        <option value="Konsumsi & Operasional">Bensin, Konsumsi & Operasional</option>
                        <option value="Lainnya">Pengeluaran Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Nominal Pengeluaran (Rp) *</label>
                    <input type="number" name="amount" required placeholder="Contoh: 1500000" min="1"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-extrabold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Tanggal Transaksi *</label>
                        <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Metode Bayar *</label>
                        <select name="payment_method" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="cash">Tunai / Cash</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Keterangan / Rincian *</label>
                    <textarea name="description" rows="2" required placeholder="Contoh: Beli kabel dropcore 1 roll 1000m + fast connector untuk desa karanganyar"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="expenseModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/30 transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Pengeluaran</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Modal 2: Catat Pemasukan Manual (Di luar tagihan bulanan) -->
    <div x-show="incomeModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="incomeModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">Catat Pemasukan Tambahan</h3>
                    <p class="text-xs text-slate-500">Mencatat biaya pasang baru, upgrade & penjualan alat</p>
                </div>
                <button @click="incomeModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('finance.income.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Kategori Pemasukan *</label>
                    <select name="category" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <option value="Biaya Pasang Baru">Biaya Pemasangan Baru (Registrasi)</option>
                        <option value="Penjualan Router / Alat">Penjualan Router / Access Point</option>
                        <option value="Jasa Servis & Setting">Jasa Servis / Setting Jaringan</option>
                        <option value="Pemasukan Lainnya">Pemasukan Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Nominal Pemasukan (Rp) *</label>
                    <input type="number" name="amount" required placeholder="Contoh: 250000" min="1"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-extrabold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Tanggal Transaksi *</label>
                        <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Metode Bayar *</label>
                        <select name="payment_method" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="cash">Tunai / Cash</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Keterangan / Rincian *</label>
                    <textarea name="description" rows="2" required placeholder="Contoh: Biaya pasang baru pelanggan Pak Herman di Desa Sukamaju"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="incomeModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Pemasukan</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    @if(auth()->user()->isOwner())
    <!-- Modal 3: Konfirmasi Reset Semua Data Keuangan (Mulai dari 0) -->
    <div x-show="resetModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div @click.away="resetModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-rose-200 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-rose-100">
                <div class="flex items-center gap-2 text-rose-600 font-extrabold text-base">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    <span>Reset Data Keuangan ke Rp 0</span>
                </div>
                <button type="button" @click="resetModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs space-y-2 leading-relaxed">
                <p class="font-bold">⚠️ Perhatian Khusus Owner:</p>
                <p>Tindakan ini akan mengosongkan seluruh catatan buku kas, transaksi pemasukan, dan pengeluaran demo sehingga angka Omzet, Biaya, dan Laba Bersih di sistem kembali bersih menjadi <strong>Rp 0</strong>.</p>
                <p class="text-[11px] text-rose-600">Gunakan fitur ini saat Anda siap memulai pencatatan uang nyata bisnis Hokigo WiFi.</p>
            </div>

            <form action="{{ route('finance.reset') }}" method="POST" class="space-y-4" x-data="{ confirmText: '' }">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Ketik kata <code class="bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded font-mono font-bold">RESET</code> untuk melanjutkan:
                    </label>
                    <input type="text" name="confirmation" x-model="confirmText" required placeholder="Ketik RESET"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-rose-300 font-mono text-xs uppercase font-bold text-center focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500">
                </div>

                <div class="space-y-2 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="delete_invoices" value="1" checked class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                        <span>Hapus tagihan demo & mulai tagihan baru</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="checkbox" name="delete_vouchers" value="1" checked class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                        <span>Reset data voucher hotspot juga</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="resetModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="confirmText.toUpperCase() !== 'RESET'"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold shadow-md shadow-rose-600/30 transition flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>Reset Sekarang</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
    @endif

</div>
@endsection
