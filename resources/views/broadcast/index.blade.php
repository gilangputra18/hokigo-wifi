@extends('layouts.app')

@section('title', 'Broadcast WhatsApp Desa')

@section('content')
@php
    $villagesData = $villages->map(function($v) {
        return [
            'id' => $v->id,
            'name' => $v->name,
            'code' => $v->code,
            'customers' => $v->customers->map(function($c) use ($v) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'code' => $c->customer_code,
                    'phone' => $c->phone,
                    'address' => $c->address,
                    'status' => $c->status,
                    'village_id' => $v->id,
                    'village_name' => $v->name,
                    'package_name' => $c->package?->name ?? 'Internet WiFi',
                ];
            })->values()->all(),
        ];
    });

    if ($noVillageCustomers->isNotEmpty()) {
        $villagesData->push([
            'id' => 0,
            'name' => 'Tanpa Desa / Luar Wilayah',
            'code' => null,
            'customers' => $noVillageCustomers->map(function($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'code' => $c->customer_code,
                    'phone' => $c->phone,
                    'address' => $c->address,
                    'status' => $c->status,
                    'village_id' => 0,
                    'village_name' => 'Luar Wilayah',
                    'package_name' => $c->package?->name ?? 'Internet WiFi',
                ];
            })->values()->all(),
        ]);
    }

    $villagesJson = $villagesData->toJson();
@endphp

<div class="space-y-6" x-data="broadcastApp({
    villages: {{ $villagesJson }},
    totalActiveCustomers: {{ $stats['active_customers'] }},
    totalAllCustomers: {{ $stats['total_customers'] }}
})">

    <!-- Page Header & Gateway Notice -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="p-2.5 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 shadow-xs">
                    <i data-lucide="radio" class="w-6 h-6"></i>
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Broadcast WhatsApp Desa</h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                    Spesifik Per Orang & Desa
                </span>
            </div>
            <p class="text-slate-500 text-sm max-w-2xl leading-relaxed">
                Pilih desa tertentu (misal <span class="font-bold text-slate-800">Desa Kalimantan</span>) lalu buka daftar orangnya untuk memilih siapa saja yang akan dikirimi chat WA, atau langsung pilih seluruh warga desa dan semua desa sekaligus.
            </p>
        </div>

        <!-- WhatsApp Gateway Status Card -->
        <div class="shrink-0 bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 flex items-center gap-4 min-w-[240px]">
            @if($isGatewayConfigured)
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status WA Gateway</div>
                    <div class="text-xs font-bold text-emerald-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Aktif ({{ strtoupper($gatewayProvider) }})
                    </div>
                </div>
            @else
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status WA Gateway</div>
                    <a href="{{ route('settings.index') }}" class="text-xs font-bold text-rose-600 hover:underline flex items-center gap-1">
                        Belum Terhubung &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Alert Success / Error Messages -->
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
                <div class="font-bold">Perhatian: Pengiriman tidak dapat diproses:</div>
                <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Main Broadcast Form -->
    <form action="{{ route('broadcast.send') }}" method="POST" @submit="confirmSend($event)">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT COLUMN: Granular Target Selection (Villages + Specific Customers) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">1</span>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900">Pilih Sasaran Orang & Desa</h2>
                                <p class="text-xs text-slate-500">Klik nama desa untuk membuka daftar orangnya & pilih siapa saja yang dikirimi.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Toolbar: Search & Quick Batch Actions -->
                    <div class="space-y-2.5">
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <!-- Search box -->
                            <div class="relative flex-1">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                <input type="text" 
                                       x-model="searchQuery" 
                                       placeholder="Cari desa atau nama orang (misal: Kalimantan, Budi)..." 
                                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            </div>

                            <!-- Filter status toggle -->
                            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl shrink-0">
                                <button type="button" 
                                        @click="setStatusFilter('active')"
                                        :class="statusFilter === 'active' ? 'bg-white text-emerald-700 shadow-2xs font-bold' : 'text-slate-500 font-medium'"
                                        class="px-2.5 py-1 text-[11px] rounded-lg transition">
                                    Hanya Aktif
                                </button>
                                <button type="button" 
                                        @click="setStatusFilter('all')"
                                        :class="statusFilter === 'all' ? 'bg-white text-indigo-700 shadow-2xs font-bold' : 'text-slate-500 font-medium'"
                                        class="px-2.5 py-1 text-[11px] rounded-lg transition">
                                    Semua
                                </button>
                            </div>
                        </div>

                        <!-- Quick action buttons -->
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-slate-100 text-xs">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button" 
                                        @click="selectAllCustomers()" 
                                        class="px-3 py-1.5 rounded-xl font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition">
                                    ✓ Pilih Semua Pelanggan (<span x-text="totalFilteredCount"></span>)
                                </button>
                                <button type="button" 
                                        @click="unselectAllCustomers()" 
                                        class="px-3 py-1.5 rounded-xl font-bold bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200 transition">
                                    ✕ Kosongkan Pilihan
                                </button>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" 
                                        @click="expandAll()" 
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
                                    Buka Semua
                                </button>
                                <span class="text-slate-300">|</span>
                                <button type="button" 
                                        @click="collapseAll()" 
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
                                    Tutup Semua
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Villages & Customers List -->
                    <div class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
                        <template x-for="village in filteredVillages" :key="village.id">
                            <div class="border rounded-2xl overflow-hidden transition-all shadow-2xs"
                                 :class="villageSelectionStatus(village) === 'all' ? 'border-indigo-400 bg-indigo-50/20' : (villageSelectionStatus(village) === 'partial' ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200/90 bg-white')">
                                
                                <!-- Village Card Header -->
                                <div class="p-3.5 flex items-center justify-between gap-3 cursor-pointer select-none"
                                     :class="isVillageExpanded(village.id) ? 'border-b border-slate-200/70 bg-slate-50/80' : 'hover:bg-slate-50/50'"
                                     @click="toggleExpandVillage(village.id)">
                                    
                                    <div class="flex items-center gap-3 min-w-0">
                                        <!-- Checkbox toggle for entire village -->
                                        <div class="shrink-0" @click.stop="toggleVillageAll(village)">
                                            <input type="checkbox" 
                                                   :checked="villageSelectionStatus(village) === 'all'"
                                                   :indeterminate="villageSelectionStatus(village) === 'partial'"
                                                   class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer">
                                        </div>

                                        <!-- Village Name & Code -->
                                        <div class="min-w-0">
                                            <div class="text-sm font-extrabold text-slate-900 truncate flex items-center gap-1.5">
                                                <span x-text="village.name"></span>
                                                <template x-if="village.code">
                                                    <span class="text-[10px] font-mono text-slate-400 font-normal" x-text="'(' + village.code + ')'"></span>
                                                </template>
                                            </div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                                                <span x-text="getFilteredCustomers(village).length + ' Orang'"></span>
                                                <span class="text-slate-300">&bull;</span>
                                                <span class="font-bold" 
                                                      :class="selectedInVillageCount(village) > 0 ? 'text-indigo-600 font-extrabold' : 'text-slate-400'"
                                                      x-text="selectedInVillageCount(village) + ' dipilih'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Badge & Expand Button -->
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-xs font-bold px-2.5 py-1 rounded-xl transition"
                                              :class="villageSelectionStatus(village) === 'all' ? 'bg-indigo-600 text-white' : (villageSelectionStatus(village) === 'partial' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600')">
                                            <span x-text="selectedInVillageCount(village)"></span>/<span x-text="getFilteredCustomers(village).length"></span>
                                        </span>
                                        
                                        <!-- Expand/Collapse Chevron -->
                                        <button type="button" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition"
                                                :title="isVillageExpanded(village.id) ? 'Tutup Daftar' : 'Buka Daftar Orang'">
                                            <svg class="w-4 h-4 transition-transform duration-200" 
                                                 :class="isVillageExpanded(village.id) ? 'rotate-180' : ''"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    </div>

                                </div>

                                <!-- Collapsible Customer List (The People Inside This Village) -->
                                <div x-show="isVillageExpanded(village.id)" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-cloak 
                                     class="p-3 bg-slate-50/60 space-y-2">
                                    
                                    <!-- Village Sub-Header Quick Actions -->
                                    <div class="flex items-center justify-between text-xs pb-1.5 border-b border-slate-200/60 px-1">
                                        <span class="text-slate-500 font-medium">
                                            Orang-orang di <strong class="text-slate-800" x-text="village.name"></strong>:
                                        </span>
                                        <div class="flex items-center gap-2 font-bold text-[11px]">
                                            <button type="button" 
                                                    @click="selectVillageAll(village)" 
                                                    class="text-indigo-600 hover:underline">
                                                Pilih Semua
                                            </button>
                                            <span class="text-slate-300">|</span>
                                            <button type="button" 
                                                    @click="deselectVillageAll(village)" 
                                                    class="text-slate-500 hover:underline">
                                                Kosongkan
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Customer Rows -->
                                    <div class="space-y-1.5 max-h-[260px] overflow-y-auto pr-1">
                                        <template x-for="customer in getFilteredCustomers(village)" :key="customer.id">
                                            <label class="p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-3 select-none"
                                                   :class="isCustomerSelected(customer.id) ? 'bg-indigo-50/90 border-indigo-300 shadow-2xs' : 'bg-white border-slate-200/70 hover:border-slate-300'">
                                                
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <input type="checkbox" 
                                                           name="customer_ids[]" 
                                                           :value="customer.id" 
                                                           :checked="isCustomerSelected(customer.id)"
                                                           @click.stop="toggleCustomer(customer.id)"
                                                           class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                                    
                                                    <div class="min-w-0">
                                                        <div class="text-xs font-bold text-slate-900 truncate flex items-center gap-1.5">
                                                            <span x-text="customer.name"></span>
                                                            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-100 text-slate-500" x-text="customer.code"></span>
                                                        </div>
                                                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                            <span class="flex items-center gap-1 text-slate-600 font-mono">
                                                                <i data-lucide="phone" class="w-3 h-3 text-emerald-500 inline"></i>
                                                                <span x-text="customer.phone || 'Tanpa No HP'"></span>
                                                            </span>
                                                            <template x-if="customer.address">
                                                                <span class="truncate text-slate-400" x-text="'&bull; ' + customer.address"></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Status Badge -->
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0"
                                                      :class="customer.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'"
                                                      x-text="customer.status === 'active' ? 'Aktif' : 'Isolir'">
                                                </span>
                                            </label>
                                        </template>

                                        <template x-if="getFilteredCustomers(village).length === 0">
                                            <div class="text-center py-4 text-xs text-slate-400">
                                                Tidak ada pelanggan yang sesuai dengan pencarian / filter di desa ini.
                                            </div>
                                        </template>
                                    </div>

                                </div>

                            </div>
                        </template>
                    </div>

                    <!-- Dynamic Real-time Selection Box -->
                    <div class="p-4 rounded-2xl bg-slate-900 text-white flex items-center justify-between gap-3 shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-indigo-500/30 text-indigo-300 flex items-center justify-center shrink-0">
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">Sasaran Broadcast Terpilih:</div>
                                <div class="text-sm font-extrabold text-white flex items-center gap-1.5">
                                    <span x-text="selectedCount + ' Orang'"></span>
                                    <span class="text-slate-400 font-normal text-xs" x-text="'(dari ' + selectedVillagesCount + ' Desa)'"></span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black text-indigo-400" x-text="selectedCount"></div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Penerima WA</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT COLUMN: Message Composer & Live WhatsApp Preview -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">2</span>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900">Isi Pesan WhatsApp</h2>
                                <p class="text-xs text-slate-500">Tulis pesan atau pilih template otomatis di bawah.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Template Buttons -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                            Pilihan Template Cepat (1-Klik Isi):
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" 
                                    @click="setTemplate('trouble')"
                                    class="px-2.5 py-2 rounded-xl text-xs font-bold border border-rose-200 bg-rose-50/70 hover:bg-rose-100 text-rose-800 transition flex items-center justify-center gap-1">
                                <span>⚠️</span> Trouble
                            </button>
                            <button type="button" 
                                    @click="setTemplate('maintenance')"
                                    class="px-2.5 py-2 rounded-xl text-xs font-bold border border-amber-200 bg-amber-50/70 hover:bg-amber-100 text-amber-800 transition flex items-center justify-center gap-1">
                                <span>🔧</span> Rawat
                            </button>
                            <button type="button" 
                                    @click="setTemplate('normal')"
                                    class="px-2.5 py-2 rounded-xl text-xs font-bold border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100 text-emerald-800 transition flex items-center justify-center gap-1">
                                <span>✅</span> Normal
                            </button>
                            <button type="button" 
                                    @click="setTemplate('announcement')"
                                    class="px-2.5 py-2 rounded-xl text-xs font-bold border border-blue-200 bg-blue-50/70 hover:bg-blue-100 text-blue-800 transition flex items-center justify-center gap-1">
                                <span>📢</span> Umum
                            </button>
                        </div>
                    </div>

                    <!-- Broadcast Title (Internal Note) -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700">Judul / Catatan Broadcast (Opsional):</label>
                        <input type="text" 
                               name="title" 
                               x-model="title" 
                               placeholder="Contoh: Trouble Kabel Putus Desa Kalimantan" 
                               class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>

                    <!-- Message Textarea -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700">Kolom Pesan WhatsApp * :</label>
                            <span class="text-[11px] text-slate-400 font-mono" x-text="message.length + ' karakter'"></span>
                        </div>
                        <textarea name="message" 
                                  x-model="message" 
                                  rows="7" 
                                  required
                                  placeholder="Ketik pesan WhatsApp yang ingin disampaikan..." 
                                  class="w-full p-3.5 text-sm rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 leading-relaxed font-sans"></textarea>
                    </div>

                    <!-- Dynamic Placeholders Helper Buttons -->
                    <div class="space-y-1.5 bg-slate-50 p-3 rounded-2xl border border-slate-200/60">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Variabel Otomatis (Klik untuk menyisipkan):</div>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="insertTag('{nama}')" class="px-2 py-1 bg-white border border-slate-200 hover:border-indigo-300 rounded-lg text-xs font-mono font-semibold text-indigo-700 shadow-2xs">
                                {nama} <span class="text-[10px] text-slate-400 font-sans">(Nama)</span>
                            </button>
                            <button type="button" @click="insertTag('{desa}')" class="px-2 py-1 bg-white border border-slate-200 hover:border-indigo-300 rounded-lg text-xs font-mono font-semibold text-indigo-700 shadow-2xs">
                                {desa} <span class="text-[10px] text-slate-400 font-sans">(Desa)</span>
                            </button>
                            <button type="button" @click="insertTag('{kode}')" class="px-2 py-1 bg-white border border-slate-200 hover:border-indigo-300 rounded-lg text-xs font-mono font-semibold text-indigo-700 shadow-2xs">
                                {kode} <span class="text-[10px] text-slate-400 font-sans">(ID)</span>
                            </button>
                            <button type="button" @click="insertTag('{paket}')" class="px-2 py-1 bg-white border border-slate-200 hover:border-indigo-300 rounded-lg text-xs font-mono font-semibold text-indigo-700 shadow-2xs">
                                {paket} <span class="text-[10px] text-slate-400 font-sans">(Paket)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Live WhatsApp Mockup Preview -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="eye" class="w-3.5 h-3.5 text-emerald-600"></i>
                            Pratinjau Live WhatsApp:
                        </label>
                        <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-[#efeae2]">
                            <!-- WhatsApp Header -->
                            <div class="bg-[#075e54] text-white px-4 py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-emerald-700 flex items-center justify-center font-bold text-xs text-white shrink-0">
                                        H
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold leading-tight truncate">Hokigo WiFi (Official)</div>
                                        <div class="text-[10px] text-emerald-200 leading-tight truncate">
                                            Penerima: <span x-text="sampleRecipient.name"></span> (<span x-text="sampleRecipient.village"></span>)
                                        </div>
                                    </div>
                                </div>
                                <div class="text-[10px] bg-emerald-800/80 px-2 py-0.5 rounded-full text-emerald-100 font-medium shrink-0">
                                    Simulasi WA
                                </div>
                            </div>

                            <!-- WhatsApp Message Bubble -->
                            <div class="p-3.5">
                                <div class="bg-white rounded-2xl rounded-tl-xs p-3.5 shadow-sm max-w-[95%] border border-slate-100 space-y-1.5">
                                    <div class="text-[13px] text-slate-800 whitespace-pre-wrap leading-relaxed font-sans" x-html="renderWhatsAppFormat(previewText)"></div>
                                    <div class="flex items-center justify-end gap-1 text-[10px] text-slate-400 font-mono">
                                        <span>{{ now()->format('H:i') }}</span>
                                        <span class="text-blue-500 font-bold">&#10003;&#10003;</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Execution Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                :disabled="isSubmitting || selectedCount === 0" 
                                class="w-full py-4 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.99] text-white font-extrabold text-base shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="!isSubmitting">
                                <div class="flex items-center gap-2.5">
                                    <i data-lucide="send" class="w-5 h-5"></i>
                                    <span>Kirim Broadcast (<span x-text="selectedCount"></span> Orang)</span>
                                </div>
                            </template>
                            <template x-if="isSubmitting">
                                <div class="flex items-center gap-2.5">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Mengirim Pesan WhatsApp...</span>
                                </div>
                            </template>
                        </button>
                        <p class="text-[11px] text-center text-slate-400 mt-2">
                            Pesan dikirim satu per satu via WhatsApp Gateway ke orang-orang yang Anda centang.
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </form>

    <!-- BOTTOM SECTION: Broadcast History Logs -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                    <i data-lucide="history" class="w-4 h-4"></i>
                </span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Riwayat Pengiriman Broadcast</h2>
                    <p class="text-xs text-slate-500">Catatan siaran pesan yang pernah dikirim oleh Owner atau Admin.</p>
                </div>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                Total {{ $logs->count() }} Riwayat
            </span>
        </div>

        @if($logs->isEmpty())
            <div class="text-center py-10 text-slate-400 space-y-2">
                <i data-lucide="message-square-off" class="w-10 h-10 mx-auto text-slate-300"></i>
                <div class="text-sm font-semibold text-slate-500">Belum ada riwayat pengiriman broadcast.</div>
                <p class="text-xs max-w-sm mx-auto text-slate-400">Siaran pesan yang Anda kirim akan otomatis tercatat di tabel ini lengkap dengan tanggal dan status pengirimannya.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-bold">
                            <th class="py-3 px-3">Waktu</th>
                            <th class="py-3 px-3">Judul & Sasaran Wilayah / Orang</th>
                            <th class="py-3 px-3">Cuplikan Pesan</th>
                            <th class="py-3 px-3 text-center">Status Terkirim</th>
                            <th class="py-3 px-3">Pengirim</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $log->created_at->format('d/m/Y') }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $log->created_at->format('H:i') }} WIB</div>
                                </td>
                                <td class="py-3 px-3 min-w-[200px]">
                                    <div class="font-extrabold text-slate-900">{{ $log->title ?: 'Broadcast Informasi' }}</div>
                                    <div class="text-[11px] text-indigo-700 font-semibold mt-0.5">
                                        📍 {{ $log->village_names }}
                                    </div>
                                </td>
                                <td class="py-3 px-3 max-w-[280px]">
                                    <div class="line-clamp-2 text-slate-600 font-mono text-[11px] bg-slate-50 p-2 rounded-xl border border-slate-100">
                                        {{ $log->message }}
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $log->failed_count > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $log->sent_count }}/{{ $log->total_recipients }} Terkirim
                                    </span>
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $log->creator?->name ?? 'Admin / Owner' }}</div>
                                    <div class="text-[10px] text-slate-400 capitalize">{{ $log->creator?->role ?? 'Staf' }}</div>
                                </td>
                                <td class="py-3 px-3 text-right whitespace-nowrap">
                                    <form action="{{ route('broadcast.destroy', $log) }}" method="POST" onsubmit="return confirm('Hapus catatan riwayat broadcast ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Riwayat">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function broadcastApp(config) {
    return {
        villages: config.villages || [],
        totalActiveCustomers: config.totalActiveCustomers || 0,
        totalAllCustomers: config.totalAllCustomers || 0,

        selectedCustomerIds: [],
        expandedVillages: {},
        searchQuery: '',
        statusFilter: 'active', // 'active' or 'all'
        title: 'Pemberitahuan Gangguan Jaringan (Trouble)',
        message: `*PEMBERITAHUAN GANGGUAN JARINGAN HOKIGO WIFI* ⚠️\n\nYth. Pelanggan Hokigo WiFi di *{desa}*,\n\nKami informasikan bahwa saat ini sedang terjadi gangguan jaringan (trouble) di wilayah Anda yang menyebabkan koneksi internet mengalami penurunan kecepatan atau terputus sementara.\n\nSaat ini tim teknisi kami sedang melakukan penanganan dan perbaikan di lokasi agar koneksi dapat segera pulih kembali.\n\nMohon maaf yang sebesar-besarnya atas ketidaknyamanan ini. Terima kasih atas pengertian dan kesabarannya. 🙏`,
        isSubmitting: false,

        init() {
            // Select all active customers of the first village and expand it by default
            if (this.villages.length > 0) {
                const firstVillage = this.villages[0];
                this.expandedVillages[firstVillage.id] = true;
                const activeCusts = (firstVillage.customers || []).filter(c => c.status === 'active');
                this.selectedCustomerIds = activeCusts.map(c => c.id);
            }
        },

        // Status Filter Toggle
        setStatusFilter(filter) {
            this.statusFilter = filter;
        },

        // Accordion Expand/Collapse
        isVillageExpanded(villageId) {
            return !!this.expandedVillages[villageId];
        },

        toggleExpandVillage(villageId) {
            this.expandedVillages[villageId] = !this.expandedVillages[villageId];
        },

        expandAll() {
            this.villages.forEach(v => {
                this.expandedVillages[v.id] = true;
            });
        },

        collapseAll() {
            this.expandedVillages = {};
        },

        // Filtered customers inside a village
        getFilteredCustomers(village) {
            const list = village.customers || [];
            return list.filter(c => {
                // Status check
                if (this.statusFilter === 'active' && c.status !== 'active') {
                    return false;
                }
                // Search query check
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    const vName = (village.name || '').toLowerCase();
                    const cName = (c.name || '').toLowerCase();
                    const cCode = (c.code || '').toLowerCase();
                    const cPhone = (c.phone || '').toLowerCase();
                    const cAddr = (c.address || '').toLowerCase();
                    return vName.includes(q) || cName.includes(q) || cCode.includes(q) || cPhone.includes(q) || cAddr.includes(q);
                }
                return true;
            });
        },

        // Filtered villages list (shown in UI)
        get filteredVillages() {
            if (!this.searchQuery) return this.villages;
            return this.villages.filter(v => {
                const q = this.searchQuery.toLowerCase();
                const vName = (v.name || '').toLowerCase();
                if (vName.includes(q)) return true;
                // If any customer inside matches, show the village and auto-expand it!
                const hasMatchingCust = (v.customers || []).some(c => {
                    const cName = (c.name || '').toLowerCase();
                    const cCode = (c.code || '').toLowerCase();
                    const cPhone = (c.phone || '').toLowerCase();
                    return cName.includes(q) || cCode.includes(q) || cPhone.includes(q);
                });
                if (hasMatchingCust) {
                    this.expandedVillages[v.id] = true;
                }
                return hasMatchingCust;
            });
        },

        // Customer selection
        isCustomerSelected(customerId) {
            return this.selectedCustomerIds.includes(customerId);
        },

        toggleCustomer(customerId) {
            if (this.selectedCustomerIds.includes(customerId)) {
                this.selectedCustomerIds = this.selectedCustomerIds.filter(id => id !== customerId);
            } else {
                this.selectedCustomerIds.push(customerId);
            }
        },

        // Village selection status ('all', 'partial', 'none')
        villageSelectionStatus(village) {
            const custs = this.getFilteredCustomers(village);
            if (custs.length === 0) return 'none';
            const selectedInV = custs.filter(c => this.selectedCustomerIds.includes(c.id)).length;
            if (selectedInV === custs.length) return 'all';
            if (selectedInV > 0) return 'partial';
            return 'none';
        },

        selectedInVillageCount(village) {
            const custs = this.getFilteredCustomers(village);
            return custs.filter(c => this.selectedCustomerIds.includes(c.id)).length;
        },

        // Toggle entire village
        toggleVillageAll(village) {
            const status = this.villageSelectionStatus(village);
            if (status === 'all') {
                this.deselectVillageAll(village);
            } else {
                this.selectVillageAll(village);
            }
        },

        selectVillageAll(village) {
            const custs = this.getFilteredCustomers(village);
            const idsToAdd = custs.map(c => c.id);
            const set = new Set([...this.selectedCustomerIds, ...idsToAdd]);
            this.selectedCustomerIds = Array.from(set);
            this.expandedVillages[village.id] = true;
        },

        deselectVillageAll(village) {
            const custs = this.getFilteredCustomers(village);
            const idsToRemove = new Set(custs.map(c => c.id));
            this.selectedCustomerIds = this.selectedCustomerIds.filter(id => !idsToRemove.has(id));
        },

        // Global Batch Actions
        selectAllCustomers() {
            const allIds = [];
            this.villages.forEach(v => {
                const custs = this.getFilteredCustomers(v);
                custs.forEach(c => allIds.push(c.id));
            });
            this.selectedCustomerIds = Array.from(new Set(allIds));
        },

        unselectAllCustomers() {
            this.selectedCustomerIds = [];
        },

        get totalFilteredCount() {
            let total = 0;
            this.villages.forEach(v => {
                total += this.getFilteredCustomers(v).length;
            });
            return total;
        },

        get selectedCount() {
            return this.selectedCustomerIds.length;
        },

        get selectedVillagesCount() {
            const villageIds = new Set();
            this.villages.forEach(v => {
                (v.customers || []).forEach(c => {
                    if (this.selectedCustomerIds.includes(c.id)) {
                        villageIds.add(v.id);
                    }
                });
            });
            return villageIds.size;
        },

        // Sample recipient for WhatsApp live preview
        get sampleRecipient() {
            if (this.selectedCustomerIds.length > 0) {
                const firstId = this.selectedCustomerIds[0];
                for (const v of this.villages) {
                    const found = (v.customers || []).find(c => c.id === firstId);
                    if (found) {
                        return {
                            name: found.name,
                            village: found.village_name || v.name,
                            code: found.code,
                            package: found.package_name
                        };
                    }
                }
            }
            return {
                name: 'Bpk. Budi Santoso',
                village: 'Desa Kalimantan',
                code: 'HKG-012',
                package: 'Hoki 1 (15 Mbps)'
            };
        },

        get previewText() {
            if (!this.message) return '';
            const rec = this.sampleRecipient;
            return this.message
                .replace(/{nama}/g, rec.name)
                .replace(/{desa}/g, rec.village)
                .replace(/{kode}/g, rec.code)
                .replace(/{paket}/g, rec.package);
        },

        setTemplate(type) {
            if (type === 'trouble') {
                this.title = 'Pemberitahuan Gangguan Jaringan (Trouble)';
                this.message = `*PEMBERITAHUAN GANGGUAN JARINGAN HOKIGO WIFI* ⚠️\n\nYth. Pelanggan Hokigo WiFi di *{desa}*,\n\nKami informasikan bahwa saat ini sedang terjadi gangguan jaringan (trouble) di wilayah Anda yang menyebabkan koneksi internet mengalami penurunan kecepatan atau terputus sementara.\n\nSaat ini tim teknisi kami sedang melakukan penanganan dan perbaikan di lokasi agar koneksi dapat segera pulih kembali.\n\nMohon maaf yang sebesar-besarnya atas ketidaknyamanan ini. Terima kasih atas pengertian dan kesabarannya. 🙏`;
            } else if (type === 'maintenance') {
                this.title = 'Pemberitahuan Pemeliharaan Jaringan (Maintenance)';
                this.message = `*PEMBERITAHUAN PEMELIHARAAN JARINGAN HOKIGO WIFI* 🔧\n\nYth. Pelanggan Hokigo WiFi di *{desa}*,\n\nDalam rangka meningkatkan kualitas dan kestabilan layanan internet, kami akan melakukan pemeliharaan jaringan (maintenance) di wilayah Anda pada:\n\n📅 Hari/Tgl : [Hari, Tanggal]\n⏰ Waktu    : [Jam Mulai s/d Selesai]\n\nSelama pemeliharaan berlangsung, koneksi internet mungkin akan mengalami gangguan sementara. Kami berupaya menyelesaikan secepat mungkin.\n\nTerima kasih atas kerja samanya. 🙏`;
            } else if (type === 'normal') {
                this.title = 'Informasi Jaringan Telah Normal Kembali';
                this.message = `*INFORMASI JARINGAN TELAH NORMAL KEMBALI* ✅\n\nYth. Pelanggan Hokigo WiFi di *{desa}*,\n\nKami informasikan bahwa proses perbaikan jaringan di wilayah Anda telah selesai dilakukan, dan koneksi internet saat ini sudah kembali normal.\n\nJika router WiFi di rumah Anda belum terhubung, silakan restart router (matikan saklar 10 detik lalu nyalakan kembali).\n\nTerima kasih atas kesabaran dan kepercayaannya bersama Hokigo WiFi. 🙏`;
            } else if (type === 'announcement') {
                this.title = 'Pengumuman Resmi Hokigo WiFi';
                this.message = `*PENGUMUMAN RESMI HOKIGO WIFI* 📢\n\nYth. Pelanggan Hokigo WiFi di *{desa}*,\n\n[Tuliskan isi pengumuman atau informasi Anda di sini...]\n\nTerima kasih telah setia menggunakan layanan Hokigo WiFi. 🙏`;
            }
        },

        insertTag(tag) {
            this.message = this.message + ' ' + tag + ' ';
        },

        renderWhatsAppFormat(text) {
            if (!text) return '';
            let safe = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;");
            
            safe = safe.replace(/\*([^\*]+)\*/g, '<strong class="font-extrabold text-slate-900">$1</strong>');
            safe = safe.replace(/_([^_]+)_/g, '<em class="italic">$1</em>');
            safe = safe.replace(/~([^~]+)~/g, '<del class="line-through">$1</del>');
            safe = safe.replace(/`([^`]+)`/g, '<code class="bg-slate-100 px-1 py-0.5 rounded text-xs font-mono text-emerald-800">$1</code>');

            return safe;
        },

        confirmSend(e) {
            if (this.selectedCount === 0) {
                e.preventDefault();
                alert('Pilih minimal satu orang pelanggan penerima broadcast!');
                return;
            }

            const confirmed = confirm('Konfirmasi Kirim Broadcast WhatsApp:\n\n' +
                '• Jumlah Penerima: ' + this.selectedCount + ' orang (' + this.selectedVillagesCount + ' desa)\n' +
                '• Judul: ' + (this.title || 'Broadcast Informasi') + '\n\n' +
                'Apakah Anda yakin ingin memulai pengiriman WhatsApp ke ' + this.selectedCount + ' pelanggan terpilih?');

            if (!confirmed) {
                e.preventDefault();
            } else {
                this.isSubmitting = true;
            }
        }
    };
}
</script>
@endpush
@endsection
