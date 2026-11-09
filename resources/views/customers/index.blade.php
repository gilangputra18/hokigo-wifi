@extends('layouts.app')

@section('title', 'Daftar Pelanggan')
@section('header_title', 'Manajemen Pelanggan Hokigo')

@section('content')
<div class="space-y-6" x-data="customerIndexApp()">

    <!-- Header Actions & Search Filter Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="users" class="w-6 h-6 text-brand-blue"></i>
                    <span>Daftar Pelanggan WiFi</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1">Dikelompokkan berdasarkan desa lengkap dengan catatan teknis & status tagihan</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Toggle View Mode: Grouped by Village vs Table -->
                <div class="flex items-center bg-slate-100 p-1 rounded-xl text-xs font-bold text-slate-600">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'grouped']) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $viewMode === 'grouped' ? 'bg-white text-brand-blue shadow-xs' : 'hover:text-slate-900' }}">
                       <i data-lucide="layout-grid" class="w-3.5 h-3.5 inline mr-1"></i> Kelompok Desa
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $viewMode === 'list' ? 'bg-white text-brand-blue shadow-xs' : 'hover:text-slate-900' }}">
                       <i data-lucide="list" class="w-3.5 h-3.5 inline mr-1"></i> Tabel Semua
                    </a>
                </div>

                <a href="{{ route('customers.create') }}" class="px-4 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-blue-600/25 transition">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Tambah Pelanggan Baru</span>
                </a>
            </div>
        </div>

        <!-- Filters Form -->
        <form method="GET" action="{{ route('customers.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-4 border-t border-slate-100">
            <input type="hidden" name="view" value="{{ $viewMode }}">

            <!-- Search Field -->
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, WA, PPPoE, catatan..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
            </div>

            <!-- Filter Desa -->
            <div>
                <select name="village_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <option value="">Semua Desa / Wilayah</option>
                    @foreach($villages as $vil)
                        <option value="{{ $vil->id }}" {{ request('village_id') == $vil->id ? 'selected' : '' }}>
                            {{ $vil->name }} ({{ $vil->customers_count }} org)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <option value="">Semua Status Pelanggan</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="isolated" {{ request('status') === 'isolated' ? 'selected' : '' }}>Isolir</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                @if(request()->anyFilled(['search', 'village_id', 'status']))
                    <a href="{{ route('customers.index', ['view' => $viewMode]) }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </form>

    </div>

    <!-- Mode 1: Grouped by Village (Requirement 1) -->
    @if($viewMode === 'grouped' && $groupedCustomers)
        @if($groupedCustomers->isEmpty())
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80">
                <i data-lucide="user-x" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                <h3 class="font-bold text-slate-700 text-base">Tidak ada data pelanggan yang cocok</h3>
                <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci pencarian atau reset filter.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($groupedCustomers as $villageName => $customersInVillage)
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden" x-data="{ open: true }">
                        
                        <!-- Village Group Header -->
                        <div class="bg-slate-50/80 px-4 sm:px-6 py-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3 cursor-pointer select-none"
                             @click="open = !open">
                            <div class="flex items-center justify-between sm:justify-start gap-3 w-full sm:w-auto">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-brand-blue/10 text-brand-blue flex items-center justify-center font-bold shrink-0">
                                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-extrabold text-slate-900 text-base leading-tight">{{ $villageName }}</h3>
                                        <p class="text-xs text-slate-500">{{ $customersInVillage->count() }} Pelanggan Terdaftar</p>
                                    </div>
                                </div>
                                <button type="button" class="sm:hidden text-slate-400 hover:text-slate-600 transition p-1">
                                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                </button>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
                                <span class="text-xs font-bold px-3 py-1 rounded-full bg-white border border-slate-200 text-slate-700 shadow-xs">
                                    Total Omzet: Rp {{ number_format($customersInVillage->sum('monthly_fee'), 0, ',', '.') }}/bln
                                </span>
                                <button type="button" class="hidden sm:block text-slate-400 hover:text-slate-600 transition">
                                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Customers in this Village -->
                        <div x-show="open" class="divide-y divide-slate-100">
                            @foreach($customersInVillage as $customer)
                                @php
                                    $currInvoice = $customer->invoices->first();
                                @endphp
                                <div class="p-5 hover:bg-slate-50/60 transition flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                    
                                    <!-- Left: Info & Catatan (Notes) -->
                                    <div class="space-y-2 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('customers.show', $customer) }}" class="font-bold text-slate-900 text-base hover:text-brand-blue transition">
                                                {{ $customer->name }}
                                            </a>
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-mono font-bold">
                                                {{ $customer->customer_code }}
                                            </span>

                                            <!-- Status Pelanggan Badge -->
                                            @if($customer->status === 'active')
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">Aktif</span>
                                            @elseif($customer->status === 'isolated')
                                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">Isolir</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[11px] font-bold">Nonaktif</span>
                                            @endif

                                            <!-- Status Tagihan Bulan Ini -->
                                            @if($currInvoice)
                                                @if($currInvoice->status === 'paid')
                                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold flex items-center gap-1">
                                                        <i data-lucide="check" class="w-3 h-3"></i> Lunas ({{ $currentPeriod }})
                                                    </span>
                                                @elseif($currInvoice->status === 'overdue')
                                                    <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-bold flex items-center gap-1 animate-pulse">
                                                        <i data-lucide="alert-triangle" class="w-3 h-3"></i> Jatuh Tempo
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold flex items-center gap-1">
                                                        <i data-lucide="clock" class="w-3 h-3"></i> Belum Bayar
                                                    </span>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                            @if(!auth()->user()->isTeknisi() && $customer->phone)
                                            <span class="flex items-center gap-1 text-slate-700 font-medium">
                                                <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-600"></i> {{ $customer->phone }}
                                            </span>
                                            @endif
                                            @if($customer->house_number)
                                                <span class="flex items-center gap-1 text-slate-700 font-semibold">
                                                    <i data-lucide="home" class="w-3.5 h-3.5 text-amber-600"></i> {{ $customer->house_number }}
                                                </span>
                                            @endif
                                            @if($customer->redaman)
                                                <span class="font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 text-[10px] font-bold">
                                                    📶 {{ $customer->redaman }}
                                                </span>
                                            @endif
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="wifi" class="w-3.5 h-3.5 text-brand-blue"></i> {{ $customer->package?->name ?? 'Internet' }}
                                            </span>
                                            <span class="font-bold text-slate-800">
                                                Rp {{ number_format($customer->monthly_fee, 0, ',', '.') }}/bln
                                            </span>
                                            <span class="text-slate-400">
                                                Tgl Tagihan: Setiap tgl <strong>{{ $customer->due_date_day }}</strong>
                                            </span>
                                            @if($customer->pppoe_user)
                                                <span class="font-mono text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200 text-[10px] font-semibold">
                                                    User: {{ $customer->pppoe_user }}
                                                </span>
                                            @endif
                                            @if($customer->pppoe_password)
                                                <span class="font-mono text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 text-[10px] font-semibold">
                                                    Sandi: {{ $customer->pppoe_password }}
                                                </span>
                                            @endif
                                            @if($customer->wifi_ssid)
                                                <span class="text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 text-[10px] font-bold">
                                                    📶 WiFi: {{ $customer->wifi_ssid }}
                                                </span>
                                            @endif
                                            @if($customer->wifi_password)
                                                <span class="font-mono text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 text-[10px] font-semibold">
                                                    🔑 Sandi: {{ $customer->wifi_password }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Catatan Khusus Pelanggan (Requirement 1: Ada Note nya juga) -->
                                        @if($customer->notes)
                                            <div class="mt-2 p-2.5 rounded-xl bg-amber-50/70 border border-amber-200/60 text-xs text-amber-900 flex items-start gap-2">
                                                <i data-lucide="sticky-note" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                                                <div>
                                                    <span class="font-bold text-amber-800">Catatan Pelanggan:</span>
                                                    <span>{{ $customer->notes }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Right: Actions -->
                                    <div class="flex flex-wrap items-center gap-2 shrink-0 pt-2 lg:pt-0">
                                        @if(!auth()->user()->isTeknisi())
                                            @if(!$currInvoice || $currInvoice->status !== 'paid')
                                                <!-- Tombol Tandai Lunas Cepat (Bayar Lebih Awal) -->
                                                <button type="button" 
                                                        @click="openPayModal({
                                                            customer_id: {{ $customer->id }},
                                                            customer_name: '{{ addslashes($customer->name) }}',
                                                            customer_code: '{{ $customer->customer_code }}',
                                                            amount: {{ $currInvoice ? (float)$currInvoice->amount : (float)$customer->monthly_fee }},
                                                            amount_formatted: '{{ number_format($currInvoice ? $currInvoice->amount : $customer->monthly_fee, 0, ',', '.') }}',
                                                            period: '{{ $currentPeriod }}',
                                                            due_date_day: {{ $customer->due_date_day ?: 10 }},
                                                            action_url: '{{ route('customers.pay-current', $customer) }}'
                                                        })"
                                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-xs" title="Tandai Lunas">
                                                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                                    <span>Tandai Lunas</span>
                                                </button>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Lunas
                                                </span>
                                            @endif

                                            <!-- WhatsApp Chat Button (Direct Click) -->
                                            @if($currInvoice)
                                                @if($currInvoice->status === 'paid')
                                                    <a href="{{ $currInvoice->whatsapp_receipt_url }}" target="_blank"
                                                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 font-bold text-xs transition shadow-xs" title="Kirim Bukti Lunas WA">
                                                        <i data-lucide="receipt" class="w-4 h-4"></i>
                                                        <span>Struk WA</span>
                                                    </a>
                                                @else
                                                    <a href="{{ $currInvoice->whatsapp_url }}" target="_blank"
                                                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs transition shadow-xs">
                                                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                                                        <span>WA Tagihan</span>
                                                    </a>
                                                @endif
                                            @else
                                                <a href="https://api.whatsapp.com/send?phone={{ $customer->clean_phone }}" target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 font-bold text-xs transition">
                                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                                    <span>Chat WA</span>
                                                </a>
                                            @endif
                                        @endif

                                        <!-- Detail Button -->
                                        <a href="{{ route('customers.show', $customer) }}" 
                                           class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                                            Detail
                                        </a>

                                        <!-- Edit Button -->
                                        <a href="{{ route('customers.edit', $customer) }}" 
                                           class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Edit">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    @endif

    <!-- Mode 2: Standard Table View -->
    @if($viewMode === 'list' && $paginatedCustomers)
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="py-4 pl-6">ID & Pelanggan</th>
                            <th class="py-4">Desa</th>
                            <th class="py-4">Paket & Biaya</th>
                            <th class="py-4">Catatan (Note)</th>
                            <th class="py-4">Status & Tagihan</th>
                            <th class="py-4 pr-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($paginatedCustomers as $customer)
                            @php $currInvoice = $customer->invoices->first(); @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-4 pl-6">
                                    <div class="font-bold text-slate-900">{{ $customer->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">
                                        {{ $customer->customer_code }}
                                        @if(!auth()->user()->isTeknisi() && $customer->phone)
                                            &bull; {{ $customer->phone }}
                                        @endif
                                    </div>
                                    @if($customer->house_number)
                                        <div class="text-[11px] text-slate-600 font-semibold mt-0.5">🏠 {{ $customer->house_number }}</div>
                                    @endif
                                </td>
                                <td class="py-4 text-xs font-medium text-slate-700">
                                    <div>{{ $customer->village?->name ?? 'Luar Wilayah' }}</div>
                                    @if($customer->redaman)
                                        <div class="text-[10px] text-emerald-700 font-mono mt-0.5 font-bold">📶 {{ $customer->redaman }}</div>
                                    @endif
                                </td>
                                <td class="py-4 text-xs">
                                    <div class="font-bold text-slate-900">Rp {{ number_format($customer->monthly_fee, 0, ',', '.') }}</div>
                                    <div class="text-slate-400">{{ $customer->package?->name ?? 'Internet' }}</div>
                                </td>
                                <td class="py-4 text-xs max-w-xs">
                                    @if($customer->notes)
                                        <div class="p-2 rounded-lg bg-amber-50 text-amber-900 text-[11px] border border-amber-200/60 line-clamp-2">
                                            {{ $customer->notes }}
                                        </div>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-4 text-xs">
                                    <div class="flex flex-col gap-1">
                                        @if($customer->status === 'active')
                                            <span class="w-max px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Aktif</span>
                                        @elseif($customer->status === 'isolated')
                                            <span class="w-max px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Isolir</span>
                                        @else
                                            <span class="w-max px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold">Nonaktif</span>
                                        @endif

                                        @if($currInvoice)
                                            <span class="text-[11px] font-semibold {{ $currInvoice->status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">
                                                {{ $currInvoice->status === 'paid' ? 'Lunas' : 'Belum Bayar' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(!auth()->user()->isTeknisi())
                                            @if(!$currInvoice || $currInvoice->status !== 'paid')
                                                <button type="button" 
                                                        @click="openPayModal({
                                                            customer_id: {{ $customer->id }},
                                                            customer_name: '{{ addslashes($customer->name) }}',
                                                            customer_code: '{{ $customer->customer_code }}',
                                                            amount: {{ $currInvoice ? (float)$currInvoice->amount : (float)$customer->monthly_fee }},
                                                            amount_formatted: '{{ number_format($currInvoice ? $currInvoice->amount : $customer->monthly_fee, 0, ',', '.') }}',
                                                            period: '{{ $currentPeriod }}',
                                                            due_date_day: {{ $customer->due_date_day ?: 10 }},
                                                            action_url: '{{ route('customers.pay-current', $customer) }}'
                                                        })"
                                                        class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs" title="Tandai Lunas">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </button>
                                            @else
                                                <a href="{{ $currInvoice->whatsapp_receipt_url }}" target="_blank"
                                                   class="p-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition shadow-xs" title="Kirim Struk Lunas WA">
                                                    <i data-lucide="receipt" class="w-4 h-4"></i>
                                                </a>
                                            @endif

                                            @if($currInvoice && $currInvoice->status !== 'paid')
                                                <a href="{{ $currInvoice->whatsapp_url }}" target="_blank"
                                                   class="p-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white transition shadow-xs" title="Kirim Tagihan WA">
                                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                                </a>
                                            @endif
                                        @endif
                                        <a href="{{ route('customers.show', $customer) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Lihat Detail">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Edit">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada data pelanggan ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($paginatedCustomers->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $paginatedCustomers->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Modal Pelunasan Cepat (Bisa Bayar Duluan Sebelum Jatuh Tempo) -->
    <div x-show="payModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.outside="payModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold shrink-0">
                        <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Pelunasan Tagihan WiFi</h3>
                        <p class="text-[11px] text-slate-500">Bisa lunaskan lebih awal sebelum tanggal jatuh tempo</p>
                    </div>
                </div>
                <button @click="payModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="selectedCustomer.action_url" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="period" :value="selectedCustomer.period">

                <!-- Ringkasan Info Pelanggan -->
                <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900" x-text="selectedCustomer.customer_name"></span>
                        <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900" x-text="selectedCustomer.customer_code"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-600 pt-1 border-t border-emerald-200/60">
                        <span>Nominal Tagihan:</span>
                        <span class="text-base font-black text-emerald-900" x-text="'Rp ' + selectedCustomer.amount_formatted"></span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span>Jatuh Tempo Asli:</span>
                        <span class="font-bold text-slate-700" x-text="'Setiap Tanggal ' + selectedCustomer.due_date_day"></span>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pembayaran *</label>
                    <select name="payment_method" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                        <option value="transfer">Transfer BSI (7233297792 a/n MUHAMMAD IRZAQ IRAWAN)</option>
                        <option value="cash">Tunai (Cash ke Kantor / Titip Teknisi)</option>
                        <option value="transfer_lain">Transfer Bank Lain / QRIS / DANA</option>
                    </select>
                </div>

                <!-- Tanggal Diterima -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Diterima Pembayaran *</label>
                    <input type="date" name="paid_at" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    <p class="text-[11px] text-slate-400 mt-1">Jika pelanggan bayar duluan (misal tanggal 4 atau 5), tanggal bisa disesuaikan.</p>
                </div>

                <!-- Catatan Opsional -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pembayaran (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Transfer BSI jam 10 pagi / Titip teknisi"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                </div>

                <!-- Alert Perlindungan Chat Otomatis -->
                <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-200/80 flex items-start gap-2.5 text-blue-900 text-xs">
                    <i data-lucide="shield-check" class="w-4 h-4 text-brand-blue shrink-0 mt-0.5"></i>
                    <p class="text-[11px] leading-relaxed">
                        <strong>Proteksi Chat Otomatis:</strong> Setelah ditandai Lunas, sistem otomatis <strong>TIDAK AKAN</strong> mengirimkan pesan penagihan WhatsApp pada tanggal jatuh tempo nanti.
                    </p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="payModalOpen = false" 
                            class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Konfirmasi Lunas</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    function customerIndexApp() {
        return {
            payModalOpen: false,
            selectedCustomer: {
                customer_id: '',
                customer_name: '',
                customer_code: '',
                amount: 0,
                amount_formatted: '0',
                period: '{{ $currentPeriod }}',
                due_date_day: 10,
                action_url: ''
            },
            openPayModal(data) {
                this.selectedCustomer = data;
                this.payModalOpen = true;
            }
        };
    }
</script>
@endsection
