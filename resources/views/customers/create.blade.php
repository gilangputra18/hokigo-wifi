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

        <form action="{{ route('customers.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Identitas Pelanggan -->
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-brand-blue"></span> 1. Identitas Pelanggan
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Kode Pelanggan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Pelanggan (ID) *</label>
                        <input type="text" name="customer_code" value="{{ old('customer_code', $suggestedCode) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
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

                    <!-- Alamat / Dusun / RT RW -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap / Dusun / RT RW</label>
                        <textarea name="address" rows="2" placeholder="Contoh: RT 03 RW 01, Dusun Krajan, dekat Musholla Al-Huda"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">{{ old('address') }}</textarea>
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

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe / Model Router (ONT)</label>
                        <input type="text" name="router_model" value="{{ old('router_model', 'ZTE F609') }}" placeholder="Contoh: ZTE F609 / Huawei"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username PPPoE (Opsional)</label>
                        <input type="text" name="pppoe_user" value="{{ old('pppoe_user') }}" placeholder="user_pppoe"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat IP Pelanggan</label>
                        <input type="text" name="ip_address" value="{{ old('ip_address') }}" placeholder="192.168.1.xxx"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>
            </div>

            <!-- Section 4: Catatan Khusus Pelanggan (Requirement 1: Ada Note nya juga) -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> 4. Catatan Khusus Pelanggan (Note Lapangan)
                </h3>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Catatan Teknis / Penagihan (Note)
                    </label>
                    <textarea name="notes" rows="3" 
                              placeholder="Tuliskan catatan penting: posisi tiang ODP, warna kabel dropcore, panjang kabel, letak router, nomor kontak cadangan, atau jadwal penagihan khusus..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue bg-amber-50/30">{{ old('notes') }}</textarea>
                    <p class="text-[10px] text-slate-400 mt-1">Catatan ini akan tampil langsung di kartu pelanggan dan daftar desa untuk memudahkan teknisi & admin.</p>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
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
</script>
@endsection
