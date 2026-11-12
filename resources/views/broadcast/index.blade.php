@extends('layouts.app')

@section('title', 'Broadcast WhatsApp Desa')

@section('content')
@php
    $villageIdsList = $villages->pluck('id')->values()->toJson();
    $villageActiveMap = $villages->pluck('active_customers_count', 'id')->toJson();
    $villageTotalMap = $villages->pluck('customers_count', 'id')->toJson();
    $villageNameMap = $villages->pluck('name', 'id')->toJson();
@endphp

<div class="space-y-6" x-data="broadcastApp({
    allVillageIds: {{ $villageIdsList }},
    villageActiveMap: {{ $villageActiveMap }},
    villageTotalMap: {{ $villageTotalMap }},
    villageNameMap: {{ $villageNameMap }},
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
                    Owner & Admin
                </span>
            </div>
            <p class="text-slate-500 text-sm max-w-2xl leading-relaxed">
                Kirim informasi gangguan jaringan (<span class="font-semibold text-rose-600">trouble</span>), jadwal pemeliharaan (<span class="font-semibold text-amber-600">maintenance</span>), atau pengumuman penting langsung ke WhatsApp pelanggan di satu desa tertentu, beberapa desa, maupun ke seluruh desa sekaligus.
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
            
            <!-- LEFT COLUMN: Target Selection (Desa / Wilayah & Filter Pelanggan) -->
            <div class="lg:col-span-6 space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">1</span>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900">Pilih Sasaran Wilayah / Desa</h2>
                                <p class="text-xs text-slate-500">Tentukan desa mana yang akan menerima pesan broadcast ini.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Target Type Selector (Selected vs All) -->
                    <div class="grid grid-cols-2 gap-3 p-1.5 bg-slate-100/80 rounded-2xl">
                        <button type="button" 
                                @click="targetType = 'selected'"
                                :class="targetType === 'selected' ? 'bg-white text-indigo-700 shadow-sm font-bold border border-indigo-200/60' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                class="py-2.5 px-3 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            <span>Pilih Desa Tertentu</span>
                        </button>
                        <button type="button" 
                                @click="targetType = 'all'"
                                :class="targetType === 'all' ? 'bg-white text-indigo-700 shadow-sm font-bold border border-indigo-200/60' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                class="py-2.5 px-3 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                            <span>Semua Desa (Serentak)</span>
                        </button>
                    </div>
                    <input type="hidden" name="target_type" :value="targetType">

                    <!-- Customer Status Filter -->
                    <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <span class="text-xs font-semibold text-slate-700 flex items-center gap-1.5">
                            <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                            Filter Status Pelanggan:
                        </span>
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer text-xs font-semibold px-2.5 py-1.5 rounded-xl border transition-all"
                                   :class="customerStatus === 'active' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-white text-slate-600 border-slate-200'">
                                <input type="radio" name="customer_status" value="active" x-model="customerStatus" class="sr-only">
                                Hanya Aktif
                            </label>
                            <label class="cursor-pointer text-xs font-semibold px-2.5 py-1.5 rounded-xl border transition-all"
                                   :class="customerStatus === 'all' ? 'bg-indigo-100 text-indigo-800 border-indigo-300' : 'bg-white text-slate-600 border-slate-200'">
                                <input type="radio" name="customer_status" value="all" x-model="customerStatus" class="sr-only">
                                Semua (Termasuk Isolir)
                            </label>
                        </div>
                    </div>

                    <!-- Village Grid Selection (Shown when 'selected') -->
                    <div x-show="targetType === 'selected'" x-cloak class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="relative flex-1">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                <input type="text" 
                                       x-model="searchQuery" 
                                       placeholder="Cari desa (misal: Kalimantan, Simpur)..." 
                                       class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            </div>
                            <button type="button" 
                                    @click="toggleAllVillages()" 
                                    class="px-3 py-1.5 text-xs font-bold rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 transition">
                                <span x-text="selectedVillages.length === allVillageIds.length ? 'Batal Pilih Semua' : 'Pilih Semua (' + allVillageIds.length + ' Desa)'"></span>
                            </button>
                        </div>

                        <!-- Villages Checklist Grid -->
                        <div class="max-h-[380px] overflow-y-auto pr-1 space-y-2 border border-slate-200/70 rounded-2xl p-2.5 bg-slate-50/40">
                            @foreach($villages as $village)
                                <div x-show="matchesSearch('{{ strtolower($village->name) }}')"
                                     @click="toggleVillage({{ $village->id }})"
                                     :class="isVillageSelected({{ $village->id }}) ? 'bg-indigo-50/90 border-indigo-400 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300'"
                                     class="p-3 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3 select-none">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <input type="checkbox" 
                                               name="village_ids[]" 
                                               value="{{ $village->id }}" 
                                               :checked="isVillageSelected({{ $village->id }})"
                                               @click.stop="toggleVillage({{ $village->id }})"
                                               class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                        <div class="min-w-0">
                                            <div class="text-sm font-extrabold text-slate-900 truncate flex items-center gap-1.5">
                                                <span>{{ $village->name }}</span>
                                                @if($village->code)
                                                    <span class="text-[10px] font-mono text-slate-400 font-normal">({{ $village->code }})</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-500">
                                                <span class="font-bold text-slate-700">{{ $village->active_customers_count }}</span> aktif &bull; {{ $village->customers_count }} total
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-xs px-2.5 py-1 rounded-xl font-bold shrink-0"
                                          :class="isVillageSelected({{ $village->id }}) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'">
                                        <span x-text="customerStatus === 'active' ? '{{ $village->active_customers_count }}' : '{{ $village->customers_count }}'"></span>
                                        <span class="font-normal text-[10px]">penerima</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- All Villages Banner (Shown when 'all') -->
                    <div x-show="targetType === 'all'" x-cloak class="p-5 rounded-2xl bg-indigo-50/70 border border-indigo-200/80 space-y-2">
                        <div class="flex items-center gap-2 text-indigo-900 font-extrabold text-sm">
                            <i data-lucide="globe-2" class="w-5 h-5 text-indigo-600"></i>
                            <span>Mode Broadcast Serentak (Semua Desa)</span>
                        </div>
                        <p class="text-xs text-indigo-800 leading-relaxed">
                            Pesan akan dikirimkan serentak ke <strong>seluruh {{ $stats['total_villages'] }} wilayah desa</strong> tanpa terkecuali. Pastikan isi pengumuman memang relevan untuk seluruh pelanggan Hokigo WiFi.
                        </p>
                    </div>

                    <!-- Dynamic Estimation Counter -->
                    <div class="p-4 rounded-2xl bg-slate-900 text-white flex items-center justify-between gap-3 shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-indigo-500/30 text-indigo-300 flex items-center justify-center shrink-0">
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">Estimasi Sasaran Penerima:</div>
                                <div class="text-sm font-extrabold text-white flex items-center gap-1.5">
                                    <span x-text="targetType === 'all' ? 'Semua Desa ({{ $stats['total_villages'] }} Wilayah)' : selectedVillages.length + ' Desa Terpilih'"></span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black text-indigo-400" x-text="estimatedCount"></div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Pelanggan</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT COLUMN: Message Composer & Live WhatsApp Preview -->
            <div class="lg:col-span-6 space-y-6">
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
                                    class="px-2.5 py-2 rounded-xl text-xs font-bold border border-rose-200 bg-rose-50/70 hover:bg-rose-100 text-rose-800 transition flex items-center justify-center gap-1.5">
                                <span>⚠️</span> Trouble
                            </button>
                            <button type="button" 
                                    @click="setTemplate('maintenance')"
                                    class="px-2.5 py-2 rounded-xl text-xs font-bold border border-amber-200 bg-amber-50/70 hover:bg-amber-100 text-amber-800 transition flex items-center justify-center gap-1.5">
                                <span>🔧</span> Perawatan
                            </button>
                            <button type="button" 
                                    @click="setTemplate('normal')"
                                    class="px-2.5 py-2 rounded-xl text-xs font-bold border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100 text-emerald-800 transition flex items-center justify-center gap-1.5">
                                <span>✅</span> Normal
                            </button>
                            <button type="button" 
                                    @click="setTemplate('announcement')"
                                    class="px-2.5 py-2 rounded-xl text-xs font-bold border border-blue-200 bg-blue-50/70 hover:bg-blue-100 text-blue-800 transition flex items-center justify-center gap-1.5">
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
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Variabel Otomatis (Klik untuk menyisipkan ke teks):</div>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="insertTag('{nama}')" class="px-2 py-1 bg-white border border-slate-200 hover:border-indigo-300 rounded-lg text-xs font-mono font-semibold text-indigo-700 shadow-2xs">
                                {nama} <span class="text-[10px] text-slate-400 font-sans">(Nama Pelanggan)</span>
                            </button>
                            <button type="button" @click="insertTag('{desa}')" class="px-2 py-1 bg-white border border-slate-200 hover:border-indigo-300 rounded-lg text-xs font-mono font-semibold text-indigo-700 shadow-2xs">
                                {desa} <span class="text-[10px] text-slate-400 font-sans">(Nama Desa)</span>
                            </button>
                            <button type="button" @click="insertTag('{kode}')" class="px-2 py-1 bg-white border border-slate-200 hover:border-indigo-300 rounded-lg text-xs font-mono font-semibold text-indigo-700 shadow-2xs">
                                {kode} <span class="text-[10px] text-slate-400 font-sans">(ID Pelanggan)</span>
                            </button>
                            <button type="button" @click="insertTag('{paket}')" class="px-2 py-1 bg-white border border-slate-200 hover:border-indigo-300 rounded-lg text-xs font-mono font-semibold text-indigo-700 shadow-2xs">
                                {paket} <span class="text-[10px] text-slate-400 font-sans">(Paket WiFi)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Live WhatsApp Mockup Preview -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="eye" class="w-3.5 h-3.5 text-emerald-600"></i>
                            Pratinjau Tampilan Pesan WhatsApp (Live Preview):
                        </label>
                        <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-[#efeae2]">
                            <!-- WhatsApp Chat Header -->
                            <div class="bg-[#075e54] text-white px-4 py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-emerald-700 flex items-center justify-center font-bold text-xs text-white">
                                        H
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold leading-tight">Hokigo WiFi (Official)</div>
                                        <div class="text-[10px] text-emerald-200 leading-tight">Siaran ke: <span x-text="sampleVillageName"></span></div>
                                    </div>
                                </div>
                                <div class="text-[10px] bg-emerald-800/80 px-2 py-0.5 rounded-full text-emerald-100 font-medium">
                                    Simulasi WA
                                </div>
                            </div>

                            <!-- WhatsApp Message Bubble -->
                            <div class="p-3.5 sm:p-4">
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
                                :disabled="isSubmitting || estimatedCount === 0" 
                                class="w-full py-4 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.99] text-white font-extrabold text-base shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="!isSubmitting">
                                <div class="flex items-center gap-2.5">
                                    <i data-lucide="send" class="w-5 h-5"></i>
                                    <span>Kirim Broadcast Sekarang (<span x-text="estimatedCount"></span> Pelanggan)</span>
                                </div>
                            </template>
                            <template x-if="isSubmitting">
                                <div class="flex items-center gap-2.5">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Memproses Pengiriman WhatsApp...</span>
                                </div>
                            </template>
                        </button>
                        <p class="text-[11px] text-center text-slate-400 mt-2">
                            Pesan dikirim satu per satu secara otomatis via WhatsApp Gateway dengan jeda waktu aman.
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
                            <th class="py-3 px-3">Judul & Sasaran Wilayah</th>
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
        allVillageIds: config.allVillageIds || [],
        villageActiveMap: config.villageActiveMap || {},
        villageTotalMap: config.villageTotalMap || {},
        villageNameMap: config.villageNameMap || {},
        totalActiveCustomers: config.totalActiveCustomers || 0,
        totalAllCustomers: config.totalAllCustomers || 0,

        targetType: 'selected', // 'selected' or 'all'
        selectedVillages: [],
        searchQuery: '',
        customerStatus: 'active', // 'active' or 'all'
        title: 'Pemberitahuan Gangguan Jaringan (Trouble)',
        message: `*PEMBERITAHUAN GANGGUAN JARINGAN HOKIGO WIFI* ⚠️\n\nYth. Pelanggan Hokigo WiFi di *{desa}*,\n\nKami informasikan bahwa saat ini sedang terjadi gangguan jaringan (trouble) di wilayah Anda yang menyebabkan koneksi internet mengalami penurunan kecepatan atau terputus sementara.\n\nSaat ini tim teknisi kami sedang melakukan penanganan dan perbaikan di lokasi agar koneksi dapat segera pulih kembali.\n\nMohon maaf yang sebesar-besarnya atas ketidaknyamanan ini. Terima kasih atas pengertian dan kesabarannya. 🙏`,
        isSubmitting: false,

        init() {
            // Select first village by default if available
            if (this.allVillageIds.length > 0) {
                this.selectedVillages = [this.allVillageIds[0]];
            }
        },

        isVillageSelected(id) {
            return this.selectedVillages.includes(id);
        },

        toggleVillage(id) {
            if (this.selectedVillages.includes(id)) {
                this.selectedVillages = this.selectedVillages.filter(v => v !== id);
            } else {
                this.selectedVillages.push(id);
            }
        },

        toggleAllVillages() {
            if (this.selectedVillages.length === this.allVillageIds.length) {
                this.selectedVillages = [];
            } else {
                this.selectedVillages = [...this.allVillageIds];
            }
        },

        matchesSearch(villageNameLower) {
            if (!this.searchQuery) return true;
            return villageNameLower.includes(this.searchQuery.toLowerCase());
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

        get estimatedCount() {
            if (this.targetType === 'all') {
                return this.customerStatus === 'active' ? this.totalActiveCustomers : this.totalAllCustomers;
            }
            let total = 0;
            this.selectedVillages.forEach(id => {
                total += (this.customerStatus === 'active' ? (this.villageActiveMap[id] || 0) : (this.villageTotalMap[id] || 0));
            });
            return total;
        },

        get sampleVillageName() {
            if (this.targetType === 'all') return 'Semua Desa';
            if (this.selectedVillages.length === 0) return 'Belum dipilih';
            const firstId = this.selectedVillages[0];
            const name = this.villageNameMap[firstId] || 'Desa';
            return this.selectedVillages.length > 1 ? name + ' +' + (this.selectedVillages.length - 1) + ' desa' : name;
        },

        get previewText() {
            if (!this.message) return '';
            let sampleVillage = 'Desa Kalimantan';
            if (this.selectedVillages.length > 0) {
                const firstId = this.selectedVillages[0];
                sampleVillage = this.villageNameMap[firstId] || 'Desa Kalimantan';
            }
            return this.message
                .replace(/{nama}/g, 'Bpk. Budi Santoso')
                .replace(/{desa}/g, sampleVillage)
                .replace(/{kode}/g, 'HKG-012')
                .replace(/{paket}/g, 'Hoki 1 (15 Mbps)');
        },

        renderWhatsAppFormat(text) {
            if (!text) return '';
            // Escape HTML
            let safe = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;");
            
            // Format *bold*
            safe = safe.replace(/\*([^\*]+)\*/g, '<strong class="font-extrabold text-slate-900">$1</strong>');
            // Format _italic_
            safe = safe.replace(/_([^_]+)_/g, '<em class="italic">$1</em>');
            // Format ~strikethrough~
            safe = safe.replace(/~([^~]+)~/g, '<del class="line-through">$1</del>');
            // Format `monospace`
            safe = safe.replace(/`([^`]+)`/g, '<code class="bg-slate-100 px-1 py-0.5 rounded text-xs font-mono text-emerald-800">$1</code>');

            return safe;
        },

        confirmSend(e) {
            if (this.estimatedCount === 0) {
                e.preventDefault();
                alert('Pilih minimal satu desa yang memiliki pelanggan aktif!');
                return;
            }

            const targetInfo = this.targetType === 'all' 
                ? 'SEMUA DESA (' + this.totalActiveCustomers + ' pelanggan)' 
                : this.selectedVillages.length + ' desa terpilih (' + this.estimatedCount + ' pelanggan)';

            const confirmed = confirm('Konfirmasi Kirim Broadcast WhatsApp:\n\n' +
                '• Target: ' + targetInfo + '\n' +
                '• Judul: ' + (this.title || 'Broadcast Informasi') + '\n\n' +
                'Apakah Anda yakin ingin memulai pengiriman sekarang?');

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
