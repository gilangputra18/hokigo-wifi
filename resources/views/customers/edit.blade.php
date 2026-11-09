@extends('layouts.app')

@section('title', 'Edit Pelanggan - ' . $customer->name)
@section('header_title', 'Edit Pelanggan: ' . $customer->name)

@push('styles')
    <!-- Leaflet Maps CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
        
        <div class="flex items-center justify-between pb-6 border-b border-slate-100 mb-6">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Perbarui Data Pelanggan</h2>
                <p class="text-xs text-slate-500 mt-1">Ubah identitas, nomor WhatsApp, desa, paket, atau catatan teknis pelanggan</p>
            </div>
            <a href="{{ route('customers.show', $customer) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
        </div>

        <form action="{{ route('customers.update', $customer) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Identitas -->
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-brand-blue"></span> 1. Identitas Pelanggan
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Pelanggan (ID) *</label>
                        <input type="text" name="customer_code" value="{{ old('customer_code', $customer->customer_code) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Pelanggan *</label>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Aktif *</label>
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok Desa / Wilayah *</label>
                        <select name="village_id" id="customer_village_select" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            @foreach($villages as $vil)
                                <option value="{{ $vil->id }}" {{ old('village_id', $customer->village_id) == $vil->id ? 'selected' : '' }}
                                        data-lat="{{ $vil->latitude }}" data-lng="{{ $vil->longitude }}" data-zoom="{{ $vil->zoom_level ?? 16 }}">
                                    {{ $vil->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- No. Rumah / Gang / Blok (Requirement Pemasangan) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Rumah / Gang / Blok</label>
                        <input type="text" name="house_number" value="{{ old('house_number', $customer->house_number) }}" placeholder="Contoh: No. 14 / Gang Mawar / Blok B-05"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <!-- Alamat / Dusun / RT RW -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap / Dusun / RT RW</label>
                        <input type="text" name="address" value="{{ old('address', $customer->address) }}" placeholder="Contoh: RT 03 RW 01, Dusun Krajan"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>
            </div>

            <!-- Section 2: Paket & Biaya -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 2. Paket Internet & Pengaturan Tagihan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Paket Internet *</label>
                        <select name="package_id" id="package_select" required onchange="updatePrice()"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" data-price="{{ $pkg->price }}" {{ old('package_id', $customer->package_id) == $pkg->id ? 'selected' : '' }}>
                                    {{ $pkg->name }} ({{ $pkg->speed }}) - Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Bulanan (Rp) *</label>
                        <input type="number" name="monthly_fee" id="monthly_fee" value="{{ old('monthly_fee', $customer->monthly_fee) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tgl Jatuh Tempo (Setiap Bulan) *</label>
                        <select name="due_date_day" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            @for($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" {{ old('due_date_day', $customer->due_date_day) == $d ? 'selected' : '' }}>
                                    Setiap Tanggal {{ $d }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Teknis -->
            <div class="pt-4 border-t border-slate-100" x-data="customerPppoeHelper()">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 3. Data Teknis / Perangkat
                    </h3>
                    <a href="{{ route('pppoe.index') }}" target="_blank" 
                       class="text-[11px] font-bold text-brand-blue hover:underline flex items-center gap-1">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Katalog Akun PPPoE &rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Model Router (ONT)</label>
                        <input type="text" name="router_model" value="{{ old('router_model', $customer->router_model) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Redaman (dBm)</label>
                        <input type="text" name="redaman" value="{{ old('redaman', $customer->redaman) }}" placeholder="Contoh: -18.5 dBm"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <p class="text-[10px] text-slate-400 mt-1">Standar GPON: -16 s/d -24 dBm</p>
                    </div>

                    <!-- Username PPPoE with Available Account Picker -->
                    <div class="relative">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Username PPPoE</label>
                            <button type="button" @click="pickerOpen = true" 
                                    class="text-[10px] font-bold text-brand-blue hover:text-blue-700 hover:underline flex items-center gap-1">
                                <i data-lucide="search" class="w-2.5 h-2.5"></i>
                                <span>Pilih PPPoE Desa</span>
                            </button>
                        </div>
                        <input type="text" name="pppoe_user" id="pppoe_user_input" x-model="pppoeUser" 
                               placeholder="Contoh: Hokigo40"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        
                        <!-- Quick Suggestions Chips for Available PPPoE in Selected Village -->
                        <div class="mt-1.5 space-y-1">
                            <template x-if="loading">
                                <span class="text-[10px] text-slate-400 flex items-center gap-1">
                                    <svg class="animate-spin w-3 h-3 text-brand-blue" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    Memeriksa PPPoE tersedia...
                                </span>
                            </template>
                            <template x-if="!loading && availablePppoe.length > 0">
                                <div class="flex flex-wrap items-center gap-1 text-[11px]">
                                    <span class="text-slate-400 text-[10px]">Tersedia:</span>
                                    <template x-for="item in availablePppoe.slice(0, 4)" :key="item.id">
                                        <button type="button" @click="selectPppoe(item)" 
                                                class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold transition border"
                                                :class="item.username === pppoeUser ? 'bg-blue-100 text-blue-900 border-blue-300' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border-emerald-200'"
                                                :title="'Pilih ' + item.username">
                                            <span x-text="item.username"></span>
                                        </button>
                                    </template>
                                    <button type="button" @click="pickerOpen = true" class="text-[10px] text-brand-blue font-bold hover:underline pl-0.5">
                                        +<span x-text="availablePppoe.length"></span> akun
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Sandi PPPoE -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sandi PPPoE</label>
                        <input type="text" name="pppoe_password" id="pppoe_password_input" x-model="pppoePassword" 
                               placeholder="Contoh: Irzaq09042003"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <!-- WiFi SSID -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                            <span class="text-brand-blue font-extrabold">📶</span>
                            <span>Nama WiFi (SSID)</span>
                        </label>
                        <input type="text" name="wifi_ssid" value="{{ old('wifi_ssid', $customer->wifi_ssid) }}" placeholder="Contoh: HOKIGO_WARUNG"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <p class="text-[10px] text-slate-400 mt-1">Nama jaringan WiFi di HP pelanggan</p>
                    </div>

                    <!-- WiFi Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                            <span class="text-amber-500 font-extrabold">🔑</span>
                            <span>Sandi WiFi (Password)</span>
                        </label>
                        <input type="text" name="wifi_password" value="{{ old('wifi_password', $customer->wifi_password) }}" placeholder="Contoh: 12345678"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <p class="text-[10px] text-slate-400 mt-1">Password untuk konek ke WiFi</p>
                    </div>
                </div>

                <!-- Modal Picker PPPoE Popover -->
                <div x-show="pickerOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div @click.away="pickerOpen = false" class="bg-white rounded-3xl max-w-md w-full p-5 shadow-2xl border border-slate-100 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <h4 class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                                    <i data-lucide="key-round" class="w-4 h-4 text-brand-blue"></i>
                                    <span>Pilih Akun PPPoE Tersedia</span>
                                </h4>
                                <p class="text-[11px] text-slate-500">Khusus desa pelanggan ini</p>
                            </div>
                            <button type="button" @click="pickerOpen = false" class="text-slate-400 hover:text-slate-600">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div>
                            <input type="text" x-model="pickerSearch" placeholder="Ketik nomor / username..." 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>

                        <div class="max-h-60 overflow-y-auto divide-y divide-slate-100 pr-1">
                            <template x-for="item in filteredAccounts()" :key="item.id">
                                <div class="py-2.5 px-3 hover:bg-blue-50/60 rounded-xl cursor-pointer flex items-center justify-between transition group"
                                     @click="selectPppoe(item)">
                                    <div>
                                        <div class="font-mono text-xs font-extrabold text-slate-900 group-hover:text-brand-blue" x-text="item.username"></div>
                                        <div class="text-[10px] text-slate-400 font-mono" x-text="'Sandi: ' + item.password"></div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                          :class="item.username === pppoeUser ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800'">
                                        <span x-text="item.username === pppoeUser ? 'Akun Saat Ini' : 'Pilih &rarr;'"></span>
                                    </span>
                                </div>
                            </template>
                            <div x-show="filteredAccounts().length === 0" class="py-6 text-center text-xs text-slate-400">
                                Tidak ada akun PPPoE tersedia di desa ini.<br>
                                <span class="text-[10px]">Silakan pilih desa di formulir atau tambah akun baru di menu PPPoE.</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <a href="{{ route('pppoe.index') }}" target="_blank" class="text-[11px] text-brand-blue font-bold hover:underline">
                                Buka Menu PPPoE &rarr;
                            </a>
                            <button type="button" @click="pickerOpen = false" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Section 4: Catatan Khusus & Foto Rumah Pelanggan -->
            <div class="pt-4 border-t border-slate-100" x-data="photoUploader('{{ $customer->house_photo }}')">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> 4. Catatan Keterangan & Foto Rumah Pelanggan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left: Catatan Lapangan & Status -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Keterangan Pemasangan & Note Lapangan
                            </label>
                            <textarea name="notes" rows="4" 
                                      placeholder="Tuliskan keterangan: posisi tiang ODP, nomor port ODP, rute & panjang kabel dropcore, letak penempatan router ONT..."
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue bg-amber-50/30">{{ old('notes', $customer->notes) }}</textarea>
                            <p class="text-[10px] text-slate-400 mt-1">Keterangan ini tampil langsung di peta dan kartu pelanggan untuk teknisi.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pemasangan</label>
                                <input type="date" name="installation_date" value="{{ old('installation_date', $customer->installation_date ? $customer->installation_date->format('Y-m-d') : '') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Pelanggan</label>
                                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                                    <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="isolated" {{ old('status', $customer->status) === 'isolated' ? 'selected' : '' }}>Isolir</option>
                                    <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Foto Rumah Pelanggan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Foto Rumah / Lokasi Pelanggan
                        </label>

                        <!-- Hidden base64 input -->
                        <input type="hidden" name="house_photo" :value="photoData">
                        <input type="file" id="camera_input" accept="image/*" capture="environment" class="hidden" @change="handleFileSelect($event)">
                        <input type="file" id="gallery_input" accept="image/*" class="hidden" @change="handleFileSelect($event)">

                        <div class="border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center hover:border-brand-blue/50 transition bg-slate-50/60">
                            <template x-if="!photoData">
                                <div class="py-4 space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-brand-blue/10 text-brand-blue flex items-center justify-center mx-auto">
                                        <i data-lucide="camera" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-700">Ambil Foto Rumah Pelanggan</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Jepret langsung via kamera smartphone teknisi atau galeri</p>
                                    </div>
                                    <div class="flex items-center justify-center gap-2 pt-2">
                                        <button type="button" @click="document.getElementById('camera_input').click()" 
                                                class="px-3.5 py-2 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                                            <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                                            <span>Buka Kamera HP</span>
                                        </button>
                                        <button type="button" @click="document.getElementById('gallery_input').click()" 
                                                class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                            <span>Pilih Galeri</span>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="photoData">
                                <div class="space-y-3">
                                    <div class="relative rounded-xl overflow-hidden max-h-56 bg-slate-900 border border-slate-200">
                                        <img :src="photoData" alt="Foto Rumah Pelanggan" class="w-full h-48 object-cover mx-auto">
                                        <div class="absolute bottom-2 right-2 bg-black/60 backdrop-blur-sm text-white text-[10px] px-2 py-0.5 rounded-md font-mono">
                                            Foto Terpilih
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="document.getElementById('camera_input').click()" 
                                                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1">
                                            <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                                            <span>Ganti Foto</span>
                                        </button>
                                        <button type="button" @click="removePhoto()" 
                                                class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition flex items-center gap-1">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5">Foto otomatis dioptimalkan agar ringan dibuka teknisi di lapangan & disimpan permanen di database.</p>
                    </div>
                </div>
            </div>

            <!-- Section 5: Penitikan Titik Rumah Pelanggan (Peta GPS & Satelit) -->
            <div class="pt-4 border-t border-slate-100" x-data="customerMapPicker()">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 5. Titik Lokasi Rumah Pelanggan (Peta Satelit)
                    </h3>
                    
                    <button type="button" @click="useCurrentGps()" 
                            class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-xs transition">
                        <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                        <span>📍 Gunakan Lokasi GPS Saya Saat Ini</span>
                    </button>
                </div>

                <p class="text-[11px] text-slate-500 mb-3">
                    Klik dan geser pin di peta di bawah ini untuk memperbarui posisi akurat rumah pelanggan, atau gunakan tombol GPS saat berada di lokasi. Anda juga bisa memilih mode tampilan peta (Google Satelit, Esri Satelit, Jalan).
                </p>

                <!-- Map Container with 3-Model Layer Switcher -->
                <div class="relative w-full h-80 sm:h-96 rounded-2xl border border-slate-200 overflow-hidden mb-3 shadow-inner">
                    
                    <!-- Floating Map Layer Switcher Pill (3 Model Sesuai Referensi) -->
                    <div class="absolute top-2.5 left-2.5 sm:top-3 sm:left-3 z-[400] flex items-center gap-1 bg-white/95 backdrop-blur-md p-1 rounded-2xl shadow-lg border border-slate-200 text-xs select-none max-w-[calc(100%-20px)] overflow-x-auto">
                        <button type="button" @click="setLayer('google')" 
                                :class="currentLayer === 'google' ? 'bg-brand-blue text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-slate-100 font-semibold'"
                                class="px-2.5 sm:px-3 py-1.5 rounded-xl text-[10px] sm:text-[11px] transition flex items-center gap-1 shrink-0 whitespace-nowrap">
                            <span>🛰️</span> <span class="hidden sm:inline">Google Satelit</span><span class="sm:hidden">Google</span>
                        </button>
                        <button type="button" @click="setLayer('esri')" 
                                :class="currentLayer === 'esri' ? 'bg-brand-blue text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-slate-100 font-semibold'"
                                class="px-2.5 sm:px-3 py-1.5 rounded-xl text-[10px] sm:text-[11px] transition flex items-center gap-1 shrink-0 whitespace-nowrap">
                            <span>🛰️</span> <span class="hidden sm:inline">Esri Satelit</span><span class="sm:hidden">Esri</span>
                        </button>
                        <button type="button" @click="setLayer('street')" 
                                :class="currentLayer === 'street' ? 'bg-brand-blue text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-slate-100 font-semibold'"
                                class="px-2.5 sm:px-3 py-1.5 rounded-xl text-[10px] sm:text-[11px] transition flex items-center gap-1 shrink-0 whitespace-nowrap">
                            <span>🗺️</span> <span>Jalan</span>
                        </button>
                    </div>

                    <!-- Coordinates readout badge -->
                    <div class="absolute bottom-3 left-3 z-[400] bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-mono px-3 py-1.5 rounded-xl shadow border border-white/10 flex items-center gap-1.5"
                         x-show="lat && lng">
                        <span>📍</span> <span x-text="lat"></span>, <span x-text="lng"></span>
                    </div>

                    <!-- Map Canvas -->
                    <div id="customer-picker-map" class="w-full h-full relative z-0"></div>
                </div>

                <!-- Lat & Lng Input Values -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Latitude (Garis Lintang)</label>
                        <input type="text" name="latitude" id="input_latitude" x-model="lat" @input="onManualCoordChange()" placeholder="-7.536064"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Longitude (Garis Bujur)</label>
                        <input type="text" name="longitude" id="input_longitude" x-model="lng" @input="onManualCoordChange()" placeholder="112.238402"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('customers.show', $customer) }}" class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>

        </form>

    </div>

</div>

<script>
    function updatePrice() {
        const select = document.getElementById('package_select');
        const selected = select.options[select.selectedIndex];
        const price = selected.getAttribute('data-price');
        if (price) {
            document.getElementById('monthly_fee').value = Math.round(price);
        }
    }

    function customerMapPicker() {
        return {
            map: null,
            marker: null,
            currentLayer: localStorage.getItem('hokigo_picker_map_layer') || 'google',
            googleHybridLayer: null,
            esriSatelliteLayer: null,
            streetLayer: null,
            lat: '{{ old('latitude', $customer->latitude ?? '') }}',
            lng: '{{ old('longitude', $customer->longitude ?? '') }}',

            init() {
                const tryInit = () => {
                    if (window.L && typeof window.L.map === 'function') {
                        this.initPicker();
                    } else {
                        setTimeout(tryInit, 100);
                    }
                };
                this.$nextTick(() => {
                    tryInit();
                });
            },

            initPicker() {
                let initLat = this.lat ? parseFloat(this.lat) : -7.536064;
                let initLng = this.lng ? parseFloat(this.lng) : 112.238402;
                let hasInitial = Boolean(this.lat && this.lng);

                this.map = L.map('customer-picker-map', {
                    center: [initLat, initLng],
                    zoom: hasInitial ? 17 : 14,
                    zoomControl: false,
                    preferCanvas: true
                });

                // Zoom control positioned at bottom right so it doesn't block top pill
                L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                // Model 1: Google Hybrid Satelit
                this.googleHybridLayer = L.tileLayer('https://mt{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                    subdomains: ['0', '1', '2', '3'],
                    maxZoom: 20,
                    attribution: '&copy; Google Maps',
                    updateWhenIdle: true
                });

                // Model 2: Esri World Imagery (Satelit)
                this.esriSatelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri',
                    maxNativeZoom: 18,
                    maxZoom: 20,
                    updateWhenIdle: true
                });

                // Model 3: OpenStreetMap (Jalan)
                this.streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19,
                    updateWhenIdle: true
                });

                // Apply initial selected layer
                this.setLayer(this.currentLayer);

                setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 200);
                setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 500);

                if (hasInitial) {
                    this.createOrUpdateMarker(initLat, initLng);
                }

                // Map Click Listener
                this.map.on('click', (e) => {
                    const pos = e.latlng;
                    this.lat = pos.lat.toFixed(6);
                    this.lng = pos.lng.toFixed(6);
                    this.createOrUpdateMarker(pos.lat, pos.lng);
                });

                // Listen to Village selector change to auto fly to village if no pin yet
                const villageSelect = document.getElementById('customer_village_select') || document.querySelector('select[name="village_id"]');
                if (villageSelect) {
                    villageSelect.addEventListener('change', (e) => {
                        const opt = e.target.selectedOptions[0];
                        if (opt) {
                            const vLat = opt.getAttribute('data-lat');
                            const vLng = opt.getAttribute('data-lng');
                            const vZoom = opt.getAttribute('data-zoom') || 16;
                            if (vLat && vLng && !this.lat && !this.lng) {
                                this.map.flyTo([parseFloat(vLat), parseFloat(vLng)], parseInt(vZoom), { duration: 1.2 });
                            }
                        }
                    });
                }
            },

            setLayer(type) {
                this.currentLayer = type;
                try {
                    localStorage.setItem('hokigo_picker_map_layer', type);
                } catch(e) {}

                if (this.googleHybridLayer && this.map.hasLayer(this.googleHybridLayer)) {
                    this.map.removeLayer(this.googleHybridLayer);
                }
                if (this.esriSatelliteLayer && this.map.hasLayer(this.esriSatelliteLayer)) {
                    this.map.removeLayer(this.esriSatelliteLayer);
                }
                if (this.streetLayer && this.map.hasLayer(this.streetLayer)) {
                    this.map.removeLayer(this.streetLayer);
                }

                if (type === 'google') {
                    this.googleHybridLayer.addTo(this.map);
                } else if (type === 'esri') {
                    this.esriSatelliteLayer.addTo(this.map);
                } else if (type === 'street') {
                    this.streetLayer.addTo(this.map);
                }
            },

            createOrUpdateMarker(lat, lng) {
                if (this.marker) {
                    this.marker.setLatLng([lat, lng]);
                } else {
                    this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                    this.marker.on('dragend', (ev) => {
                        const p = ev.target.getLatLng();
                        this.lat = p.lat.toFixed(6);
                        this.lng = p.lng.toFixed(6);
                    });
                }
            },

            onManualCoordChange() {
                let latVal = parseFloat(this.lat);
                let lngVal = parseFloat(this.lng);
                if (!isNaN(latVal) && !isNaN(lngVal) && latVal >= -90 && latVal <= 90 && lngVal >= -180 && lngVal <= 180) {
                    this.createOrUpdateMarker(latVal, lngVal);
                    this.map.panTo([latVal, lngVal]);
                }
            },

            useCurrentGps() {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung deteksi lokasi GPS.');
                    return;
                }

                navigator.geolocation.getCurrentPosition((pos) => {
                    const pLat = pos.coords.latitude;
                    const pLng = pos.coords.longitude;
                    this.lat = pLat.toFixed(6);
                    this.lng = pLng.toFixed(6);

                    this.map.setView([pLat, pLng], 18);
                    this.createOrUpdateMarker(pLat, pLng);

                    alert('📍 Posisi GPS Anda berhasil diambil!');
                }, (err) => {
                    alert('Gagal mendeteksi GPS: ' + err.message + '. Pastikan izin GPS aktif di browser HP.');
                }, { enableHighAccuracy: true });
            }
        };
    }

    function photoUploader(initialPhoto = '') {
        return {
            photoData: initialPhoto || '{{ old('house_photo', '') }}',
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;
                        const maxDim = 1024;

                        if (width > maxDim || height > maxDim) {
                            if (width > height) {
                                height = Math.round((height * maxDim) / width);
                                width = maxDim;
                            } else {
                                width = Math.round((width * maxDim) / height);
                                height = maxDim;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        // Compress to efficient JPEG (~70-90KB)
                        this.photoData = canvas.toDataURL('image/jpeg', 0.82);
                        if (window.lucide) {
                            setTimeout(() => window.lucide.createIcons(), 50);
                        }
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            removePhoto() {
                this.photoData = '';
                const cam = document.getElementById('camera_input');
                const gal = document.getElementById('gallery_input');
                if (cam) cam.value = '';
                if (gal) gal.value = '';
            }
        };
    }

    function customerPppoeHelper() {
        return {
            pppoeUser: @json(old('pppoe_user', $customer->pppoe_user ?? '')),
            pppoePassword: @json(old('pppoe_password', $customer->pppoe_password ?? '')),
            availablePppoe: [],
            loading: false,
            pickerOpen: false,
            pickerSearch: '',
            selectedVillageId: '{{ $customer->village_id }}',
            currentUsername: '{{ $customer->pppoe_user }}',

            init() {
                const villageSelect = document.getElementById('customer_village_select') || document.querySelector('select[name="village_id"]');
                if (villageSelect) {
                    this.selectedVillageId = villageSelect.value || '{{ $customer->village_id }}';
                    if (this.selectedVillageId) {
                        this.fetchAvailable();
                    }
                    villageSelect.addEventListener('change', (e) => {
                        this.selectedVillageId = e.target.value;
                        this.fetchAvailable();
                    });
                } else if (this.selectedVillageId) {
                    this.fetchAvailable();
                }
            },

            fetchAvailable() {
                if (!this.selectedVillageId) {
                    this.availablePppoe = [];
                    return;
                }
                this.loading = true;
                fetch(`{{ route('pppoe.available') }}?village_id=${this.selectedVillageId}&current_username=${encodeURIComponent(this.currentUsername)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.availablePppoe = Array.isArray(data) ? data : [];
                        this.loading = false;
                        if (window.lucide) {
                            setTimeout(() => window.lucide.createIcons(), 50);
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            selectPppoe(item) {
                this.pppoeUser = item.username;
                if (item.password) {
                    this.pppoePassword = item.password;
                }
                this.pickerOpen = false;
            },

            filteredAccounts() {
                if (!this.pickerSearch) return this.availablePppoe;
                const q = this.pickerSearch.toLowerCase();
                return this.availablePppoe.filter(item => item.username.toLowerCase().includes(q));
            }
        };
    }
</script>
@endsection
