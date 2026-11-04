@extends('layouts.app')

@section('title', 'Tagihan & Pembayaran WiFi')
@section('header_title', 'Tagihan & Pembayaran Pelanggan')

@section('content')
<div class="space-y-6" x-data="{ 
    payModalOpen: false, 
    selectedInvoice: null,
    generateModalOpen: false,
    openPayModal(inv) {
        this.selectedInvoice = inv;
        this.payModalOpen = true;
    }
}">

    <!-- Top Stats Cards for Selected Period -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        
        <!-- Total Tagihan -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Tagihan ({{ \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->translatedFormat('F Y') }})</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">
                Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $stats['total_count'] }} Pelanggan Diterbitkan</p>
        </div>

        <!-- Tagihan Lunas -->
        <div class="bg-emerald-50 rounded-3xl p-6 border border-emerald-200/80 shadow-xs">
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Sudah Lunas (Omzet)
            </span>
            <div class="text-2xl font-extrabold text-emerald-950 mt-1">
                Rp {{ number_format($stats['paid_amount'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-emerald-800 font-semibold mt-1">{{ $stats['paid_count'] }} Pelanggan Telah Membayar</p>
        </div>

        <!-- Belum Bayar -->
        <div class="bg-amber-50 rounded-3xl p-6 border border-amber-200/80 shadow-xs">
            <span class="text-xs font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Belum Bayar (Piutang)
            </span>
            <div class="text-2xl font-extrabold text-amber-950 mt-1">
                Rp {{ number_format($stats['unpaid_amount'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-amber-800 font-semibold mt-1">{{ $stats['unpaid_count'] }} Pelanggan Menunggu Pembayaran</p>
        </div>

    </div>

    <!-- Filter & Action Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="receipt" class="w-6 h-6 text-brand-blue"></i>
                    <span>Daftar Faktur Tagihan WiFi</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola status tagihan, konfirmasi pembayaran, dan kirim pengingat WhatsApp</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Generate Monthly Invoices Button -->
                <button type="button" @click="generateModalOpen = true" 
                        class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-blue-400"></i>
                    <span>Buat Tagihan Bulanan</span>
                </button>

                <!-- WhatsApp Reminders Quick Link -->
                <a href="{{ route('invoices.reminders') }}" 
                   class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/25 transition">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Pusat Reminder WhatsApp</span>
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-4 border-t border-slate-100">
            <!-- Period Selector -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Periode Bulan</label>
                <select name="period" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    @foreach($periods as $val => $label)
                        <option value="{{ $val }}" {{ $selectedPeriod === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Pembayaran</label>
                <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <option value="">Semua Status</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas (Sudah Bayar)</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Lewat Jatuh Tempo</option>
                </select>
            </div>

            <!-- Village Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Desa / Wilayah</label>
                <select name="village_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <option value="">Semua Desa</option>
                    @foreach($villages as $vil)
                        <option value="{{ $vil->id }}" {{ request('village_id') == $vil->id ? 'selected' : '' }}>
                            {{ $vil->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search & Submit -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pencarian</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / ID / WA..." 
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <button type="submit" class="py-2.5 px-4 bg-brand-blue hover:bg-brand-blueHover text-white rounded-xl font-bold text-xs transition">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </form>

    </div>

    <!-- Table of Invoices -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-4 pl-6">No. Faktur</th>
                        <th class="py-4">Pelanggan & Desa</th>
                        <th class="py-4">Jatuh Tempo</th>
                        <th class="py-4">Tagihan</th>
                        <th class="py-4">Status & Bukti</th>
                        <th class="py-4 pr-6 text-right">Aksi Penagihan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/60 transition">
                            
                            <!-- Invoice Number -->
                            <td class="py-4 pl-6">
                                <div class="font-bold text-slate-900 font-mono text-xs">{{ $inv->invoice_number }}</div>
                                <div class="text-[11px] text-slate-400">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $inv->period)->translatedFormat('F Y') }}
                                </div>
                            </td>

                            <!-- Customer Info -->
                            <td class="py-4">
                                <a href="{{ route('customers.show', $inv->customer) }}" class="font-bold text-slate-900 text-xs hover:text-brand-blue transition">
                                    {{ $inv->customer->name }}
                                </a>
                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-slate-400">{{ $inv->customer->customer_code }}</span> &bull; 
                                    <span class="text-brand-blue font-medium">{{ $inv->customer->village?->name ?? 'Luar Wilayah' }}</span>
                                </div>
                            </td>

                            <!-- Due Date -->
                            <td class="py-4 text-xs">
                                <span class="font-bold {{ $inv->status !== 'paid' && now()->gt($inv->due_date) ? 'text-rose-600' : 'text-slate-700' }}">
                                    {{ $inv->due_date->isoFormat('D MMMM Y') }}
                                </span>
                                @if($inv->status !== 'paid' && now()->gt($inv->due_date))
                                    <span class="block text-[10px] text-rose-500 font-semibold">
                                        Terlambat {{ now()->diffInDays($inv->due_date) }} hari
                                    </span>
                                @endif
                            </td>

                            <!-- Amount -->
                            <td class="py-4 text-xs">
                                <div class="font-extrabold text-slate-900">Rp {{ number_format($inv->amount, 0, ',', '.') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $inv->customer->package?->name ?? 'Internet' }}</div>
                            </td>

                            <!-- Status -->
                            <td class="py-4 text-xs">
                                @if($inv->status === 'paid')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold inline-flex items-center gap-1">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i> Lunas
                                    </span>
                                    <div class="text-[10px] text-slate-500 mt-1">
                                        {{ strtoupper($inv->payment_method ?? 'Cash') }} &bull; {{ $inv->paid_at ? $inv->paid_at->format('d/m/Y') : '' }}
                                    </div>
                                @elseif($inv->status === 'overdue')
                                    <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold inline-flex items-center gap-1 animate-pulse">
                                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Jatuh Tempo
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold inline-flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3.5 h-3.5"></i> Belum Bayar
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 pr-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    
                                    @if($inv->status === 'paid')
                                        <!-- Struk WA Lunas (Requirement 2 & 3) -->
                                        <a href="{{ $inv->whatsapp_receipt_url }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold transition" title="Kirim Bukti Pembayaran via WA">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>Kirim Struk WA</span>
                                        </a>

                                        <!-- Cancel Payment (if mistake) -->
                                        <form action="{{ route('invoices.mark-unpaid', $inv) }}" method="POST" onsubmit="return confirm('Batalkan pelunasan tagihan ini? Transaksi omzet terkait akan dihapus.')">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 transition" title="Batalkan Lunas">
                                                <i data-lucide="undo-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Send WhatsApp Bill (Direct Click to WhatsApp) -->
                                        <a href="{{ $inv->whatsapp_url }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition shadow-xs" title="Chat Tagihan WhatsApp">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                            <span>Chat WA</span>
                                        </a>

                                        <!-- Mark Paid Modal Trigger -->
                                        <button type="button" 
                                                @click="openPayModal({{ json_encode([
                                                    'id' => $inv->id,
                                                    'invoice_number' => $inv->invoice_number,
                                                    'customer_name' => $inv->customer->name,
                                                    'amount' => $inv->amount,
                                                    'amount_formatted' => number_format($inv->amount, 0, ',', '.'),
                                                    'action_url' => route('invoices.mark-paid', $inv)
                                                ]) }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold transition shadow-xs">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                            <span>Bayar</span>
                                        </button>
                                    @endif

                                    <!-- Print Receipt -->
                                    <a href="{{ route('invoices.print', $inv) }}" target="_blank"
                                       class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Cetak Struk Thermal / A4">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </a>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                Belum ada tagihan untuk periode {{ $selectedPeriod }}. Klik tombol "Buat Tagihan Bulanan" di atas untuk menerbitkan tagihan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <!-- Modal 1: Konfirmasi Pembayaran Tagihan (Mark Paid) -->
    <div x-show="payModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="payModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">Konfirmasi Pembayaran Tagihan</h3>
                    <p class="text-xs text-slate-500" x-text="selectedInvoice ? selectedInvoice.customer_name : ''"></p>
                </div>
                <button @click="payModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="selectedInvoice ? selectedInvoice.action_url : '#'" method="POST" class="space-y-4">
                @csrf
                
                <div class="bg-blue-50/70 p-4 rounded-2xl border border-blue-100 flex items-center justify-between">
                    <span class="text-xs text-slate-600 font-medium">Nominal Tagihan:</span>
                    <span class="text-lg font-extrabold text-brand-blue" x-text="'Rp ' + (selectedInvoice ? selectedInvoice.amount_formatted : 0)"></span>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Metode Pembayaran *</label>
                    <select name="payment_method" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <option value="cash">Tunai / Cash (Bayar di Tempat / Kantor)</option>
                        <option value="bca">Transfer Bank BCA</option>
                        <option value="bri">Transfer Bank BRI</option>
                        <option value="mandiri">Transfer Bank Mandiri</option>
                        <option value="qris">QRIS Hokigo</option>
                        <option value="dana">DANA / GoPay / OVO</option>
                    </select>
                </div>

                <!-- Payment Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Tanggal & Waktu Bayar</label>
                    <input type="datetime-local" name="paid_at" value="{{ now()->format('Y-m-d\TH:i') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Misal: Diterima oleh teknisi Dani / Bukti transfer WA"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 text-[11px] font-medium border border-emerald-100 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0 text-emerald-600"></i>
                    <span>Saat disimpan, sistem akan otomatis mencatat uang ini ke <strong>Omzet & Pemasukan</strong> bulan berjalan.</span>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="payModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Konfirmasi Lunas</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- Modal 2: Buat Tagihan Bulanan Sekaligus (Bulk Generate) -->
    <div x-show="generateModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="generateModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">Buat Tagihan Bulanan Serentak</h3>
                    <p class="text-xs text-slate-500">Menerbitkan invoice untuk semua pelanggan aktif</p>
                </div>
                <button @click="generateModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('invoices.generate') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Pilih Periode Tagihan *</label>
                    <input type="month" name="period" value="{{ $selectedPeriod }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <p class="text-[11px] text-slate-400 mt-1">Sistem hanya akan membuat tagihan bagi pelanggan yang belum memiliki invoice di bulan tersebut (tidak akan duplikat).</p>
                </div>

                <div class="p-3 rounded-xl bg-amber-50 text-amber-800 text-[11px] font-medium border border-amber-200/60 flex items-start gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-amber-600 mt-0.5"></i>
                    <span>Tagihan akan dibuat otomatis berdasarkan nominal bulanan dan tanggal jatuh tempo masing-masing pelanggan.</span>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="generateModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        <span>Proses Buat Tagihan</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
