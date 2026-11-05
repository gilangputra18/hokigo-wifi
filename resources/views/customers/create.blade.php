@extends('layouts.app')

@section('title', 'Tambah Pelanggan Baru')
@section('header_title', 'Registrasi Pelanggan Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
        
        <div class="flex items-center justify-between pb-6 border-b border-slate-100 mb-6">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Pemasangan Baru</h2>
                <p class="text-xs text-slate-500 mt-1">Lengkapi data pelanggan, paket langganan, desa dan catatan penarikan kabel</p>
            </div>
            <a href="{{ route('customers.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
        </div>

        <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Identitas Pelanggan -->
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-brand-blue"></span> 1. Identitas Pelanggan
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Kode Pelanggan (Otomatis Nambah Sendiri) -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Kode Pelanggan (ID) *</label>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold flex items-center gap-1">
                                <i data-lucide="sparkles" class="w-3 h-3 text-emerald-600"></i>
                                <span>Otomatis Nambah</span>
                            </span>
                        </div>
                        <div class="relative">
                            <input type="text" name="customer_code" id="customer_code_input" 
                                   value="{{ old('customer_code', $suggestedCode) }}" required
                                   class="w-full pl-3.5 pr-28 py-2.5 rounded-xl border border-slate-200 font-mono text-xs font-bold text-slate-900 bg-emerald-50/20 focus:bg-white focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <button type="button" onclick="document.getElementById('customer_code_input').value = '{{ $suggestedCode }}'"
                                    class="absolute right-1.5 top-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-[10px] font-extrabold transition flex items-center gap-1"
                                    title="Klik untuk reset ke kode otomatis">
                                <span>Auto: {{ $suggestedCode }}</span>
                            </button>
                        </div>
                        <p class="text-[10px] text-emerald-700 font-medium mt-1">
                            ✓ ID ini otomatis bertambah sendiri berurutan (contoh: HKG-001, HKG-002, HKG-003, HKG-004 dst). Anda tidak perlu mengetik manual.
                        </p>
                    </div>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Pelanggan *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Pak Budi Santoso"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <!-- WhatsApp / Telepon -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Aktif *</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-xs text-slate-400 font-mono font-bold">+62 / 08</span>
                            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="081234567890"
                                   class="w-full pl-20 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Pesan tagihan akan dikirimkan otomatis ke nomor ini.</p>
                    </div>

                    <!-- Desa / Wilayah (Requirement 1) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok Desa / Wilayah *</label>
                        <select name="village_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="">-- Pilih Desa --</option>
                            @foreach($villages as $vil)
                                <option value="{{ $vil->id }}" {{ old('village_id') == $vil->id ? 'selected' : '' }}>
                                    {{ $vil->name }} {{ $vil->district ? '('.$vil->district.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- No Rumah / Gang / Blok (Requirement Pemasangan) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Rumah / Gang / Blok</label>
                        <input type="text" name="house_number" value="{{ old('house_number') }}" placeholder="Contoh: No. 14 / Gang Mawar / Blok B-05"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <!-- Alamat / Dusun / RT RW -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap / Dusun / RT RW</label>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Contoh: RT 03 RW 01, Dusun Krajan"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>
            </div>

            <!-- Section 2: Paket & Penagihan (Billing) -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 2. Paket Internet & Pengaturan Tagihan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Paket Internet -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Paket Internet *</label>
                        <select name="package_id" id="package_select" required onchange="updatePrice()"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="">-- Pilih Paket --</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" data-price="{{ $pkg->price }}" {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                                    {{ $pkg->name }} ({{ $pkg->speed }}) - Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Biaya Bulanan (Bisa Custom) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Bulanan (Rp) *</label>
                        <input type="number" name="monthly_fee" id="monthly_fee" value="{{ old('monthly_fee', 150000) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <!-- Tanggal Jatuh Tempo Setiap Bulan (Requirement 2) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tgl Jatuh Tempo (Setiap Bulan) *</label>
                        <select name="due_date_day" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            @for($d = 1; $d <= 28; $d++)
                                <option value="{{ $d }}" {{ old('due_date_day', 10) == $d ? 'selected' : '' }}>
                                    Setiap Tanggal {{ $d }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Data Teknis & Jaringan -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 3. Data Teknis / Perangkat
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe / Model Router (ONT)</label>
                        <input type="text" name="router_model" value="{{ old('router_model', 'ZTE F609') }}" placeholder="Contoh: ZTE F609 / Huawei"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Redaman (dBm)</label>
                        <input type="text" name="redaman" value="{{ old('redaman') }}" placeholder="Contoh: -18.5 dBm"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <p class="text-[10px] text-slate-400 mt-1">Standar GPON: -16 s/d -24 dBm</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username PPPoE (Opsional)</label>
                        <input type="text" name="pppoe_user" value="{{ old('pppoe_user') }}" placeholder="user_pppoe"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sandi PPPoE</label>
                        <input type="text" name="pppoe_password" value="{{ old('pppoe_password') }}" placeholder="Contoh: 12345 / rahasia"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                            <span class="text-brand-blue font-extrabold">📶</span>
                            <span>Nama WiFi (SSID)</span>
                        </label>
                        <input type="text" name="wifi_ssid" value="{{ old('wifi_ssid') }}" placeholder="Contoh: HOKIGO_WARUNG"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <p class="text-[10px] text-slate-400 mt-1">Nama jaringan WiFi di HP pelanggan</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                            <span class="text-amber-500 font-extrabold">🔑</span>
                            <span>Sandi WiFi (Password)</span>
                        </label>
                        <input type="text" name="wifi_password" value="{{ old('wifi_password') }}" placeholder="Contoh: 12345678"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <p class="text-[10px] text-slate-400 mt-1">Password untuk konek ke WiFi</p>
                    </div>
                </div>
            </div>

            <!-- Section 4: Catatan Khusus & Foto Rumah Pelanggan -->
            <div class="pt-4 border-t border-slate-100" x-data="photoUploader()">
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
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue bg-amber-50/30">{{ old('notes') }}</textarea>
                            <p class="text-[10px] text-slate-400 mt-1">Keterangan ini tampil langsung di peta dan kartu pelanggan untuk teknisi.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pemasangan</label>
                                <input type="date" name="installation_date" value="{{ old('installation_date', date('Y-m-d')) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Awal</label>
                                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="isolated" {{ old('status') === 'isolated' ? 'selected' : '' }}>Isolir</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
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
                    Teknisi dapat langsung klik tombol <strong>"Gunakan Lokasi GPS Saya Saat Ini"</strong> saat berdiri di rumah pelanggan, atau klik & geser pin di peta satelit di bawah ini.
                </p>

                <!-- Map Canvas -->
                <div id="customer-picker-map" class="w-full h-72 sm:h-80 rounded-2xl border border-slate-200 overflow-hidden mb-3 relative z-0"></div>

                <!-- Lat & Lng Input Values -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Latitude (Garis Lintang)</label>
                        <input type="text" name="latitude" id="input_latitude" x-model="lat" placeholder="-7.536064"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Longitude (Garis Bujur)</label>
                        <input type="text" name="longitude" id="input_longitude" x-model="lng" placeholder="112.238402"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('customers.index') }}" class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan & Buat Pelanggan</span>
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
            lat: '{{ old('latitude', '') }}',
            lng: '{{ old('longitude', '') }}',

            init() {
                this.$nextTick(() => {
                    this.initPicker();
                });
            },

            initPicker() {
                let initLat = this.lat ? parseFloat(this.lat) : -7.536064;
                let initLng = this.lng ? parseFloat(this.lng) : 112.238402;
                let hasInitial = Boolean(this.lat && this.lng);

                this.map = L.map('customer-picker-map', {
                    center: [initLat, initLng],
                    zoom: hasInitial ? 16 : 13
                });

                // Satellite layer
                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri',
                    maxZoom: 19
                }).addTo(this.map);

                if (hasInitial) {
                    this.marker = L.marker([initLat, initLng], { draggable: true }).addTo(this.map);
                    this.marker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.lat = pos.lat.toFixed(6);
                        this.lng = pos.lng.toFixed(6);
                    });
                }

                this.map.on('click', (e) => {
                    const pos = e.latlng;
                    this.lat = pos.lat.toFixed(6);
                    this.lng = pos.lng.toFixed(6);

                    if (this.marker) {
                        this.marker.setLatLng(pos);
                    } else {
                        this.marker = L.marker(pos, { draggable: true }).addTo(this.map);
                        this.marker.on('dragend', (ev) => {
                            const p = ev.target.getLatLng();
                            this.lat = p.lat.toFixed(6);
                            this.lng = p.lng.toFixed(6);
                        });
                    }
                });
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

                    this.map.setView([pLat, pLng], 17);

                    if (this.marker) {
                        this.marker.setLatLng([pLat, pLng]);
                    } else {
                        this.marker = L.marker([pLat, pLng], { draggable: true }).addTo(this.map);
                    }

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
</script>
@endsection
