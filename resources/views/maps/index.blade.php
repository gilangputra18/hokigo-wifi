@extends('layouts.app')

@section('title', 'Peta Jaringan & Penitikan Rumah Pelanggan')
@section('header_title', 'Peta Jaringan & Titik Pelanggan')

@section('content')
<div class="space-y-4" x-data="mapApp()">

    <!-- Top Stats & Filter Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Stats Summary -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-4">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-100 text-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                <span class="text-slate-600 font-medium">Total Pelanggan:</span>
                <span class="font-extrabold text-blue-900">{{ $allCustomers->count() }}</span>
            </div>

            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-100 text-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-slate-600 font-medium">Sudah Dititik:</span>
                <span class="font-extrabold text-emerald-800" x-text="pinnedCount">{{ $pinnedCustomers->count() }}</span>
            </div>

            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-100 text-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <span class="text-slate-600 font-medium">Belum Dititik:</span>
                <span class="font-extrabold text-amber-800" x-text="unpinnedCount">{{ $unpinnedCustomers->count() }}</span>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Filter Desa -->
            <select x-model="selectedVillage" @change="filterMarkers()" 
                    class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                <option value="">Semua Wilayah / Desa</option>
                @foreach($villages as $v)
                    <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->system_type === 'voucher' ? 'Voucher' : ($v->system_type === 'hybrid' ? 'Hybrid' : 'PPPoE') }})</option>
                @endforeach
            </select>

            <!-- Search Input -->
            <div class="relative">
                <input type="text" x-model="searchQuery" @input="filterMarkers()" placeholder="Cari nama / kode..." 
                       class="pl-8 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue w-36 sm:w-48">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
            </div>

            <!-- Button Titik Pelanggan Manual (Tanpa Harus ke Rumah Pelanggan) -->
            <button type="button" @click="openManualPinModal()" 
                    class="px-3.5 py-2 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-blue-600/25 transition"
                    title="Pilih pelanggan dan tentukan titik rumah langsung di peta satelit">
                <i data-lucide="map-pin" class="w-4 h-4"></i>
                <span>+ Titik Manual</span>
            </button>

            <!-- GPS Device Button (For Field Technicians) -->
            <button type="button" @click="locateMe()" 
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-xs transition"
                    title="Arahkan peta ke posisi GPS saya sekarang">
                <i data-lucide="crosshair" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Posisi Saya</span>
            </button>

            @if(auth()->user()->isAdmin())
                <!-- Reset Data Peta Button -->
                <button type="button" @click="resetMapModalOpen = true" 
                        class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-black text-rose-300 hover:text-rose-200 border border-rose-500/30 font-bold text-xs flex items-center gap-1.5 shadow-xs transition"
                        title="Kosongkan / Reset titik koordinat GPS di peta">
                    <i data-lucide="rotate-ccw" class="w-4 h-4 text-rose-400"></i>
                    <span>Reset Titik Peta</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Main Map Workspace Container -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

        <!-- Map View (Takes 3 columns on desktop, full on mobile) -->
        <div class="lg:col-span-3 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden relative">
            
            <!-- Map Layer Switcher / Mode Overlay Badge -->
            <div class="absolute top-3 left-3 z-[1000] flex items-center gap-2 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-xl shadow-md border border-slate-200 text-xs">
                <span class="font-bold text-slate-700">Tampilan:</span>
                <button type="button" @click="setLayer('satellite')" 
                        :class="currentLayer === 'satellite' ? 'bg-brand-blue text-white font-bold' : 'bg-slate-100 text-slate-600'"
                        class="px-2.5 py-1 rounded-lg text-[11px] transition">
                    🛰️ Satelit
                </button>
                <button type="button" @click="setLayer('street')" 
                        :class="currentLayer === 'street' ? 'bg-brand-blue text-white font-bold' : 'bg-slate-100 text-slate-600'"
                        class="px-2.5 py-1 rounded-lg text-[11px] transition">
                    🗺️ Jalan
                </button>
            </div>

            <!-- Floating Manual Pinning Control Card on Map (Always Visible on Mobile & PC) -->
            <div x-show="pinningCustomer !== null" x-cloak
                 class="absolute bottom-4 left-3 right-3 sm:left-auto sm:right-4 sm:max-w-md z-[1000] bg-white/95 backdrop-blur-md p-4 rounded-2xl sm:rounded-3xl shadow-2xl border-2 border-brand-blue space-y-3 animate-in fade-in slide-in-from-bottom-4 duration-200">
                
                <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-2.5">
                    <div>
                        <div class="flex items-center gap-1.5 text-xs font-extrabold text-brand-blue">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-blue animate-ping"></span>
                            <span>Mode Penitikan Manual Aktif</span>
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

            <!-- Map Footer Guide -->
            <div class="p-3 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 text-[11px] text-slate-500">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-blue-600 inline-block shadow-xs"></span> Rumah Pelanggan Aktif
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-amber-500 inline-block shadow-xs"></span> Isolir / Catatan
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-rose-500 inline-block shadow-xs"></span> Nonaktif
                    </span>
                </div>
                <span>*Klik pada marker rumah untuk melihat rute kabel, sandi PPPoE & petunjuk arah Google Maps</span>
            </div>
        </div>

        <!-- Sidebar List: Pelanggan Belum Dititik & Manajemen Penitikan -->
        <div class="lg:col-span-1 space-y-4">
            
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs p-4 sm:p-5 flex flex-col h-[520px] sm:h-[650px]">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-1.5">
                            <i data-lucide="map-pin-off" class="w-4 h-4 text-amber-500"></i>
                            <span>Belum Ada Titik GPS</span>
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Pilih pelanggan untuk penitikan</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold" x-text="unpinnedList.length"></span>
                </div>

                <!-- Unpinned Customer List -->
                <div class="flex-1 overflow-y-auto divide-y divide-slate-100 py-2 space-y-1">
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

                <!-- Quick Location Save Dialog (Modal/Box) -->
                <div x-show="pendingCoordinate !== null" x-cloak 
                     class="pt-3 border-t border-slate-100 bg-blue-50/80 -mx-4 -mb-4 p-4 rounded-b-2xl sm:rounded-b-3xl">
                    <p class="text-xs font-extrabold text-blue-900 mb-1">Simpan Titik Koordinat?</p>
                    <p class="text-[11px] text-blue-700 mb-2">
                        Pelanggan: <strong x-text="pinningCustomer?.name"></strong><br>
                        Lat: <span x-text="pendingCoordinate?.lat.toFixed(6)"></span>, 
                        Lng: <span x-text="pendingCoordinate?.lng.toFixed(6)"></span>
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="saveLocation()" :disabled="saving"
                                class="flex-1 py-2 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-xs transition">
                            <span x-show="!saving">Simpan Titik Ini</span>
                            <span x-show="saving">Menyimpan...</span>
                        </button>
                        <button type="button" @click="cancelPinning()" class="px-3 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition">
                            Batal
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Modal Penitikan Pelanggan Manual (Tanpa Harus ke Rumah Pelanggan) -->
    <div x-show="manualPinModalOpen" x-cloak 
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
        <div @click.away="manualPinModalOpen = false" 
             class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 relative my-auto flex flex-col max-h-[90vh] overflow-hidden">
            
            <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-white shrink-0">
                <div class="flex items-center gap-2.5 text-slate-900 font-extrabold text-base">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-brand-blue flex items-center justify-center shadow-xs">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Penitikan Pelanggan Manual</h3>
                        <p class="text-[11px] text-slate-400 font-medium">Titik rumah pelanggan yang sudah terpasang dari kantor/PC/HP</p>
                    </div>
                </div>
                <button type="button" @click="manualPinModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="overflow-y-auto p-5 space-y-4 flex-1">
                
                <!-- Pilih Pelanggan -->
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

                <!-- Pilihan Metode Titik -->
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-700">Pilih Cara Menitik Rumah:</p>

                    <!-- Metode 1: Klik Langsung di Peta Satelit -->
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

                    <!-- Metode 2: Masukkan Titik Koordinat Manual (Lat & Lng) -->
                    <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="map" class="w-4 h-4 text-emerald-600"></i>
                            <h4 class="font-extrabold text-xs text-slate-800">Atau Masukkan Koordinat Manual (Lat, Lng)</h4>
                        </div>
                        <p class="text-[11px] text-slate-500">Jika Anda punya titik koordinat dari Google Maps atau share loc WhatsApp:</p>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 mb-1">Latitude</label>
                                <input type="text" x-model="modalInputLat" placeholder="-7.536123"
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 mb-1">Longitude</label>
                                <input type="text" x-model="modalInputLng" placeholder="112.238456"
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono">
                            </div>
                        </div>

                        <button type="button" @click="saveManualCoordinatesFromModal()" :disabled="!modalSelectedCustomerId || !modalInputLat || !modalInputLng || saving"
                                class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-xs">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span x-text="saving ? 'Menyimpan...' : 'Simpan Koordinat Ini Langsung'"></span>
                        </button>
                    </div>
                </div>

            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 shrink-0 flex items-center justify-end">
                <button type="button" @click="manualPinModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    <!-- Modal Konfirmasi Reset Titik Peta & Pelanggan -->
    <div x-show="resetMapModalOpen" x-cloak 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md">
        <div @click.away="resetMapModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-rose-200 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-rose-100">
                <div class="flex items-center gap-2 text-rose-600 font-extrabold text-base">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    <span>Reset Data Titik Peta (0-kan Peta)</span>
                </div>
                <button type="button" @click="resetMapModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('maps.reset') }}" method="POST" class="space-y-4" x-data="{ confirmReset: '', actionChoice: 'coordinates_only' }">
                @csrf

                <div class="space-y-2.5">
                    <label class="block text-xs font-bold text-slate-700">Pilih Opsi Reset:</label>
                    
                    <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 hover:border-brand-blue cursor-pointer bg-slate-50/60 transition">
                        <input type="radio" name="action_type" value="coordinates_only" x-model="actionChoice" checked class="mt-0.5 text-brand-blue focus:ring-brand-blue">
                        <div class="text-xs">
                            <span class="font-extrabold text-slate-800 block">Kosongkan Titik Koordinat GPS Saja (0 Titik)</span>
                            <span class="text-slate-500 text-[11px] block mt-0.5">Semua marker rumah di peta dihapus. Data pelanggan tetap aman dan berpindah ke daftar 'Belum Dititik' untuk dititik ulang.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-3 rounded-xl border border-rose-200 hover:border-rose-400 cursor-pointer bg-rose-50/40 transition">
                        <input type="radio" name="action_type" value="delete_all_customers" x-model="actionChoice" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                        <div class="text-xs">
                            <span class="font-extrabold text-rose-700 block">Hapus Seluruh Data Pelanggan (0 Pelanggan)</span>
                            <span class="text-rose-600/80 text-[11px] block mt-0.5">Menghapus seluruh daftar pelanggan dari sistem sehingga Anda bisa input data pelanggan baru dari nol.</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Ketik kata <code class="bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded font-mono font-bold">RESET</code> untuk konfirmasi:
                    </label>
                    <input type="text" name="confirmation" x-model="confirmReset" required placeholder="Ketik RESET"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-rose-300 font-mono text-xs uppercase font-bold text-center focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="resetMapModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="confirmReset.toUpperCase() !== 'RESET'"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold shadow-md shadow-rose-600/30 transition flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>Eksekusi Reset</span>
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
            satelliteLayer: null,
            streetLayer: null,
            currentLayer: 'satellite',
            markersGroup: null,
            resetMapModalOpen: false,
            
            // Manual Pin Modal States
            manualPinModalOpen: false,
            modalSelectedCustomerId: '',
            modalCustomerSearch: '',
            modalInputLat: '',
            modalInputLng: '',
            
            allCustomersData: @json($allCustomers),
            pinnedList: @json($pinnedCustomers),
            unpinnedList: @json($unpinnedCustomers),
            
            pinnedCount: {{ $pinnedCustomers->count() }},
            unpinnedCount: {{ $unpinnedCustomers->count() }},
            
            selectedVillage: '',
            searchQuery: '',
            
            pinningCustomer: null,
            temporaryMarker: null,
            pendingCoordinate: null,
            saving: false,

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
                this.$nextTick(() => {
                    this.initMap();
                });
            },

            initMap() {
                // Determine center: either first pinned customer or default East Java / Indonesia coords
                let defaultLat = -7.536064;
                let defaultLng = 112.238402;
                let defaultZoom = 13;

                if (this.pinnedList.length > 0) {
                    defaultLat = parseFloat(this.pinnedList[0].latitude);
                    defaultLng = parseFloat(this.pinnedList[0].longitude);
                    defaultZoom = 15;
                }

                // Initialize Leaflet Map
                this.map = L.map('map', {
                    center: [defaultLat, defaultLng],
                    zoom: defaultZoom,
                    zoomControl: true
                });

                // Satellite Tile Layer (Esri World Imagery)
                this.satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                    maxZoom: 19
                });

                // Street Tile Layer (OpenStreetMap)
                this.streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                });

                // Default to Satellite view like in Image 3
                this.satelliteLayer.addTo(this.map);

                // Group for markers
                this.markersGroup = L.featureGroup().addTo(this.map);

                // Render all initial customer markers
                this.renderMarkers();

                // Click listener for pinning new customers or clicking an empty spot
                this.map.on('click', (e) => {
                    if (this.pinningCustomer !== null) {
                        this.handleMapClickPinning(e.latlng);
                    } else {
                        const lat = e.latlng.lat.toFixed(6);
                        const lng = e.latlng.lng.toFixed(6);
                        L.popup()
                            .setLatLng(e.latlng)
                            .setContent(`
                                <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; text-align: center; padding: 4px; min-width: 170px;">
                                    <div style="font-weight: 800; color: #0f172a; margin-bottom: 2px;">📍 Titik Lokasi Peta</div>
                                    <div style="font-family: monospace; font-size: 10px; color: #64748b; margin-bottom: 8px;">${lat}, ${lng}</div>
                                    <button type="button" onclick="window.mapAppInstance.openModalWithCoordinate(${lat}, ${lng})"
                                            style="background: #1055CC; color: white; border: none; padding: 6px 10px; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer; width: 100%;">
                                        + Titik Pelanggan di Sini
                                    </button>
                                </div>
                            `)
                            .openOn(this.map);
                    }
                });

                // Fit bounds if markers exist
                if (this.pinnedList.length > 0) {
                    try {
                        this.map.fitBounds(this.markersGroup.getBounds(), { padding: [50, 50], maxZoom: 16 });
                    } catch (e) {}
                }
            },

            setLayer(type) {
                this.currentLayer = type;
                if (type === 'satellite') {
                    this.map.removeLayer(this.streetLayer);
                    this.satelliteLayer.addTo(this.map);
                } else {
                    this.map.removeLayer(this.satelliteLayer);
                    this.streetLayer.addTo(this.map);
                }
            },

            createCustomIcon(customer) {
                let bgColor = '#1055CC'; // Active blue
                let ringColor = '#93c5fd';
                let iconSymbol = '📶';

                if (customer.status === 'isolated') {
                    bgColor = '#D97706'; // Amber
                    ringColor = '#fde68a';
                    iconSymbol = '⚠️';
                } else if (customer.status === 'inactive') {
                    bgColor = '#E11D48'; // Rose/Red
                    ringColor = '#fecdd3';
                    iconSymbol = '❌';
                }

                const html = `
                    <div style="position: relative; display: flex; flex-direction: column; align-items: center;">
                        <div style="background-color: ${bgColor}; border: 2.5px solid #ffffff; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.4); font-size: 15px; color: white;">
                            ${iconSymbol}
                        </div>
                        <div style="background: rgba(15, 23, 42, 0.88); color: #ffffff; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 6px; margin-top: 3px; white-space: nowrap; border: 1px solid rgba(255,255,255,0.3); text-shadow: 0 1px 2px rgba(0,0,0,0.8);">
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

            renderMarkers() {
                this.markersGroup.clearLayers();

                const filtered = this.pinnedList.filter(c => {
                    const matchVillage = !this.selectedVillage || c.village_id == this.selectedVillage;
                    const matchSearch = !this.searchQuery || 
                        c.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                        c.customer_code.toLowerCase().includes(this.searchQuery.toLowerCase());
                    return matchVillage && matchSearch;
                });

                filtered.forEach(c => {
                    const lat = parseFloat(c.latitude);
                    const lng = parseFloat(c.longitude);
                    if (isNaN(lat) || isNaN(lng)) return;

                    const marker = L.marker([lat, lng], {
                        icon: this.createCustomIcon(c)
                    });

                    // Build Rich Popup
                    const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
                    const customerUrl = `/customers/${c.id}`;
                    const waUrl = c.phone ? `https://api.whatsapp.com/send?phone=${c.clean_phone || c.phone}` : '#';
                    const villageName = c.village ? c.village.name : 'Tanpa Desa';
                    const packageName = c.package ? `${c.package.name} (${c.package.speed})` : 'Paket Standar';
                    const monthlyFee = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(c.monthly_fee || 0);

                    const photoHtml = c.house_photo 
                        ? `<div style="margin-bottom: 8px; border-radius: 8px; overflow: hidden; max-height: 110px; background: #0f172a;"><img src="${c.house_photo}" style="width: 100%; height: 100px; object-fit: cover;" alt="Foto Rumah"></div>`
                        : '';
                    const houseNumberHtml = c.house_number 
                        ? `<div>🏠 <strong>${c.house_number}</strong></div>`
                        : '';
                    const redamanHtml = c.redaman 
                        ? `<div>📶 Redaman: <strong style="color: #059669; background: #ecfdf5; padding: 1px 5px; border-radius: 4px; border: 1px solid #a7f3d0;">${c.redaman}</strong></div>`
                        : '';

                    const popupContent = `
                        <div style="font-family: 'Plus Jakarta Sans', sans-serif; min-width: 220px; max-width: 280px; padding: 4px;">
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
                                ${c.pppoe_password ? `<div>🔑 Sandi: <code style="background: #e2e8f0; padding: 1px 4px; border-radius: 4px; font-weight: 600;">${c.pppoe_password}</code></div>` : ''}
                                ${c.notes ? `<div style="margin-top: 4px; padding: 4px 6px; background: #fef3c7; border-left: 3px solid #f59e0b; border-radius: 4px; color: #92400e; font-size: 10px;">📝 ${c.notes}</div>` : ''}
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <a href="${googleMapsUrl}" target="_blank" 
                                   style="display: flex; align-items: center; justify-content: center; gap: 4px; background: #059669; color: white; padding: 6px 10px; border-radius: 8px; font-weight: 700; font-size: 11px; text-decoration: none;">
                                    🗺️ Buka Navigasi Google Maps
                                </a>
                                <div style="display: flex; gap: 4px;">
                                    <a href="${customerUrl}" 
                                       style="flex: 1; text-align: center; background: #1055CC; color: white; padding: 5px 8px; border-radius: 8px; font-weight: 700; font-size: 11px; text-decoration: none;">
                                        Lihat Profil
                                    </a>
                                    <a href="${waUrl}" target="_blank" 
                                       style="flex: 1; text-align: center; background: #22c55e; color: white; padding: 5px 8px; border-radius: 8px; font-weight: 700; font-size: 11px; text-decoration: none;">
                                        Chat WA
                                    </a>
                                </div>
                                <button type="button" onclick="window.mapAppInstance.startRepositionCustomer(${c.id})" 
                                        style="width: 100%; margin-top: 2px; display: flex; align-items: center; justify-content: center; gap: 4px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 5px 8px; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer;">
                                    📍 Geser / Ubah Titik Rumah
                                </button>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupContent);
                    this.markersGroup.addLayer(marker);
                });
            },

            filterMarkers() {
                this.renderMarkers();
            },

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

            async saveManualCoordinatesFromModal() {
                if (!this.modalSelectedCustomerId) {
                    alert('Pilih pelanggan terlebih dahulu.');
                    return;
                }
                const lat = parseFloat(this.modalInputLat);
                const lng = parseFloat(this.modalInputLng);
                if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                    alert('Nilai Latitude (-90 s/d 90) dan Longitude (-180 s/d 180) tidak valid.');
                    return;
                }

                const customer = this.allCustomersData.find(c => c.id == this.modalSelectedCustomerId);
                if (!customer) return;

                this.saving = true;
                try {
                    const response = await fetch(`/maps/customers/${customer.id}/location`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ latitude: lat, longitude: lng })
                    });
                    const data = await response.json();
                    if (data.success) {
                        customer.latitude = lat;
                        customer.longitude = lng;

                        if (!this.pinnedList.some(item => item.id == customer.id)) {
                            this.pinnedList.push(customer);
                            this.unpinnedList = this.unpinnedList.filter(item => item.id != customer.id);
                        }
                        this.pinnedCount = this.pinnedList.length;
                        this.unpinnedCount = this.unpinnedList.length;

                        this.manualPinModalOpen = false;
                        this.renderMarkers();
                        this.map.setView([lat, lng], 17);

                        alert('Titik koordinat rumah ' + customer.name + ' berhasil disimpan!');
                    } else {
                        alert('Gagal menyimpan koordinat.');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan koneksi.');
                } finally {
                    this.saving = false;
                }
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

                // 1. If customer already has coordinates, zoom to it
                if (customer.latitude && customer.longitude) {
                    const lat = parseFloat(customer.latitude);
                    const lng = parseFloat(customer.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        this.map.setView([lat, lng], 17);
                        this.handleMapClickPinning({ lat: lat, lng: lng });
                        return;
                    }
                }

                // 2. Look for existing neighbor pins in the same village
                if (customer.village_id) {
                    const villageNeighbors = this.pinnedList.filter(c => c.village_id == customer.village_id);
                    if (villageNeighbors.length > 0) {
                        const first = villageNeighbors[0];
                        this.map.setView([parseFloat(first.latitude), parseFloat(first.longitude)], 16);
                        return;
                    }
                }
            },

            startPinning(customer) {
                this.pinningCustomer = customer;
                this.pendingCoordinate = null;
                if (this.temporaryMarker) {
                    this.map.removeLayer(this.temporaryMarker);
                    this.temporaryMarker = null;
                }

                this.centerOnCustomerVillage(customer);

                // Auto-scroll to map smoothly on mobile so user sees the map canvas immediately
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
                        // Move customer from unpinned to pinned
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
