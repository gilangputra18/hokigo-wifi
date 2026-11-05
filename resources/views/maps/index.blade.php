@extends('layouts.app')

@section('title', 'Peta Jaringan, Server Starlink, ODP & Jalur Kabel')
@section('header_title', 'Peta Jaringan & Infrastruktur WiFi')

@section('content')
<style>
    /* Leaflet performance & mobile touch optimizations */
    .custom-house-marker {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        will-change: transform;
    }
    .leaflet-div-icon {
        background: transparent !important;
        border: none !important;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 16px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.15) !important;
        padding: 4px !important;
    }
    .leaflet-popup-content {
        margin: 6px 8px !important;
        line-height: 1.4 !important;
    }
    .leaflet-container {
        font-family: inherit !important;
        touch-action: pan-x pan-y !important;
    }
    .leaflet-tile {
        will-change: transform, opacity;
    }
</style>

<div class="space-y-4" x-data="mapApp()">

    <!-- Top Stats & Controls Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3">
        
        <!-- Row 1: Summary Counts -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-100 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <span class="text-slate-600 font-medium">Pelanggan:</span>
                    <span class="font-extrabold text-blue-900" x-text="pinnedCount + '/' + allCustomersData.length"></span>
                </div>

                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-100 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-slate-600 font-medium">Server & ODP:</span>
                    <span class="font-extrabold text-amber-900" x-text="networkNodesData.length"></span>
                </div>

                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-100 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <span class="text-slate-600 font-medium">Jalur Kabel FO:</span>
                    <span class="font-extrabold text-emerald-900" x-text="cableRoutesData.length + ' rute'"></span>
                </div>
            </div>

            <!-- Layer View Toggles -->
            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl text-[11px] font-bold">
                <button type="button" @click="showCustomers = !showCustomers; renderMarkers()"
                        :class="showCustomers ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-400 hover:text-slate-600'"
                        class="px-2.5 py-1 rounded-lg transition">
                    🏠 Rumah
                </button>
                <button type="button" @click="showNodes = !showNodes; renderNodes()"
                        :class="showNodes ? 'bg-white text-amber-700 shadow-xs' : 'text-slate-400 hover:text-slate-600'"
                        class="px-2.5 py-1 rounded-lg transition">
                    🛰️ ODP & Server
                </button>
                <button type="button" @click="showCables = !showCables; renderCables()"
                        :class="showCables ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-400 hover:text-slate-600'"
                        class="px-2.5 py-1 rounded-lg transition">
                    〰️ Jalur FO
                </button>
            </div>
        </div>

        <!-- Row 2: Search, Filters & Action Buttons -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
            
            <div class="flex flex-wrap items-center gap-2 flex-1 min-w-[280px]">
                <!-- Filter Desa (Pilih desa langsung terbang ke lokasinya) -->
                <select x-model="selectedVillage" @change="onVillageSelected()" 
                        class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <option value="">🗺️ Semua Wilayah / Desa</option>
                    @foreach($villages as $v)
                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->system_type === 'voucher' ? 'Voucher' : ($v->system_type === 'hybrid' ? 'Hybrid' : 'PPPoE') }})</option>
                    @endforeach
                </select>

                <!-- Interactive Search Bar with Instant Fly-To Dropdown -->
                <div class="relative flex-1 max-w-sm" @click.away="searchDropdownOpen = false">
                    <div class="relative">
                        <input type="text" x-model="searchQuery" 
                               @input="onSearchInput()" 
                               @focus="searchDropdownOpen = true"
                               @keydown.enter.prevent="goToFirstSearchResult()"
                               placeholder="Cari nama tempat, desa, pelanggan, ODP..." 
                               class="w-full pl-8 pr-8 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue bg-white shadow-xs">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
                        
                        <!-- Tombol Clear [X] jika ada teks -->
                        <button type="button" x-show="searchQuery.length > 0" @click="clearSearch()"
                                class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 p-0.5 rounded-full hover:bg-slate-100 transition">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Floating Search Autocomplete Dropdown (Langsung Ke Arah Lokasi Tersebut) -->
                    <div x-show="searchDropdownOpen && searchQuery.trim().length > 0" x-cloak
                         class="absolute top-full left-0 right-0 mt-1.5 z-[1050] bg-white/98 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200 overflow-hidden divide-y divide-slate-100 max-h-80 overflow-y-auto animate-in fade-in slide-in-from-top-2 duration-150">
                        
                        <!-- 1. Wilayah / Desa -->
                        <template x-if="matchedVillages.length > 0">
                            <div>
                                <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                                    Wilayah / Desa
                                </div>
                                <template x-for="v in matchedVillages" :key="'mv_' + v.id">
                                    <button type="button" @click="goToVillage(v)"
                                            class="w-full text-left p-2.5 hover:bg-blue-50/80 transition flex items-center justify-between gap-2 group">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="w-7 h-7 rounded-lg bg-blue-100 text-brand-blue flex items-center justify-center shrink-0 text-sm">🏛️</span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-900 group-hover:text-brand-blue truncate" x-text="v.name"></p>
                                                <p class="text-[10px] text-slate-500 truncate" x-text="'Wilayah Desa • ' + (v.system_type === 'voucher' ? 'Voucher Hotspot' : 'PPPoE / FTTH')"></p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold text-brand-blue bg-blue-50 px-2 py-0.5 rounded-md shrink-0 flex items-center gap-1">
                                            <span>Terbang</span> <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        <!-- 2. Rumah Pelanggan -->
                        <template x-if="matchedCustomers.length > 0">
                            <div>
                                <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                                    Rumah Pelanggan
                                </div>
                                <template x-for="c in matchedCustomers" :key="'mc_' + c.id">
                                    <button type="button" @click="goToCustomer(c)"
                                            class="w-full text-left p-2.5 hover:bg-emerald-50/80 transition flex items-center justify-between gap-2 group">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 text-xs font-bold">🏠</span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-900 group-hover:text-emerald-800 truncate" x-text="c.name + ' (' + c.customer_code + ')'"></p>
                                                <p class="text-[10px] text-slate-500 truncate" x-text="(c.village ? c.village.name : 'Tanpa Desa') + (c.house_number ? ' • ' + c.house_number : '') + (c.pppoe_user ? ' • User: ' + c.pppoe_user : '')"></p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md shrink-0 flex items-center gap-1">
                                            <span>Ke Atap</span> <i data-lucide="navigation" class="w-3 h-3"></i>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        <!-- 3. Server & ODP -->
                        <template x-if="matchedNodes.length > 0">
                            <div>
                                <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                                    Server & ODP
                                </div>
                                <template x-for="node in matchedNodes" :key="'mn_' + node.id">
                                    <button type="button" @click="goToNode(node)"
                                            class="w-full text-left p-2.5 hover:bg-amber-50/80 transition flex items-center justify-between gap-2 group">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 text-sm"
                                                  x-text="node.type === 'server' || node.type === 'starlink' ? '🛰️' : '🔲'"></span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-900 group-hover:text-amber-800 truncate" x-text="node.name"></p>
                                                <p class="text-[10px] text-slate-500 truncate" x-text="(node.type === 'server' ? 'Server Pusat' : (node.type === 'starlink' ? 'Dish Starlink' : 'Kotak ODP')) + (node.capacity ? ' • ' + node.capacity : '')"></p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md shrink-0 flex items-center gap-1">
                                            <span>Ke Tiang</span> <i data-lucide="navigation" class="w-3 h-3"></i>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        <!-- 4. Tombol Cari Tempat Online (Nominatim OpenStreetMap) -->
                        <div class="p-2 bg-slate-50 text-[11px]">
                            <button type="button" @click="triggerExternalSearch()" :disabled="searchingExternal"
                                    class="w-full text-left p-2 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 transition flex items-center justify-between gap-2 text-slate-700 font-semibold shadow-xs">
                                <div class="flex items-center gap-2 truncate">
                                    <i data-lucide="globe" class="w-3.5 h-3.5 text-blue-600 shrink-0"></i>
                                    <span class="truncate">Cari tempat &quot;<span class="font-bold text-slate-900" x-text="searchQuery"></span>&quot; di peta satelit</span>
                                </div>
                                <span class="text-[10px] text-blue-600 font-bold shrink-0" x-text="searchingExternal ? 'Mencari...' : 'Cari 🔎'"></span>
                            </button>
                        </div>

                        <!-- Hasil Pencarian Tempat Online -->
                        <template x-for="p in externalResults" :key="'ext_' + p.place_id">
                            <button type="button" @click="goToExternalPlace(p)"
                                    class="w-full text-left p-2.5 hover:bg-sky-50 transition flex items-center justify-between gap-2 group">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-800 flex items-center justify-center shrink-0 text-sm">📍</span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 group-hover:text-sky-700 truncate" x-text="p.display_name.split(',')[0]"></p>
                                        <p class="text-[10px] text-slate-500 truncate" x-text="p.display_name"></p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md shrink-0">
                                    Terbang ✈️
                                </span>
                            </button>
                        </template>

                        <!-- Jika Tidak Ada Hasil -->
                        <div x-show="matchedCustomers.length === 0 && matchedVillages.length === 0 && matchedNodes.length === 0 && externalResults.length === 0 && !searchingExternal" 
                             class="p-4 text-center text-xs text-slate-400">
                            Tekan Enter atau klik <strong>Cari di peta satelit</strong> di atas.
                        </div>

                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                
                <!-- Tarik Jalur Kabel Interaktif -->
                <button type="button" @click="startCableDrawing()" 
                        :class="drawingCable ? 'bg-emerald-700 ring-2 ring-emerald-300' : 'bg-emerald-600 hover:bg-emerald-700'"
                        class="px-3.5 py-2 rounded-xl text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-emerald-600/20 transition"
                        title="Tarik garis jalur kabel optik di peta satelit">
                    <i data-lucide="git-commit" class="w-4 h-4"></i>
                    <span>+ Tarik Kabel</span>
                </button>

                <!-- Button Titik Manual (Pelanggan / Server / ODP) -->
                <button type="button" @click="openManualPinModal()" 
                        class="px-3.5 py-2 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-blue-600/20 transition"
                        title="Titik rumah pelanggan, ODP, atau server di peta">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>+ Titik Baru</span>
                </button>

                <!-- GPS Device Button (For Field Technicians) -->
                <button type="button" @click="locateMe()" 
                        class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5 transition"
                        title="Arahkan peta ke posisi GPS saya sekarang">
                    <i data-lucide="crosshair" class="w-4 h-4 text-emerald-600"></i>
                    <span class="hidden sm:inline">GPS Saya</span>
                </button>

                @if(auth()->user()->isAdmin())
                    <!-- Reset Data Peta Button -->
                    <button type="button" @click="resetMapModalOpen = true" 
                            class="p-2 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 border border-slate-200 text-xs transition"
                            title="Reset data koordinat peta">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </button>
                @endif
            </div>
        </div>

        <!-- Smart Search Suggestion Chips (When typing village name like "Simpur") -->
        <template x-if="suggestedVillages.length > 0">
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                <span class="text-[11px] font-bold text-slate-400">Wilayah Ditemukan:</span>
                <template x-for="v in suggestedVillages" :key="'sug_v_' + v.id">
                    <button type="button" @click="flyToVillage(v.id)" 
                            class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-brand-blue font-bold text-xs flex items-center gap-1.5 border border-blue-200 transition">
                        <span>🏛️</span>
                        <span x-text="'Terbang ke ' + v.name"></span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </button>
                </template>
            </div>
        </template>
    </div>

    <!-- Main Map Workspace Container -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

        <!-- Map View (Takes 3 columns on desktop, full on mobile) -->
        <div class="lg:col-span-3 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden relative">
            
            <!-- Map Layer Switcher Badge -->
            <div class="absolute top-3 left-3 z-[1000] flex items-center gap-1.5 bg-white/95 backdrop-blur-md p-1 rounded-xl shadow-md border border-slate-200 text-xs">
                <button type="button" @click="setLayer('google')" 
                        :class="currentLayer === 'google' ? 'bg-brand-blue text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                        class="px-2.5 py-1.5 rounded-lg text-[11px] transition flex items-center gap-1">
                    <span>🛰️</span> <span class="hidden sm:inline">Google Satelit</span><span class="sm:hidden">Google</span>
                </button>
                <button type="button" @click="setLayer('esri')" 
                        :class="currentLayer === 'esri' ? 'bg-brand-blue text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                        class="px-2.5 py-1.5 rounded-lg text-[11px] transition flex items-center gap-1">
                    <span>🛰️</span> <span class="hidden sm:inline">Esri Satelit</span><span class="sm:hidden">Esri</span>
                </button>
                <button type="button" @click="setLayer('street')" 
                        :class="currentLayer === 'street' ? 'bg-brand-blue text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                        class="px-2.5 py-1.5 rounded-lg text-[11px] transition flex items-center gap-1">
                    <span>🗺️</span> <span>Jalan</span>
                </button>
            </div>

            <!-- Floating Card: Mode Tarik Jalur Kabel FO -->
            <div x-show="drawingCable" x-cloak
                 class="absolute bottom-4 left-3 right-3 sm:left-auto sm:right-4 sm:max-w-md z-[1000] bg-white/95 backdrop-blur-md p-4 rounded-2xl sm:rounded-3xl shadow-2xl border-2 border-emerald-500 space-y-3 animate-in fade-in slide-in-from-bottom-4 duration-200">
                <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-2">
                    <div>
                        <div class="flex items-center gap-1.5 text-xs font-extrabold text-emerald-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Mode Penarikan Jalur Kabel FO Aktif</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Klik titik demi titik di peta mengikuti jalan atau tiang dropcore.
                        </p>
                    </div>
                    <button type="button" @click="cancelCableDrawing()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200/80 flex items-center justify-between text-xs font-bold text-emerald-900">
                    <div>Titik Tiang: <span class="text-emerald-700" x-text="cablePoints.length"></span></div>
                    <div>Estimasi Panjang: <span class="text-emerald-700 font-mono" x-text="newCableLengthMeters + ' m'"></span></div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="openSaveCableModal()" :disabled="cablePoints.length < 2"
                            class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-md shadow-emerald-600/30 transition flex items-center justify-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>✓ SIMPAN JALUR KABEL INI</span>
                    </button>
                    <button type="button" @click="cancelCableDrawing()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                </div>
            </div>

            <!-- Floating Card: Mode Penitikan Rumah Pelanggan -->
            <div x-show="pinningCustomer !== null" x-cloak
                 class="absolute bottom-4 left-3 right-3 sm:left-auto sm:right-4 sm:max-w-md z-[1000] bg-white/95 backdrop-blur-md p-4 rounded-2xl sm:rounded-3xl shadow-2xl border-2 border-brand-blue space-y-3 animate-in fade-in slide-in-from-bottom-4 duration-200">
                
                <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-2.5">
                    <div>
                        <div class="flex items-center gap-1.5 text-xs font-extrabold text-brand-blue">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-blue animate-ping"></span>
                            <span>Mode Penitikan Rumah Aktif</span>
                        </div>
                        <h4 class="text-sm font-extrabold text-slate-900 mt-0.5" x-text="pinningCustomer?.name"></h4>
                        <p class="text-[11px] text-slate-500 font-medium">
                            <span x-text="pinningCustomer?.village ? pinningCustomer.village.name : 'Tanpa Desa'"></span> &bull; 
                            <span x-text="pinningCustomer?.customer_code"></span>
                            <template x-if="pinningCustomer?.house_number">
                                <span class="font-bold text-amber-700" x-text="' • ' + pinningCustomer.house_number"></span>
                            </template>
                        </p>
                    </div>
                    <button type="button" @click="cancelPinning()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition" title="Batal">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- State 1: Before clicking the map -->
                <template x-if="pendingCoordinate === null">
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/80 text-amber-900 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <i data-lucide="mouse-pointer-click" class="w-4 h-4 text-amber-600 shrink-0"></i>
                            <span>Klik di Peta Satelit</span>
                        </div>
                        <p class="text-[11px] leading-relaxed text-amber-800">
                            Arahkan dan <strong>klik tepat di atap rumah pelanggan</strong> pada peta satelit untuk menaruh pin titik koordinat.
                        </p>
                    </div>
                </template>

                <!-- State 2: After clicking the map, pin is placed -->
                <template x-if="pendingCoordinate !== null">
                    <div class="space-y-2.5">
                        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-950 text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1 text-emerald-800">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                <span>Titik Koordinat Terpilih:</span>
                            </div>
                            <p class="font-mono text-[11px] text-emerald-900">
                                Lat: <strong x-text="pendingCoordinate.lat.toFixed(6)"></strong>, 
                                Lng: <strong x-text="pendingCoordinate.lng.toFixed(6)"></strong>
                            </p>
                            <p class="text-[10px] text-emerald-700 font-medium">
                                *Pin di peta bisa digeser (drag) lagi jika posisinya ingin disesuaikan lebih pas.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="saveLocation()" :disabled="saving"
                                    class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/30 transition flex items-center justify-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span x-text="saving ? 'Menyimpan...' : '✓ SIMPAN TITIK RUMAH INI'"></span>
                            </button>
                            <button type="button" @click="cancelPinning()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                Batal
                            </button>
                        </div>
                    </div>
                </template>

            </div>

            <!-- Leaflet Map Canvas -->
            <div id="map" class="w-full h-[520px] sm:h-[650px] z-0"></div>

            <!-- Map Legend Footer -->
            <div class="p-3 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 text-[11px] text-slate-500">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-md bg-blue-600 inline-block shadow-xs"></span> Rumah Aktif
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-md bg-amber-500 inline-block shadow-xs"></span> 🛰️ Server / Starlink
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-md bg-indigo-600 inline-block shadow-xs"></span> 🔲 Kotak ODP
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-4 h-1 rounded bg-emerald-500 inline-block"></span> Jalur Dropcore
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-4 h-1 rounded bg-blue-500 inline-block"></span> Kabel Distribusi
                    </span>
                </div>
                <span>*Posisi & zoom peta tersimpan otomatis saat Anda berpindah halaman</span>
            </div>
        </div>

        <!-- Sidebar List: Pelanggan Belum Dititik, Server/ODP & Kabel -->
        <div class="lg:col-span-1 space-y-4">
            
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs p-4 sm:p-5 flex flex-col h-[520px] sm:h-[650px]">
                
                <!-- Sidebar Tabs -->
                <div class="flex items-center gap-1 border-b border-slate-100 pb-2 mb-3">
                    <button type="button" @click="sidebarTab = 'unpinned'" 
                            :class="sidebarTab === 'unpinned' ? 'bg-amber-50 text-amber-800 font-extrabold border-amber-200' : 'text-slate-500 hover:text-slate-800 border-transparent'"
                            class="flex-1 py-1.5 px-2 rounded-xl text-xs border text-center transition">
                        Belum Titik (<span x-text="unpinnedList.length"></span>)
                    </button>
                    <button type="button" @click="sidebarTab = 'infrastructure'" 
                            :class="sidebarTab === 'infrastructure' ? 'bg-indigo-50 text-indigo-800 font-extrabold border-indigo-200' : 'text-slate-500 hover:text-slate-800 border-transparent'"
                            class="flex-1 py-1.5 px-2 rounded-xl text-xs border text-center transition">
                        Server/ODP (<span x-text="networkNodesData.length"></span>)
                    </button>
                </div>

                <!-- Tab 1: Pelanggan Belum Dititik -->
                <div x-show="sidebarTab === 'unpinned'" class="overflow-y-auto divide-y divide-slate-100 flex-1 pr-1">
                    <template x-for="c in unpinnedList" :key="c.id">
                        <div class="py-2.5 px-2 rounded-xl hover:bg-slate-50 transition flex items-center justify-between gap-2 group">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate" x-text="c.name"></p>
                                <p class="text-[10px] text-slate-500 truncate" x-text="(c.village ? c.village.name : 'Tanpa Desa') + ' • ' + c.customer_code"></p>
                            </div>
                            
                            <button type="button" @click="startPinning(c)" 
                                    class="px-2.5 py-1.5 rounded-lg bg-brand-blue hover:bg-brand-blueHover text-white text-[11px] font-bold shrink-0 transition flex items-center gap-1 shadow-xs">
                                <i data-lucide="crosshair" class="w-3 h-3"></i>
                                <span>Titik</span>
                            </button>
                        </div>
                    </template>

                    <div x-show="unpinnedList.length === 0" class="py-12 text-center text-slate-400 text-xs">
                        <i data-lucide="check-circle" class="w-8 h-8 mx-auto text-emerald-400 mb-2"></i>
                        Semua pelanggan sudah memiliki titik koordinat GPS! 👍
                    </div>
                </div>

                <!-- Tab 2: Server & Kotak ODP -->
                <div x-show="sidebarTab === 'infrastructure'" class="overflow-y-auto divide-y divide-slate-100 flex-1 pr-1">
                    <template x-for="node in networkNodesData" :key="'infra_' + node.id">
                        <div class="py-2.5 px-2 rounded-xl hover:bg-slate-50 transition flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate" x-text="(node.type === 'server' || node.type === 'starlink' ? '🛰️ ' : '🔲 ') + node.name"></p>
                                <p class="text-[10px] text-slate-500 truncate" x-text="(node.village ? node.village.name : 'Pusat') + (node.capacity ? ' • ' + node.capacity : '')"></p>
                            </div>
                            
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" @click="centerOnCoords(node.latitude, node.longitude, 18)" 
                                        class="p-1.5 rounded-lg bg-blue-50 text-brand-blue hover:bg-blue-100 text-xs" title="Lihat di Peta">
                                    <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" @click="deleteNetworkNode(node.id)" 
                                        class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs" title="Hapus">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div x-show="networkNodesData.length === 0" class="py-12 text-center text-slate-400 text-xs space-y-2">
                        <i data-lucide="server" class="w-8 h-8 mx-auto text-slate-300"></i>
                        <p>Belum ada titik Server atau ODP.</p>
                        <button type="button" @click="openAddNodeModal()" class="text-brand-blue font-bold text-xs hover:underline">
                            + Tambah Server / ODP
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Modal 1: Penitikan Baru (Pelanggan / Server / ODP / Titik Desa) -->
    <div x-show="manualPinModalOpen" x-cloak 
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
         style="padding-top: max(20px, calc(env(safe-area-inset-top, 0px) + 12px)); padding-bottom: max(20px, calc(env(safe-area-inset-bottom, 0px) + 12px));">
        <div @click.away="manualPinModalOpen = false" 
             class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 relative my-auto flex flex-col max-h-[90vh] overflow-hidden">
            
            <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-white shrink-0">
                <div class="flex items-center gap-2.5 text-slate-900 font-extrabold text-base">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-brand-blue flex items-center justify-center shadow-xs">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Penitikan Peta Manual</h3>
                        <p class="text-[11px] text-slate-400 font-medium">Pilih jenis objek yang ingin Anda tandai di peta satelit</p>
                    </div>
                </div>
                <button type="button" @click="manualPinModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Tab Options inside Modal -->
            <div class="p-3 bg-slate-50 border-b border-slate-100 flex items-center gap-1 text-xs">
                <button type="button" @click="pinTypeTab = 'customer'" 
                        :class="pinTypeTab === 'customer' ? 'bg-white text-brand-blue font-extrabold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                        class="flex-1 py-1.5 rounded-xl transition">
                    🏠 Rumah Pelanggan
                </button>
                <button type="button" @click="pinTypeTab = 'node'" 
                        :class="pinTypeTab === 'node' ? 'bg-white text-amber-700 font-extrabold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                        class="flex-1 py-1.5 rounded-xl transition">
                    🛰️ Server / ODP
                </button>
                <button type="button" @click="pinTypeTab = 'village'" 
                        :class="pinTypeTab === 'village' ? 'bg-white text-emerald-700 font-extrabold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                        class="flex-1 py-1.5 rounded-xl transition">
                    🏛️ Titik Pusat Desa
                </button>
            </div>

            <div class="overflow-y-auto p-5 space-y-4 flex-1">
                
                <!-- Sub-tab 1: Pelanggan -->
                <div x-show="pinTypeTab === 'customer'" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Pelanggan Yang Ingin Dititik *</label>
                        <div class="relative mb-2">
                            <input type="text" x-model="modalCustomerSearch" placeholder="Ketik nama atau kode pelanggan..."
                                   class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
                        </div>

                        <select x-model="modalSelectedCustomerId" 
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="">-- Pilih Pelanggan --</option>
                            <optgroup label="📍 Belum Memiliki Titik GPS">
                                <template x-for="c in filteredUnpinnedListForModal" :key="'unp_' + c.id">
                                    <option :value="c.id" x-text="c.name + ' (' + (c.village ? c.village.name : 'Tanpa Desa') + ' - ' + c.customer_code + ')'"></option>
                                </template>
                            </optgroup>
                            <optgroup label="✅ Sudah Ada Titik (Ubah / Geser Titik)">
                                <template x-for="c in filteredPinnedListForModal" :key="'pin_' + c.id">
                                    <option :value="c.id" x-text="c.name + ' (' + (c.village ? c.village.name : 'Tanpa Desa') + ')'"></option>
                                </template>
                            </optgroup>
                        </select>
                    </div>

                    <div class="p-3.5 rounded-2xl border-2 border-brand-blue/40 bg-blue-50/50 hover:bg-blue-50 transition cursor-pointer"
                         @click="startPinningFromModal()">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-blue text-white flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="crosshair" class="w-5 h-5"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-extrabold text-xs text-brand-blue">Arahkan ke Peta Satelit (Klik Atap Rumah)</h4>
                                <p class="text-[11px] text-slate-600 mt-0.5">Peta otomatis terbang ke desa pelanggan, Anda tinggal klik di atas atap rumahnya pada peta.</p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-brand-blue shrink-0"></i>
                        </div>
                    </div>
                </div>

                <!-- Sub-tab 2: Server / ODP -->
                <div x-show="pinTypeTab === 'node'" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Perangkat *</label>
                        <select x-model="newNodeType" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                            <option value="starlink">🛰️ Dish Antena Starlink</option>
                            <option value="server">🖥️ Server Utama / NOC / Mikrotik</option>
                            <option value="odp">🔲 Kotak ODP (Optical Distribution Point)</option>
                            <option value="switch">🔀 Switch / Router Distribusi</option>
                            <option value="pole">📍 Tiang Distribusi FO</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perangkat / Label *</label>
                        <input type="text" x-model="newNodeName" placeholder="Contoh: Server SD HMDC 07 / ODP-01 Simpur"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Wilayah / Desa</label>
                            <select x-model="newNodeVillageId" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                                <option value="">Semua Wilayah</option>
                                <template x-for="v in villagesData" :key="'nv_' + v.id">
                                    <option :value="v.id" x-text="v.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kapasitas Port / Tipe</label>
                            <input type="text" x-model="newNodeCapacity" placeholder="Contoh: 8 Port / 16 Port"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Lokasi</label>
                        <input type="text" x-model="newNodeNotes" placeholder="Contoh: Di tiang PLN depan balai desa"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    </div>

                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/80 text-amber-900 text-xs">
                        <p class="font-bold">📍 Cara Menentukan Titik Server / ODP:</p>
                        <p class="text-[11px] mt-0.5">Tutup modal ini, lalu <strong>klik di titik tiang/bangunan pada peta satelit</strong>, lalu pilih <em>+ Titik Server / ODP</em>.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Latitude</label>
                            <input type="text" x-model="modalInputLat" placeholder="-7.536123" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Longitude</label>
                            <input type="text" x-model="modalInputLng" placeholder="112.238456" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono">
                        </div>
                    </div>

                    <button type="button" @click="saveNodeDirectFromModal()" :disabled="!newNodeName || !modalInputLat || !modalInputLng || saving"
                            class="w-full py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 disabled:opacity-40 text-white font-bold text-xs transition shadow-xs">
                        <span x-text="saving ? 'Menyimpan...' : '✓ Simpan Titik Perangkat Ini'"></span>
                    </button>
                </div>

                <!-- Sub-tab 3: Titik Pusat Desa -->
                <div x-show="pinTypeTab === 'village'" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Wilayah / Desa *</label>
                        <select x-model="selectedVillageForCenter" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                            <option value="">-- Pilih Desa --</option>
                            <template x-for="v in villagesData" :key="'vc_' + v.id">
                                <option :value="v.id" x-text="v.name + (v.latitude ? ' (Sudah Ada Titik)' : ' (Belum Ada Titik)')"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Latitude</label>
                            <input type="text" x-model="modalInputLat" placeholder="-7.536123" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Longitude</label>
                            <input type="text" x-model="modalInputLng" placeholder="112.238456" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono">
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500">
                        *Dengan menyimpan titik pusat desa, saat Anda mencari nama desa di kotak pencarian peta, kamera akan otomatis terbang ke desa tersebut.
                    </p>

                    <button type="button" @click="saveVillageCenterFromModal()" :disabled="!selectedVillageForCenter || !modalInputLat || !modalInputLng || saving"
                            class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-bold text-xs transition shadow-xs">
                        <span x-text="saving ? 'Menyimpan...' : '✓ Simpan Titik Pusat Desa'"></span>
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- Modal 2: Simpan Jalur Kabel FO Baru -->
    <div x-show="cableModalOpen" x-cloak 
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
         style="padding-top: max(20px, calc(env(safe-area-inset-top, 0px) + 12px)); padding-bottom: max(20px, calc(env(safe-area-inset-bottom, 0px) + 12px));">
        <div @click.away="cableModalOpen = false" 
             class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 p-5 space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span>Simpan Jalur Kabel Optik</span>
                </h3>
                <button type="button" @click="cableModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Jalur Kabel *</label>
                    <input type="text" x-model="newCableName" placeholder="Contoh: Kabel Dropcore Server ke ODP 01"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Kabel</label>
                        <select x-model="newCableType" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs">
                            <option value="dropcore">Kabel Dropcore</option>
                            <option value="distribution">Kabel Distribusi</option>
                            <option value="backbone">Kabel Backbone</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Core</label>
                        <select x-model="newCableCore" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs font-mono">
                            <option value="1 Core">1 Core</option>
                            <option value="2 Core">2 Core</option>
                            <option value="4 Core">4 Core</option>
                            <option value="12 Core">12 Core</option>
                            <option value="24 Core">24 Core</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Wilayah / Desa</label>
                        <select x-model="newCableVillageId" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs">
                            <option value="">Semua Wilayah</option>
                            <template x-for="v in villagesData" :key="'cv_' + v.id">
                                <option :value="v.id" x-text="v.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Warna Garis</label>
                        <select x-model="newCableColor" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs font-bold">
                            <option value="#10b981">🟢 Hijau (Dropcore)</option>
                            <option value="#3b82f6">🔵 Biru (Distribusi)</option>
                            <option value="#f59e0b">🟡 Kuning (Backbone)</option>
                            <option value="#ef4444">🔴 Merah (Kritis)</option>
                            <option value="#8b5cf6">🟣 Ungu (Cadangan)</option>
                        </select>
                    </div>
                </div>

                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs flex items-center justify-between font-bold text-slate-700">
                    <span>Total Panjang Kabel:</span>
                    <span class="font-mono text-emerald-700 font-extrabold text-sm" x-text="newCableLengthMeters + ' meter'"></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" x-model="newCableNotes" placeholder="Contoh: Melewati tiang PLN 1 s/d 6"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                </div>

                <button type="button" @click="saveCableRoute()" :disabled="!newCableName || saving"
                        class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/30">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span x-text="saving ? 'Menyimpan...' : '✓ SIMPAN JALUR KABEL'"></span>
                </button>
            </div>

        </div>
    </div>

    <!-- Modal 3: Tambah Server / ODP Cepat -->
    <div x-show="newNodeModalOpen" x-cloak 
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
         style="padding-top: max(20px, calc(env(safe-area-inset-top, 0px) + 12px)); padding-bottom: max(20px, calc(env(safe-area-inset-bottom, 0px) + 12px));">
        <div @click.away="newNodeModalOpen = false" 
             class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 p-5 space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span>Tambah Titik Server / ODP</span>
                </h3>
                <button type="button" @click="newNodeModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Perangkat *</label>
                    <select x-model="newNodeType" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        <option value="starlink">🛰️ Dish Antena Starlink</option>
                        <option value="server">🖥️ Server Utama / NOC / Mikrotik</option>
                        <option value="odp">🔲 Kotak ODP (Optical Distribution Point)</option>
                        <option value="switch">🔀 Switch / Router Distribusi</option>
                        <option value="pole">📍 Tiang Distribusi FO</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perangkat / Label *</label>
                    <input type="text" x-model="newNodeName" placeholder="Contoh: Server SD HMDC 07 / ODP-01 Simpur"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Wilayah / Desa</label>
                        <select x-model="newNodeVillageId" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                            <option value="">Semua Wilayah</option>
                            <template x-for="v in villagesData" :key="'nvm_' + v.id">
                                <option :value="v.id" x-text="v.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kapasitas Port / Tipe</label>
                        <input type="text" x-model="newNodeCapacity" placeholder="Contoh: 8 Port / 16 Port"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Lokasi</label>
                    <input type="text" x-model="newNodeNotes" placeholder="Contoh: Di tiang PLN depan rumah Pak RT"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                </div>

                <div class="p-2.5 bg-blue-50 rounded-xl text-[11px] text-blue-900 font-mono">
                    Koordinat: <span x-text="newNodeCoordinate ? newNodeCoordinate.lat.toFixed(6) + ', ' + newNodeCoordinate.lng.toFixed(6) : '-'"></span>
                </div>

                <button type="button" @click="saveNetworkNode()" :disabled="!newNodeName || saving"
                        class="w-full py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 disabled:opacity-40 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 shadow-md shadow-amber-600/30">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span x-text="saving ? 'Menyimpan...' : '✓ SIMPAN TITIK PERANGKAT'"></span>
                </button>
            </div>

        </div>
    </div>

    <!-- Modal 4: Reset Titik Peta (Admin Only) -->
    <div x-show="resetMapModalOpen" x-cloak 
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
         style="padding-top: max(20px, calc(env(safe-area-inset-top, 0px) + 12px)); padding-bottom: max(20px, calc(env(safe-area-inset-bottom, 0px) + 12px));">
        <div @click.away="resetMapModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-rose-200 space-y-4">
            <div class="flex items-center gap-3 text-rose-600">
                <div class="w-10 h-10 rounded-2xl bg-rose-50 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">Reset Data Peta</h3>
                    <p class="text-xs text-rose-600 font-semibold">Tindakan ini permanen & memerlukan konfirmasi</p>
                </div>
            </div>

            <form action="{{ route('maps.reset') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-2 text-xs text-slate-700">
                    <label class="flex items-start gap-2 p-3 rounded-2xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="action_type" value="coordinates_only" checked class="mt-0.5 text-brand-blue focus:ring-brand-blue">
                        <div>
                            <strong class="text-slate-900 block font-bold">1. Kosongkan Titik GPS Saja (Recommended)</strong>
                            <span class="text-slate-500 text-[11px] block mt-0.5">Semua data pelanggan & tagihan tetap aman, hanya koordinat di peta yang di-0 kan untuk dititik ulang.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2 p-3 rounded-2xl border border-rose-200 bg-rose-50/50 hover:bg-rose-50 cursor-pointer">
                        <input type="radio" name="action_type" value="delete_all_customers" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                        <div>
                            <strong class="text-rose-900 block font-bold">2. Hapus Seluruh Pelanggan (0 Pelanggan)</strong>
                            <span class="text-rose-600/80 text-[11px] block mt-0.5">Menghapus seluruh daftar pelanggan dari sistem sehingga Anda bisa input data pelanggan baru dari nol.</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Ketik kata <span class="text-rose-600 font-mono font-bold">RESET</span> untuk konfirmasi:
                    </label>
                    <input type="text" name="confirmation" required placeholder="RESET" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold uppercase focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="resetMapModalOpen = false" 
                            class="flex-1 py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition shadow-md shadow-rose-600/30">
                        Ya, Reset Peta
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Leaflet Map Script Implementation -->
<script>
    function mapApp() {
        return {
            map: null,
            googleHybridLayer: null,
            esriSatelliteLayer: null,
            streetLayer: null,
            currentLayer: 'google',
            markersGroup: null,
            nodesGroup: null,
            cablesGroup: null,
            
            // Layer Visibility
            showCustomers: true,
            showNodes: true,
            showCables: true,
            
            // Tab sidebar
            sidebarTab: 'unpinned',

            // Data loaded from server (lightweight)
            isTeknisi: {{ auth()->user()->isTeknisi() ? 'true' : 'false' }},
            allCustomersData: @json($allCustomers),
            villagesData: @json($villages),
            networkNodesData: @json($networkNodes),
            cableRoutesData: @json($cableRoutes),
            
            pinnedList: [],
            unpinnedList: [],
            pinnedCount: 0,
            unpinnedCount: 0,

            // Filter & Search
            selectedVillage: '',
            searchQuery: '',
            searchDropdownOpen: false,
            customerMarkers: {},
            nodeMarkers: {},
            searchingExternal: false,
            externalResults: [],
            externalSearchMarker: null,
            suggestedVillages: [],

            get matchedCustomers() {
                if (!this.searchQuery || this.searchQuery.trim().length < 2) return [];
                const q = this.searchQuery.toLowerCase().trim();
                return this.pinnedList.filter(c => 
                    c.name.toLowerCase().includes(q) || 
                    c.customer_code.toLowerCase().includes(q) ||
                    (c.house_number && c.house_number.toLowerCase().includes(q)) ||
                    (c.pppoe_user && c.pppoe_user.toLowerCase().includes(q))
                ).slice(0, 8);
            },

            get matchedVillages() {
                if (!this.searchQuery || this.searchQuery.trim().length < 2) return [];
                const q = this.searchQuery.toLowerCase().trim();
                return this.villagesData.filter(v => v.name.toLowerCase().includes(q)).slice(0, 5);
            },

            get matchedNodes() {
                if (!this.searchQuery || this.searchQuery.trim().length < 2) return [];
                const q = this.searchQuery.toLowerCase().trim();
                return this.networkNodesData.filter(n => 
                    n.name.toLowerCase().includes(q) ||
                    (n.capacity && n.capacity.toLowerCase().includes(q)) ||
                    (n.notes && n.notes.toLowerCase().includes(q))
                ).slice(0, 5);
            },

            // Customer Pinning State
            pinningCustomer: null,
            temporaryMarker: null,
            pendingCoordinate: null,
            
            // Cable Drawing State
            drawingCable: false,
            cablePoints: [],
            tempPolyline: null,
            tempPointMarkers: [],
            cableModalOpen: false,
            newCableName: '',
            newCableVillageId: '',
            newCableType: 'dropcore',
            newCableColor: '#10b981',
            newCableCore: '2 Core',
            newCableNotes: '',
            newCableLengthMeters: 0,

            // New Node Modal State
            newNodeModalOpen: false,
            newNodeType: 'starlink',
            newNodeName: '',
            newNodeVillageId: '',
            newNodeCapacity: '8 Port',
            newNodeNotes: '',
            newNodeCoordinate: null,

            // Manual Pin Modal
            manualPinModalOpen: false,
            pinTypeTab: 'customer',
            modalCustomerSearch: '',
            modalSelectedCustomerId: '',
            modalInputLat: '',
            modalInputLng: '',
            selectedVillageForCenter: '',

            resetMapModalOpen: false,
            saving: false,
            isMapMoving: false,

            get filteredUnpinnedListForModal() {
                if (!this.modalCustomerSearch) return this.unpinnedList;
                const q = this.modalCustomerSearch.toLowerCase();
                return this.unpinnedList.filter(c => 
                    c.name.toLowerCase().includes(q) || 
                    c.customer_code.toLowerCase().includes(q) ||
                    (c.village && c.village.name.toLowerCase().includes(q))
                );
            },

            get filteredPinnedListForModal() {
                if (!this.modalCustomerSearch) return this.pinnedList;
                const q = this.modalCustomerSearch.toLowerCase();
                return this.pinnedList.filter(c => 
                    c.name.toLowerCase().includes(q) || 
                    c.customer_code.toLowerCase().includes(q) ||
                    (c.village && c.village.name.toLowerCase().includes(q))
                );
            },

            init() {
                window.mapAppInstance = this;
                
                // Partition customers in memory to keep payload ultra-fast
                this.pinnedList = this.allCustomersData.filter(c => c.latitude !== null && c.longitude !== null && !isNaN(parseFloat(c.latitude)));
                this.unpinnedList = this.allCustomersData.filter(c => c.latitude === null || c.longitude === null || isNaN(parseFloat(c.latitude)));
                this.pinnedCount = this.pinnedList.length;
                this.unpinnedCount = this.unpinnedList.length;

                this.$nextTick(() => {
                    this.initMap();
                });
            },

            // Save last map state (lat, lng, zoom, layer) so user never loses their view!
            saveMapState() {
                if (!this.map) return;
                try {
                    const center = this.map.getCenter();
                    const zoom = this.map.getZoom();
                    localStorage.setItem('hokigo_map_state', JSON.stringify({
                        lat: center.lat,
                        lng: center.lng,
                        zoom: zoom,
                        layer: this.currentLayer
                    }));
                } catch (e) {}
            },

            getSavedMapState() {
                try {
                    const raw = localStorage.getItem('hokigo_map_state');
                    if (raw) return JSON.parse(raw);
                } catch (e) {}
                return null;
            },

            initMap() {
                const saved = this.getSavedMapState();
                let defaultLat = -7.536064;
                let defaultLng = 112.238402;
                let defaultZoom = 13;

                // Priority 1: Restore exact location where user left off
                if (saved && saved.lat && saved.lng && saved.zoom) {
                    defaultLat = parseFloat(saved.lat);
                    defaultLng = parseFloat(saved.lng);
                    defaultZoom = parseInt(saved.zoom);
                    if (saved.layer) this.currentLayer = saved.layer;
                } else if (this.pinnedList.length > 0) {
                    defaultLat = parseFloat(this.pinnedList[0].latitude);
                    defaultLng = parseFloat(this.pinnedList[0].longitude);
                    defaultZoom = 16;
                }

                // Initialize Leaflet Map
                this.map = L.map('map', {
                    center: [defaultLat, defaultLng],
                    zoom: defaultZoom,
                    zoomControl: true,
                    preferCanvas: true
                });

                // Google Hybrid Satellite
                this.googleHybridLayer = L.tileLayer('https://mt{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                    subdomains: ['0', '1', '2', '3'],
                    maxZoom: 20,
                    attribution: '&copy; Google Maps',
                    updateWhenIdle: true,
                    updateWhenZooming: false,
                    keepBuffer: 3
                });

                // Esri World Imagery (Satellite)
                this.esriSatelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri',
                    maxNativeZoom: 18,
                    maxZoom: 20,
                    updateWhenIdle: true,
                    updateWhenZooming: false,
                    keepBuffer: 2
                });

                // Street Tile Layer (OpenStreetMap)
                this.streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19,
                    updateWhenIdle: true,
                    keepBuffer: 2
                });

                // Apply active layer
                this.setLayer(this.currentLayer || 'google');

                // Feature groups
                this.cablesGroup = L.featureGroup().addTo(this.map);
                this.nodesGroup = L.featureGroup().addTo(this.map);
                this.markersGroup = L.featureGroup().addTo(this.map);

                // Auto-sync container size on mobile
                setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 150);
                setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 400);
                window.addEventListener('resize', () => {
                    if (this.map) this.map.invalidateSize();
                });

                // Render all features
                this.renderAll();

                // Persistence: save position on move & zoom
                this.map.on('movestart', () => { this.isMapMoving = true; });
                this.map.on('moveend', () => {
                    this.saveMapState();
                    setTimeout(() => { this.isMapMoving = false; }, 250);
                });

                // Click listener
                this.map.on('click', (e) => {
                    if (this.isMapMoving) return;

                    // Case A: Drawing cable line
                    if (this.drawingCable) {
                        this.handleCablePointClick(e.latlng);
                        return;
                    }

                    // Case B: Pinning customer house
                    if (this.pinningCustomer !== null) {
                        this.handleMapClickPinning(e.latlng);
                        return;
                    }

                    // Case C: General empty spot click
                    const lat = e.latlng.lat.toFixed(6);
                    const lng = e.latlng.lng.toFixed(6);
                    L.popup({ maxWidth: 240 })
                        .setLatLng(e.latlng)
                        .setContent(`
                            <div style="font-family: inherit; font-size: 11px; text-align: center; padding: 4px;">
                                <div style="font-weight: 800; color: #0f172a; margin-bottom: 2px;">📍 Titik Koordinat Peta</div>
                                <div style="font-family: monospace; font-size: 10px; color: #64748b; margin-bottom: 8px;">${lat}, ${lng}</div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <button type="button" onclick="window.mapAppInstance.openModalWithCoordinate(${lat}, ${lng})"
                                            style="background: #1055CC; color: white; border: none; padding: 6px 10px; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer; width: 100%;">
                                        + Titik Rumah Pelanggan
                                    </button>
                                    <button type="button" onclick="window.mapAppInstance.openAddNodeModal(${lat}, ${lng})"
                                            style="background: #f59e0b; color: white; border: none; padding: 6px 10px; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer; width: 100%;">
                                        + Titik Server / ODP
                                    </button>
                                </div>
                            </div>
                        `)
                        .openOn(this.map);
                });
            },

            setLayer(type) {
                this.currentLayer = type;
                if (this.googleHybridLayer) this.map.removeLayer(this.googleHybridLayer);
                if (this.esriSatelliteLayer) this.map.removeLayer(this.esriSatelliteLayer);
                if (this.streetLayer) this.map.removeLayer(this.streetLayer);

                if (type === 'google') {
                    this.googleHybridLayer.addTo(this.map);
                } else if (type === 'esri') {
                    this.esriSatelliteLayer.addTo(this.map);
                } else if (type === 'street') {
                    this.streetLayer.addTo(this.map);
                }
                this.saveMapState();
            },

            renderAll() {
                this.renderCables();
                this.renderNodes();
                this.renderMarkers();
            },

            // --- SEARCH & DIRECT FLY-TO IMPLEMENTATION ---
            onVillageSelected() {
                this.filterMarkers();
                if (!this.selectedVillage) return;
                this.flyToVillage(this.selectedVillage);
            },

            onSearchInput() {
                this.searchDropdownOpen = true;
                this.filterMarkers();
            },

            clearSearch() {
                this.searchQuery = '';
                this.searchDropdownOpen = false;
                this.externalResults = [];
                if (this.externalSearchMarker) {
                    this.map.removeLayer(this.externalSearchMarker);
                    this.externalSearchMarker = null;
                }
                this.filterMarkers();
            },

            goToCustomer(c) {
                if (!c.latitude || !c.longitude) {
                    alert('Pelanggan ' + c.name + ' belum memiliki titik koordinat.');
                    return;
                }
                const lat = parseFloat(c.latitude);
                const lng = parseFloat(c.longitude);
                this.searchDropdownOpen = false;
                this.searchQuery = c.name;
                this.showCustomers = true;

                if (this.selectedVillage && c.village_id && this.selectedVillage != c.village_id) {
                    this.selectedVillage = '';
                }
                this.renderAll();

                // Smoothly fly directly to exact customer rooftop at zoom 18
                this.map.flyTo([lat, lng], 18, { duration: 1.2 });

                setTimeout(() => {
                    const marker = this.customerMarkers[c.id];
                    if (marker) {
                        marker.openPopup();
                    }
                }, 1100);
            },

            goToNode(node) {
                if (!node.latitude || !node.longitude) return;
                const lat = parseFloat(node.latitude);
                const lng = parseFloat(node.longitude);
                this.searchDropdownOpen = false;
                this.searchQuery = node.name;
                this.showNodes = true;

                if (this.selectedVillage && node.village_id && this.selectedVillage != node.village_id) {
                    this.selectedVillage = '';
                }
                this.renderAll();

                // Smoothly fly directly to exact server / ODP location at zoom 18
                this.map.flyTo([lat, lng], 18, { duration: 1.2 });

                setTimeout(() => {
                    const marker = this.nodeMarkers[node.id];
                    if (marker) {
                        marker.openPopup();
                    }
                }, 1100);
            },

            goToVillage(v) {
                this.searchDropdownOpen = false;
                this.searchQuery = v.name;
                this.flyToVillage(v.id);
            },

            goToFirstSearchResult() {
                if (this.matchedCustomers.length > 0) {
                    this.goToCustomer(this.matchedCustomers[0]);
                } else if (this.matchedVillages.length > 0) {
                    this.goToVillage(this.matchedVillages[0]);
                } else if (this.matchedNodes.length > 0) {
                    this.goToNode(this.matchedNodes[0]);
                } else if (this.externalResults.length > 0) {
                    this.goToExternalPlace(this.externalResults[0]);
                } else {
                    this.triggerExternalSearch();
                }
            },

            async triggerExternalSearch() {
                if (!this.searchQuery || this.searchQuery.trim().length < 2) return;
                this.searchingExternal = true;
                try {
                    const query = this.searchQuery.trim();
                    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=4`);
                    const data = await res.json();
                    this.externalResults = data || [];
                    if (this.externalResults.length === 0) {
                        alert('Tidak ditemukan lokasi dengan nama "' + query + '" di peta satelit.');
                    } else if (this.externalResults.length === 1) {
                        this.goToExternalPlace(this.externalResults[0]);
                    }
                } catch (e) {
                    alert('Gagal mencari lokasi online.');
                } finally {
                    this.searchingExternal = false;
                }
            },

            goToExternalPlace(place) {
                const lat = parseFloat(place.lat);
                const lng = parseFloat(place.lon);
                this.searchDropdownOpen = false;
                this.searchQuery = place.display_name.split(',')[0];

                this.map.flyTo([lat, lng], 17, { duration: 1.5 });

                if (this.externalSearchMarker) {
                    this.map.removeLayer(this.externalSearchMarker);
                }
                this.externalSearchMarker = L.marker([lat, lng])
                    .addTo(this.map)
                    .bindPopup(`
                        <div style="font-family: inherit; font-size: 11px; padding: 4px; text-align: center; max-width: 220px;">
                            <div style="font-weight: 800; color: #0f172a; font-size: 12px;">📍 ${place.display_name.split(',')[0]}</div>
                            <div style="color: #64748b; font-size: 10px; margin-top: 2px; line-height: 1.3;">${place.display_name}</div>
                            <div style="margin-top: 6px; display: flex; gap: 4px;">
                                <button type="button" onclick="window.mapAppInstance.openModalWithCoordinate(${lat}, ${lng})"
                                        style="flex: 1; background: #1055CC; color: white; border: none; padding: 5px 8px; border-radius: 6px; font-weight: 700; font-size: 10px; cursor: pointer;">
                                    + Titik Rumah
                                </button>
                                <button type="button" onclick="window.mapAppInstance.openAddNodeModal(${lat}, ${lng})"
                                        style="flex: 1; background: #f59e0b; color: white; border: none; padding: 5px 8px; border-radius: 6px; font-weight: 700; font-size: 10px; cursor: pointer;">
                                    + Titik Server
                                </button>
                            </div>
                        </div>
                    `)
                    .openPopup();
            },

            flyToVillage(villageId) {
                const v = this.villagesData.find(item => item.id == villageId);
                if (!v) return;

                this.selectedVillage = v.id;
                this.suggestedVillages = [];

                if (v.latitude && v.longitude) {
                    this.map.flyTo([parseFloat(v.latitude), parseFloat(v.longitude)], v.zoom_level || 16, {
                        duration: 1.2
                    });
                } else {
                    // Calculate centroid of customers in this village
                    const villageCustomers = this.pinnedList.filter(c => c.village_id == v.id);
                    if (villageCustomers.length > 0) {
                        const bounds = L.latLngBounds(villageCustomers.map(c => [parseFloat(c.latitude), parseFloat(c.longitude)]));
                        this.map.flyToBounds(bounds, { padding: [50, 50], maxZoom: 17, duration: 1.2 });
                    }
                }

                this.filterMarkers();
            },

            centerOnCoords(lat, lng, zoom = 17) {
                if (!lat || !lng) return;
                this.map.flyTo([parseFloat(lat), parseFloat(lng)], zoom, { duration: 1 });
            },

            // --- CABLE DRAWING TOOL ---
            startCableDrawing() {
                this.cancelPinning();
                this.drawingCable = true;
                this.cablePoints = [];
                this.newCableLengthMeters = 0;
                this.clearTempCable();
                
                const mapEl = document.getElementById('map');
                if (mapEl) mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            },

            cancelCableDrawing() {
                this.drawingCable = false;
                this.cablePoints = [];
                this.newCableLengthMeters = 0;
                this.clearTempCable();
            },

            clearTempCable() {
                if (this.tempPolyline) {
                    this.map.removeLayer(this.tempPolyline);
                    this.tempPolyline = null;
                }
                this.tempPointMarkers.forEach(m => this.map.removeLayer(m));
                this.tempPointMarkers = [];
            },

            handleCablePointClick(latlng) {
                this.cablePoints.push([latlng.lat, latlng.lng]);
                
                // Waypoint circle
                const dot = L.circleMarker(latlng, {
                    radius: 5,
                    color: '#ffffff',
                    fillColor: this.newCableColor || '#10b981',
                    fillOpacity: 1,
                    weight: 2
                }).addTo(this.map);
                this.tempPointMarkers.push(dot);

                // Polyline line
                if (this.tempPolyline) {
                    this.tempPolyline.setLatLngs(this.cablePoints);
                } else {
                    this.tempPolyline = L.polyline(this.cablePoints, {
                        color: this.newCableColor || '#10b981',
                        weight: 4.5,
                        opacity: 0.9,
                        dashArray: '6, 6'
                    }).addTo(this.map);
                }

                // Compute length
                this.newCableLengthMeters = this.calculateDistance(this.cablePoints);
            },

            calculateDistance(points) {
                if (points.length < 2) return 0;
                let total = 0;
                for (let i = 0; i < points.length - 1; i++) {
                    const p1 = L.latLng(points[i][0], points[i][1]);
                    const p2 = L.latLng(points[i + 1][0], points[i + 1][1]);
                    total += p1.distanceTo(p2);
                }
                return Math.round(total);
            },

            openSaveCableModal() {
                if (this.cablePoints.length < 2) {
                    alert('Minimal klik 2 titik di peta satelit untuk membentuk jalur kabel.');
                    return;
                }
                this.newCableLengthMeters = this.calculateDistance(this.cablePoints);
                this.cableModalOpen = true;
            },

            async saveCableRoute() {
                if (!this.newCableName) {
                    alert('Masukkan nama jalur kabel.');
                    return;
                }
                this.saving = true;
                try {
                    const response = await fetch('/maps/cables', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            village_id: this.newCableVillageId || null,
                            name: this.newCableName,
                            cable_type: this.newCableType,
                            color: this.newCableColor,
                            core_count: this.newCableCore,
                            coordinates: this.cablePoints,
                            length_meters: this.newCableLengthMeters,
                            notes: this.newCableNotes
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.cableRoutesData.push(data.cable);
                        this.cableModalOpen = false;
                        this.cancelCableDrawing();
                        this.renderCables();
                        alert('Jalur kabel ' + data.cable.name + ' (' + data.cable.length_meters + ' m) berhasil disimpan!');
                    } else {
                        alert('Gagal menyimpan kabel.');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan koneksi.');
                } finally {
                    this.saving = false;
                }
            },

            async deleteCableRoute(cableId) {
                if (!confirm('Hapus jalur kabel ini dari peta?')) return;
                try {
                    const response = await fetch(`/maps/cables/${cableId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.cableRoutesData = this.cableRoutesData.filter(c => c.id != cableId);
                        this.renderCables();
                        this.map.closePopup();
                    }
                } catch (e) {
                    alert('Gagal menghapus kabel.');
                }
            },

            renderCables() {
                this.cablesGroup.clearLayers();
                if (!this.showCables) return;

                this.cableRoutesData.forEach(cable => {
                    if (!cable.coordinates || cable.coordinates.length < 2) return;

                    if (this.selectedVillage && cable.village_id && cable.village_id != this.selectedVillage) {
                        return;
                    }

                    const polyline = L.polyline(cable.coordinates, {
                        color: cable.color || '#10b981',
                        weight: 4.5,
                        opacity: 0.95,
                        lineJoin: 'round',
                        smoothFactor: 1
                    });

                    const villageName = cable.village ? cable.village.name : 'Semua Wilayah';
                    const typeLabel = cable.cable_type === 'backbone' ? 'Backbone Utama' : (cable.cable_type === 'distribution' ? 'Distribusi Antar ODP' : 'Dropcore Pelanggan');

                    polyline.bindPopup(`
                        <div style="font-family: inherit; font-size: 12px; min-width: 210px; padding: 4px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 4px;">
                                <span style="font-weight: 800; font-size: 13px; color: #0f172a;">〰️ ${cable.name}</span>
                            </div>
                            <div style="font-size: 11px; color: #64748b; line-height: 1.5; margin-bottom: 8px;">
                                <div>📍 Wilayah: <strong>${villageName}</strong></div>
                                <div>📦 Tipe: <strong style="color: ${cable.color};">${typeLabel}</strong></div>
                                <div>📏 Panjang: <strong>${cable.length_meters || '-'} meter</strong></div>
                                ${cable.core_count ? `<div>🧵 Kapasitas: <strong>${cable.core_count}</strong></div>` : ''}
                                ${cable.notes ? `<div style="margin-top: 4px; padding: 4px 6px; background: #f8fafc; border-radius: 4px; font-size: 10px;">📝 ${cable.notes}</div>` : ''}
                            </div>
                            <button type="button" onclick="window.mapAppInstance.deleteCableRoute(${cable.id})"
                                    style="width: 100%; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 5px 8px; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer;">
                                🗑️ Hapus Jalur Kabel
                            </button>
                        </div>
                    `, { autoPanPadding: [20, 20] });

                    this.cablesGroup.addLayer(polyline);
                });
            },

            // --- NETWORK NODES (SERVER, STARLINK, ODP) ---
            openAddNodeModal(lat = null, lng = null) {
                this.newNodeModalOpen = true;
                this.newNodeCoordinate = (lat && lng) ? { lat, lng } : null;
                this.newNodeName = '';
                this.newNodeCapacity = this.newNodeType === 'odp' ? '8 Port' : (this.newNodeType === 'starlink' ? 'Starlink Standard' : '');
                this.newNodeNotes = '';
                this.map.closePopup();
            },

            createNodeIcon(node) {
                let bgColor = '#f59e0b';
                let iconChar = '🛰️';
                let borderColor = '#ffffff';

                if (node.type === 'server') {
                    bgColor = '#0f172a';
                    iconChar = '🖥️';
                    borderColor = '#fbbf24';
                } else if (node.type === 'odp') {
                    bgColor = '#4f46e5';
                    iconChar = '🔲';
                    borderColor = '#c7d2fe';
                } else if (node.type === 'pole') {
                    bgColor = '#475569';
                    iconChar = '📍';
                }

                const html = `
                    <div style="position: relative; display: flex; flex-direction: column; align-items: center; pointer-events: auto;">
                        <div style="background-color: ${bgColor}; border: 2.5px solid ${borderColor}; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.4); font-size: 15px; color: white;">
                            ${iconChar}
                        </div>
                        <div style="background: rgba(15, 23, 42, 0.92); color: #fef08a; font-size: 10px; font-weight: 800; padding: 1.5px 6px; border-radius: 6px; margin-top: 3px; white-space: nowrap; border: 1px solid rgba(254, 240, 138, 0.4); text-shadow: 0 1px 2px rgba(0,0,0,0.8);">
                            ${node.name}
                        </div>
                    </div>
                `;

                return L.divIcon({
                    html: html,
                    className: 'custom-house-marker',
                    iconSize: [90, 50],
                    iconAnchor: [45, 25],
                    popupAnchor: [0, -25]
                });
            },

            async saveNetworkNode() {
                if (!this.newNodeName || !this.newNodeCoordinate) {
                    alert('Nama dan koordinat wajib diisi.');
                    return;
                }
                this.saving = true;
                try {
                    const response = await fetch('/maps/nodes', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            village_id: this.newNodeVillageId || null,
                            name: this.newNodeName,
                            type: this.newNodeType,
                            capacity: this.newNodeCapacity,
                            latitude: this.newNodeCoordinate.lat,
                            longitude: this.newNodeCoordinate.lng,
                            notes: this.newNodeNotes
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.networkNodesData.push(data.node);
                        this.newNodeModalOpen = false;
                        this.renderNodes();
                        alert('Titik ' + data.node.name + ' berhasil disimpan!');
                    } else {
                        alert('Gagal menyimpan perangkat.');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan koneksi.');
                } finally {
                    this.saving = false;
                }
            },

            async saveNodeDirectFromModal() {
                const lat = parseFloat(this.modalInputLat);
                const lng = parseFloat(this.modalInputLng);
                if (isNaN(lat) || isNaN(lng)) {
                    alert('Koordinat latitude dan longitude tidak valid.');
                    return;
                }
                this.newNodeCoordinate = { lat, lng };
                await this.saveNetworkNode();
                this.manualPinModalOpen = false;
            },

            async deleteNetworkNode(nodeId) {
                if (!confirm('Hapus titik perangkat ini dari peta?')) return;
                try {
                    const response = await fetch(`/maps/nodes/${nodeId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.networkNodesData = this.networkNodesData.filter(n => n.id != nodeId);
                        this.renderNodes();
                        this.map.closePopup();
                    }
                } catch (e) {
                    alert('Gagal menghapus perangkat.');
                }
            },

            renderNodes() {
                this.nodesGroup.clearLayers();
                this.nodeMarkers = {};
                if (!this.showNodes) return;

                this.networkNodesData.forEach(node => {
                    if (!node.latitude || !node.longitude) return;

                    if (this.selectedVillage && node.village_id && node.village_id != this.selectedVillage) {
                        return;
                    }

                    const lat = parseFloat(node.latitude);
                    const lng = parseFloat(node.longitude);
                    if (isNaN(lat) || isNaN(lng)) return;

                    const marker = L.marker([lat, lng], {
                        icon: this.createNodeIcon(node)
                    });

                    const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
                    const villageName = node.village ? node.village.name : 'Umum / Pusat';
                    const typeLabel = node.type === 'server' ? 'Server Pusat / NOC' : (node.type === 'starlink' ? 'Dish Antena Starlink' : (node.type === 'odp' ? 'Kotak ODP FO' : 'Perangkat Jaringan'));

                    marker.bindPopup(`
                        <div style="font-family: inherit; font-size: 12px; min-width: 220px; padding: 4px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 4px;">
                                <span style="font-weight: 800; font-size: 13px; color: #0f172a;">${node.name}</span>
                            </div>
                            <div style="font-size: 11px; color: #64748b; line-height: 1.5; margin-bottom: 8px;">
                                <div>⚙️ Tipe: <strong>${typeLabel}</strong></div>
                                <div>📍 Lokasi: <strong>${villageName}</strong></div>
                                ${node.capacity ? `<div>🔲 Kapasitas: <strong style="color: #4f46e5;">${node.capacity}</strong></div>` : ''}
                                ${node.notes ? `<div style="margin-top: 4px; padding: 4px 6px; background: #fef3c7; border-left: 3px solid #f59e0b; border-radius: 4px; color: #92400e; font-size: 10px;">📝 ${node.notes}</div>` : ''}
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <a href="${googleMapsUrl}" target="_blank" 
                                   style="display: flex; align-items: center; justify-content: center; gap: 4px; background: #059669; color: white; padding: 5px 8px; border-radius: 8px; font-weight: 700; font-size: 11px; text-decoration: none;">
                                    🗺️ Navigasi Google Maps
                                </a>
                                <button type="button" onclick="window.mapAppInstance.deleteNetworkNode(${node.id})"
                                        style="width: 100%; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 5px 8px; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer;">
                                    🗑️ Hapus Titik Ini
                                </button>
                            </div>
                        </div>
                    `, { autoPanPadding: [20, 20] });

                    this.nodesGroup.addLayer(marker);
                    this.nodeMarkers[node.id] = marker;
                });
            },

            // --- VILLAGE CENTER SETTING ---
            async saveVillageCenterFromModal() {
                if (!this.selectedVillageForCenter) {
                    alert('Pilih desa terlebih dahulu.');
                    return;
                }
                const lat = parseFloat(this.modalInputLat);
                const lng = parseFloat(this.modalInputLng);
                if (isNaN(lat) || isNaN(lng)) {
                    alert('Koordinat latitude dan longitude tidak valid.');
                    return;
                }

                this.saving = true;
                try {
                    const response = await fetch(`/maps/villages/${this.selectedVillageForCenter}/location`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ latitude: lat, longitude: lng, zoom_level: 16 })
                    });
                    const data = await response.json();
                    if (data.success) {
                        const v = this.villagesData.find(item => item.id == this.selectedVillageForCenter);
                        if (v) {
                            v.latitude = lat;
                            v.longitude = lng;
                            v.zoom_level = 16;
                        }
                        this.manualPinModalOpen = false;
                        this.map.flyTo([lat, lng], 16);
                        alert('Titik pusat desa ' + data.village.name + ' berhasil disimpan!');
                    } else {
                        alert('Gagal menyimpan titik desa.');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan koneksi.');
                } finally {
                    this.saving = false;
                }
            },

            // --- CUSTOMER MARKERS & POPUPS ---
            createCustomIcon(customer) {
                let bgColor = '#1055CC';
                let iconSymbol = '📶';

                if (customer.status === 'isolated') {
                    bgColor = '#D97706';
                    iconSymbol = '⚠️';
                } else if (customer.status === 'inactive') {
                    bgColor = '#E11D48';
                    iconSymbol = '❌';
                }

                const html = `
                    <div style="position: relative; display: flex; flex-direction: column; align-items: center; pointer-events: auto;">
                        <div style="background-color: ${bgColor}; border: 2.5px solid #ffffff; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.35); font-size: 14px; color: white;">
                            ${iconSymbol}
                        </div>
                        <div style="background: rgba(15, 23, 42, 0.9); color: #ffffff; font-size: 10px; font-weight: 700; padding: 1.5px 6px; border-radius: 6px; margin-top: 3px; white-space: nowrap; border: 1px solid rgba(255,255,255,0.3); text-shadow: 0 1px 2px rgba(0,0,0,0.8);">
                            ${customer.name}
                        </div>
                    </div>
                `;

                return L.divIcon({
                    html: html,
                    className: 'custom-house-marker',
                    iconSize: [80, 50],
                    iconAnchor: [40, 25],
                    popupAnchor: [0, -25]
                });
            },

            buildPopupHtml(c) {
                const lat = parseFloat(c.latitude);
                const lng = parseFloat(c.longitude);
                const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
                const customerUrl = `/customers/${c.id}`;
                const waUrl = c.phone ? `https://api.whatsapp.com/send?phone=${c.clean_phone || c.phone}` : '#';
                const villageName = c.village ? c.village.name : 'Tanpa Desa';
                const packageName = c.package ? `${c.package.name} (${c.package.speed})` : 'Paket Standar';
                const monthlyFee = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(c.monthly_fee || 0);

                const photoHtml = (c.has_photo && c.photo_url)
                    ? `<div style="margin-bottom: 8px; border-radius: 8px; overflow: hidden; height: 110px; background: #0f172a; position: relative;">
                         <img src="${c.photo_url}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;" alt="Foto Rumah">
                       </div>`
                    : '';
                const houseNumberHtml = c.house_number 
                    ? `<div>🏠 <strong>${c.house_number}</strong></div>`
                    : '';
                const redamanHtml = c.redaman 
                    ? `<div>📶 Redaman: <strong style="color: #059669; background: #ecfdf5; padding: 1px 5px; border-radius: 4px; border: 1px solid #a7f3d0;">${c.redaman}</strong></div>`
                    : '';

                return `
                    <div style="font-family: inherit; min-width: 220px; max-width: 280px; padding: 4px;">
                        ${photoHtml}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px;">
                            <h4 style="margin: 0; font-weight: 800; font-size: 14px; color: #0f172a;">${c.name}</h4>
                            <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 6px; background: #f1f5f9; color: #475569;">${c.customer_code}</span>
                        </div>
                        
                        <div style="font-size: 11px; color: #64748b; line-height: 1.4; margin-bottom: 8px;">
                            <div>📍 <strong>${villageName}</strong></div>
                            ${houseNumberHtml}
                            <div>📦 ${packageName} &bull; <strong>${monthlyFee}</strong></div>
                            ${redamanHtml}
                            ${c.pppoe_user ? `<div>👤 PPPoE: <code style="background: #e2e8f0; padding: 1px 4px; border-radius: 4px; font-weight: 600;">${c.pppoe_user}</code></div>` : ''}
                            ${c.pppoe_password ? `<div>🔑 Sandi PPPoE: <code style="background: #e2e8f0; padding: 1px 4px; border-radius: 4px; font-weight: 600;">${c.pppoe_password}</code></div>` : ''}
                            ${c.wifi_ssid ? `<div>📶 WiFi: <strong style="color: #0f172a;">${c.wifi_ssid}</strong></div>` : ''}
                            ${c.wifi_password ? `<div>🔑 Sandi WiFi: <code style="background: #ecfdf5; color: #065f46; padding: 1px 4px; border-radius: 4px; font-weight: 700; border: 1px solid #a7f3d0;">${c.wifi_password}</code></div>` : ''}
                            ${c.notes ? `<div style="margin-top: 4px; padding: 4px 6px; background: #fef3c7; border-left: 3px solid #f59e0b; border-radius: 4px; color: #92400e; font-size: 10px;">📝 ${c.notes}</div>` : ''}
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <a href="${googleMapsUrl}" target="_blank" 
                               style="display: flex; align-items: center; justify-content: center; gap: 4px; background: #059669; color: white; padding: 6px 10px; border-radius: 8px; font-weight: 700; font-size: 11px; text-decoration: none;">
                                🗺️ Buka Navigasi Google Maps
                            </a>
                            <div style="display: flex; gap: 4px;">
                                <a href="${customerUrl}" 
                                   style="${(!this.isTeknisi && c.phone) ? 'flex: 1;' : 'width: 100%;'} text-align: center; background: #1055CC; color: white; padding: 5px 8px; border-radius: 8px; font-weight: 700; font-size: 11px; text-decoration: none;">
                                    Lihat Profil
                                </a>
                                ${(!this.isTeknisi && c.phone) ? `
                                    <a href="${waUrl}" target="_blank" 
                                       style="flex: 1; text-align: center; background: #22c55e; color: white; padding: 5px 8px; border-radius: 8px; font-weight: 700; font-size: 11px; text-decoration: none;">
                                        Chat WA
                                    </a>
                                ` : ''}
                            </div>
                            <button type="button" onclick="window.mapAppInstance.startRepositionCustomer(${c.id})" 
                                    style="width: 100%; margin-top: 2px; display: flex; align-items: center; justify-content: center; gap: 4px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 5px 8px; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer;">
                                📍 Geser / Ubah Titik Rumah
                            </button>
                        </div>
                    </div>
                `;
            },

            renderMarkers() {
                this.markersGroup.clearLayers();
                this.customerMarkers = {};
                if (!this.showCustomers) return;

                const filtered = this.pinnedList.filter(c => {
                    const matchVillage = !this.selectedVillage || c.village_id == this.selectedVillage;
                    const matchSearch = !this.searchQuery || 
                        c.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                        c.customer_code.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        (c.pppoe_user && c.pppoe_user.toLowerCase().includes(this.searchQuery.toLowerCase()));
                    return matchVillage && matchSearch;
                });

                filtered.forEach(c => {
                    const lat = parseFloat(c.latitude);
                    const lng = parseFloat(c.longitude);
                    if (isNaN(lat) || isNaN(lng)) return;

                    const marker = L.marker([lat, lng], {
                        icon: this.createCustomIcon(c)
                    });

                    marker.bindPopup(() => this.buildPopupHtml(c), {
                        maxWidth: 290,
                        minWidth: 220,
                        autoPanPadding: [25, 25]
                    });

                    this.markersGroup.addLayer(marker);
                    this.customerMarkers[c.id] = marker;
                });
            },

            filterMarkers() {
                this.renderAll();
            },

            // --- MANUAL PINNING HELPERS ---
            openManualPinModal() {
                this.manualPinModalOpen = true;
                this.modalCustomerSearch = '';
                this.modalSelectedCustomerId = '';
                this.modalInputLat = '';
                this.modalInputLng = '';
                if (window.lucide) {
                    setTimeout(() => window.lucide.createIcons(), 50);
                }
            },

            openModalWithCoordinate(lat, lng) {
                this.map.closePopup();
                this.manualPinModalOpen = true;
                this.modalInputLat = lat;
                this.modalInputLng = lng;
                if (window.lucide) {
                    setTimeout(() => window.lucide.createIcons(), 50);
                }
            },

            startPinningFromModal() {
                if (!this.modalSelectedCustomerId) {
                    alert('Silakan pilih salah satu pelanggan terlebih dahulu.');
                    return;
                }

                const customer = this.allCustomersData.find(c => c.id == this.modalSelectedCustomerId);
                if (!customer) return;

                this.manualPinModalOpen = false;
                this.startPinning(customer);
            },

            startRepositionCustomer(customerId) {
                const customer = this.allCustomersData.find(c => c.id == customerId);
                if (!customer) return;

                this.map.closePopup();
                this.startPinning(customer);

                if (customer.latitude && customer.longitude) {
                    const lat = parseFloat(customer.latitude);
                    const lng = parseFloat(customer.longitude);
                    this.handleMapClickPinning({ lat: lat, lng: lng });
                    this.map.setView([lat, lng], 17);
                }
            },

            centerOnCustomerVillage(customer) {
                if (!customer) return;

                if (customer.latitude && customer.longitude) {
                    const lat = parseFloat(customer.latitude);
                    const lng = parseFloat(customer.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        this.map.setView([lat, lng], 17);
                        this.handleMapClickPinning({ lat: lat, lng: lng });
                        return;
                    }
                }

                if (customer.village_id) {
                    const v = this.villagesData.find(item => item.id == customer.village_id);
                    if (v && v.latitude && v.longitude) {
                        this.map.setView([parseFloat(v.latitude), parseFloat(v.longitude)], 16);
                        return;
                    }
                    const villageNeighbors = this.pinnedList.filter(c => c.village_id == customer.village_id);
                    if (villageNeighbors.length > 0) {
                        const first = villageNeighbors[0];
                        this.map.setView([parseFloat(first.latitude), parseFloat(first.longitude)], 16);
                        return;
                    }
                }
            },

            startPinning(customer) {
                this.cancelCableDrawing();
                this.pinningCustomer = customer;
                this.pendingCoordinate = null;
                if (this.temporaryMarker) {
                    this.map.removeLayer(this.temporaryMarker);
                    this.temporaryMarker = null;
                }

                this.centerOnCustomerVillage(customer);

                const mapEl = document.getElementById('map');
                if (mapEl) {
                    mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                if (window.lucide) {
                    setTimeout(() => window.lucide.createIcons(), 50);
                }
            },

            cancelPinning() {
                this.pinningCustomer = null;
                this.pendingCoordinate = null;
                if (this.temporaryMarker) {
                    this.map.removeLayer(this.temporaryMarker);
                    this.temporaryMarker = null;
                }
            },

            handleMapClickPinning(latlng) {
                this.pendingCoordinate = latlng;

                if (this.temporaryMarker) {
                    this.temporaryMarker.setLatLng(latlng);
                } else {
                    this.temporaryMarker = L.marker(latlng, {
                        draggable: true
                    }).addTo(this.map);

                    this.temporaryMarker.on('dragend', (e) => {
                        this.pendingCoordinate = e.target.getLatLng();
                    });
                }

                if (window.lucide) {
                    setTimeout(() => window.lucide.createIcons(), 50);
                }
            },

            locateMe() {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung deteksi lokasi GPS.');
                    return;
                }

                navigator.geolocation.getCurrentPosition((pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    this.map.setView([lat, lng], 17);

                    L.circleMarker([lat, lng], {
                        radius: 8,
                        color: '#059669',
                        fillColor: '#10B981',
                        fillOpacity: 0.8
                    }).addTo(this.map).bindPopup('📍 Anda sedang berada di sini!').openPopup();

                    if (this.pinningCustomer !== null) {
                        this.handleMapClickPinning({ lat: lat, lng: lng });
                    }
                }, (err) => {
                    alert('Gagal membaca GPS: ' + err.message + '. Pastikan izin lokasi aktif.');
                }, { enableHighAccuracy: true });
            },

            async saveLocation() {
                if (!this.pinningCustomer || !this.pendingCoordinate) return;

                this.saving = true;

                try {
                    const response = await fetch(`/maps/customers/${this.pinningCustomer.id}/location`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            latitude: this.pendingCoordinate.lat,
                            longitude: this.pendingCoordinate.lng
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        const updated = this.pinningCustomer;
                        updated.latitude = this.pendingCoordinate.lat;
                        updated.longitude = this.pendingCoordinate.lng;

                        if (!this.pinnedList.some(item => item.id == updated.id)) {
                            this.pinnedList.push(updated);
                            this.unpinnedList = this.unpinnedList.filter(item => item.id !== updated.id);
                        }
                        this.pinnedCount = this.pinnedList.length;
                        this.unpinnedCount = this.unpinnedList.length;

                        this.cancelPinning();
                        this.renderMarkers();

                        alert('Titik rumah ' + updated.name + ' berhasil disimpan!');
                    } else {
                        alert('Gagal menyimpan koordinat.');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan koneksi.');
                } finally {
                    this.saving = false;
                }
            }
        };
    }
</script>
@endsection
