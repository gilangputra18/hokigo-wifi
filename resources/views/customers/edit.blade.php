@extends('layouts.app')

@section('title', 'Edit Pelanggan - ' . $customer->name)
@section('header_title', 'Edit Pelanggan: ' . $customer->name)

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

        <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-6">
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
                        <select name="village_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            @foreach($villages as $vil)
                                <option value="{{ $vil->id }}" {{ old('village_id', $customer->village_id) == $vil->id ? 'selected' : '' }}>
                                    {{ $vil->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap / Dusun / RT RW</label>
                        <textarea name="address" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">{{ old('address', $customer->address) }}</textarea>
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
                            @for($d = 1; $d <= 28; $d++)
                                <option value="{{ $d }}" {{ old('due_date_day', $customer->due_date_day) == $d ? 'selected' : '' }}>
                                    Setiap Tanggal {{ $d }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Teknis -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 3. Data Teknis / Perangkat
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Model Router (ONT)</label>
                        <input type="text" name="router_model" value="{{ old('router_model', $customer->router_model) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username PPPoE</label>
                        <input type="text" name="pppoe_user" value="{{ old('pppoe_user', $customer->pppoe_user) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sandi PPPoE</label>
                        <input type="text" name="pppoe_password" value="{{ old('pppoe_password', $customer->pppoe_password) }}" placeholder="Contoh: 12345 / rahasia"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>
            </div>

            <!-- Section 4: Notes (Requirement 1) -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> 4. Catatan Khusus Pelanggan (Note Lapangan)
                </h3>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Teknis & Penagihan (Note)</label>
                    <textarea name="notes" rows="3" 
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue bg-amber-50/30">{{ old('notes', $customer->notes) }}</textarea>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                    Klik dan geser pin di peta satelit di bawah ini untuk memperbarui posisi akurat rumah pelanggan, atau gunakan tombol GPS saat berada di lokasi.
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
            lat: '{{ old('latitude', $customer->latitude ?? '') }}',
            lng: '{{ old('longitude', $customer->longitude ?? '') }}',

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
</script>
@endsection
