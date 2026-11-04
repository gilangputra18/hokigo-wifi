@extends('layouts.app')

@section('title', 'Pusat Pengingat WhatsApp Jatuh Tempo')
@section('header_title', 'Pusat Pengingat Tagihan WhatsApp')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-800 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-emerald-100 text-xs font-semibold backdrop-blur-md mb-2">
                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> WhatsApp Direct Notification Engine
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Kirim Tagihan & Follow Up Otomatis</h2>
                <p class="text-emerald-100 text-xs sm:text-sm mt-1 max-w-xl">
                    Klik tombol "Kirim WA" pada setiap pelanggan untuk langsung membuka obrolan WhatsApp dengan format tagihan resmi, rincian paket, rekening transfer, dan tanggal jatuh tempo.
                </p>
            </div>

            <!-- Stats badges -->
            <div class="flex items-center gap-3">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl px-5 py-3 border border-white/15 text-center">
                    <div class="text-2xl font-black text-amber-300">{{ $todayCount }}</div>
                    <div class="text-[11px] font-semibold text-emerald-100 uppercase tracking-wider">Jatuh Tempo Hari Ini</div>
                </div>
                <div class="bg-white/10 backdrop-blur-md rounded-2xl px-5 py-3 border border-white/15 text-center">
                    <div class="text-2xl font-black text-rose-300">{{ $overdueCount }}</div>
                    <div class="text-[11px] font-semibold text-emerald-100 uppercase tracking-wider">Sudah Lewat Tempo</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('invoices.reminders', ['filter' => 'today_or_overdue']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $filter === 'today_or_overdue' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Perlu Follow-Up ({{ $todayCount + $overdueCount }})
            </a>
            <a href="{{ route('invoices.reminders', ['filter' => 'today']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $filter === 'today' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Jatuh Tempo Hari Ini ({{ $todayCount }})
            </a>
            <a href="{{ route('invoices.reminders', ['filter' => 'overdue']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $filter === 'overdue' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Terlambat / Overdue ({{ $overdueCount }})
            </a>
        </div>

        <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1">
            <span>Kembali ke Semua Tagihan</span> &rarr;
        </a>
    </div>

    <!-- Customer Reminder Cards Grid -->
    @if($invoices->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80">
            <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="check-check" class="w-7 h-7"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-base">Tidak ada antrean WhatsApp</h3>
            <p class="text-xs text-slate-500 mt-1">Semua pelanggan pada filter ini sudah melunasi tagihannya atau belum jatuh tempo.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($invoices as $inv)
                @php
                    $isToday = $inv->due_date->isToday();
                    $isPast = now()->gt($inv->due_date);
                @endphp
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between space-y-4">
                    
                    <div class="space-y-3">
                        <!-- Top header card -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">{{ $inv->customer->name }}</h3>
                                <div class="text-xs text-slate-400 font-mono">{{ $inv->customer->customer_code }}</div>
                            </div>
                            
                            @if($isToday)
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold shrink-0">
                                    Tempo Hari Ini
                                </span>
                            @elseif($isPast)
                                <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 text-[11px] font-bold shrink-0 animate-pulse">
                                    Lewat {{ now()->diffInDays($inv->due_date) }} Hari
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold shrink-0">
                                    {{ $inv->due_date->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>

                        <!-- Details -->
                        <div class="bg-slate-50 p-3.5 rounded-2xl space-y-1.5 text-xs">
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Desa:</span>
                                <span class="font-bold text-slate-800">{{ $inv->customer->village?->name ?? 'Luar Wilayah' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Paket WiFi:</span>
                                <span class="font-medium text-slate-800">{{ $inv->customer->package?->name ?? 'Internet' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>No. WhatsApp:</span>
                                <span class="font-bold text-emerald-700 font-mono">{{ $inv->customer->phone }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600 pt-1 border-t border-slate-200/70">
                                <span class="font-bold">Total Tagihan:</span>
                                <span class="font-extrabold text-sm text-brand-blue">Rp {{ number_format($inv->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Notes from customer if exists -->
                        @if($inv->customer->notes)
                            <div class="p-2.5 rounded-xl bg-amber-50/70 text-amber-900 text-[11px] border border-amber-200/60 line-clamp-2">
                                <strong>Note:</strong> {{ $inv->customer->notes }}
                            </div>
                        @endif

                        @if($inv->wa_sent_at)
                            <div class="text-[10px] text-slate-400 flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3 text-emerald-500"></i>
                                <span>Terakhir dikirim: {{ $inv->wa_sent_at->isoFormat('D MMM Y HH:mm') }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Bottom Buttons -->
                    <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
                        <!-- 1-Click WhatsApp Button (Requirement 2) -->
                        <a href="{{ $inv->whatsapp_url }}" target="_blank"
                           class="flex-1 py-2.5 px-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Kirim Chat WA</span>
                        </a>

                        <!-- Quick Mark Lunas -->
                        <form action="{{ route('invoices.mark-paid', $inv) }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment_method" value="cash">
                            <button type="submit" onclick="return confirm('Tandai tagihan {{ $inv->customer->name }} lunas tunai?')"
                                    class="py-2.5 px-3 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs shadow-xs transition" title="Tandai Lunas">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>

        @if($invoices->hasPages())
            <div class="p-4 bg-white rounded-3xl border border-slate-200/80 shadow-xs">
                {{ $invoices->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
