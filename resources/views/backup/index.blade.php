@extends('layouts.app')

@section('title', 'Backup & Restore Data')
@section('header_title', 'Cadangan & Pemulihan Database')

@section('content')
<div class="space-y-8" x-data="{ 
    restoreModalOpen: false, 
    restoreMode: 'fresh', 
    selectedFile: null, 
    fileName: '', 
    fileSize: '',
    onFileChange(e) {
        const file = e.target.files[0];
        if (file) {
            this.selectedFile = file;
            this.fileName = file.name;
            const sizeKB = Math.round(file.size / 1024);
            this.fileSize = sizeKB > 1024 ? (sizeKB / 1024).toFixed(2) + ' MB' : sizeKB + ' KB';
        } else {
            this.selectedFile = null;
            this.fileName = '';
            this.fileSize = '';
        }
    }
}">

    <!-- Page Header & Overview Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-brand-dark to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-white/10">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Perlindungan Data Usaha
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    <i data-lucide="database-backup" class="w-8 h-8 text-emerald-400"></i>
                    <span>Pusat Cadangan & Pemulihan Data</span>
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    Seluruh data penting pelanggan, tagihan, catatan keuangan, akun PPPoE, dan peta jaringan Hokigo WiFi aman dan dapat dicadangkan kapan saja ke perangkat HP/Laptop Anda maupun Google Drive.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                <a href="{{ route('backup.download') }}" 
                   class="px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-black text-sm flex items-center justify-center gap-2.5 shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5">
                    <i data-lucide="download" class="w-5 h-5"></i>
                    <span>Unduh Cadangan (.json)</span>
                </a>
                <a href="{{ route('backup.export-customers-csv') }}" 
                   class="px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs flex items-center justify-center gap-2 border border-white/10 transition">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400"></i>
                    <span>Excel Pelanggan (.csv)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2-Layer Security Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <!-- Layer 1: Supabase PostgreSQL Cloud Protection -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                <i data-lucide="cloud-check" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1.5 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Lapisan 1: Cloud Otomatis</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                </div>
                <h3 class="font-extrabold text-slate-900 text-sm">Supabase PostgreSQL Enterprise (Singapore)</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Database sistem Hokigo disimpan secara terpusat di cloud Supabase. Server cloud memiliki pencadangan log berkala (WAL snapshot), sehingga data ratusan pelanggan Anda tetap aman di cloud walaupun HP atau laptop Anda mati.
                </p>
            </div>
        </div>

        <!-- Layer 2: Local & Google Drive Snapshot -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-brand-blue flex items-center justify-center shrink-0 border border-blue-100">
                <i data-lucide="hard-drive-download" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1.5 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-blue">Lapisan 2: Cadangan Mandiri</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-black">
                        1-Klik
                    </span>
                </div>
                <h3 class="font-extrabold text-slate-900 text-sm">Salinan Fisik di HP, Laptop & Google Drive</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Sebagai pemilik usaha, Anda dapat mengunduh salinan lengkap database dalam 1 file JSON. File ini bebas Anda simpan ke Google Drive pribadi atau harddisk untuk ketenangan pikiran dan pemulihan instan kapan pun dibutuhkan.
                </p>
            </div>
        </div>

    </div>

    <!-- Live Data Snapshot Stats -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="layers" class="w-5 h-5 text-brand-blue"></i>
                    <span>Ringkasan Data yang Siap Dicadangkan</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Semua data berikut akan otomatis terbungkus rapi saat Anda mengunduh cadangan</p>
            </div>
            <div class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl self-start sm:self-auto">
                Total Koleksi Data: <span class="text-brand-blue font-black">{{ number_format($totalRecords) }}</span> baris
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
            
            <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 truncate">Pelanggan</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($stats['customers']) }}</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 truncate">Kelompok Desa</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($stats['villages']) }}</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-cyan-50/60 border border-cyan-100/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-600 text-white flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="wifi" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 truncate">Paket Internet</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($stats['packages']) }}</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-violet-50/60 border border-violet-100/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-violet-600 text-white flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="key-round" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 truncate">Akun PPPoE</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($stats['pppoe_accounts']) }}</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 truncate">Tagihan WiFi</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($stats['invoices']) }}</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-100/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 truncate">Keuangan (Kas)</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($stats['financial_transactions']) }}</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-100/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="ticket" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 truncate">Voucher Hotspot</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($stats['voucher_sales']) }}</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-100/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="map-pinned" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 truncate">Titik Peta & ODP</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($stats['network_nodes']) }}</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-100/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="git-commit" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 truncate">Rute Kabel FO</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($stats['cable_routes']) }}</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-700 text-white flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="sliders" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-500 truncate">Konfigurasi</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($stats['settings']) }}</p>
                </div>
            </div>

        </div>
    </div>

    <!-- 2 Main Interactive Action Columns: Download vs Restore -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Card 1: Unduh Cadangan (Backup Download) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-6">
            <div class="space-y-4">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                        <i data-lucide="download-cloud" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900">1. Unduh Cadangan Data</h2>
                        <p class="text-xs text-slate-500">Simpan salinan database ke perangkat HP / Laptop</p>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2.5">
                    <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                        <span>Apa saja yang termasuk dalam file cadangan?</span>
                    </h4>
                    <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside">
                        <li>Daftar lengkap pelanggan (identitas, foto rumah, koordinat GPS, paket, no HP).</li>
                        <li>Daftar akun PPPoE dan kata sandi pelanggan.</li>
                        <li>Tagihan bulanan, status lunas, dan rincian transaksi keuangan.</li>
                        <li>Penjualan voucher hotspot desa & pembagian komisi reseller.</li>
                        <li>Titik server, ODP fiber optic, dan jalur rute kabel di peta.</li>
                    </ul>
                </div>

                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        <strong>Tips Pemilik:</strong> Unduh file ini secara rutin (misal: setiap akhir bulan atau setelah mendaftarkan banyak pelanggan baru), lalu unggah file tersebut ke <strong>Google Drive</strong> Anda agar data selalu tersimpan abadi di awan.
                    </p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 space-y-3">
                <a href="{{ route('backup.download') }}" 
                   class="w-full py-3.5 px-6 rounded-2xl bg-brand-blue hover:bg-brand-blueHover text-white font-extrabold text-sm flex items-center justify-center gap-2.5 shadow-lg shadow-blue-600/25 transition transform hover:-translate-y-0.5">
                    <i data-lucide="download" class="w-5 h-5"></i>
                    <span>Unduh File Cadangan Sekarang (.json)</span>
                </a>

                <div class="flex items-center justify-between text-[11px] text-slate-500 px-1">
                    <span>Format: JSON Terenkripsi Standar</span>
                    <span>Kompatibel: Semua Perangkat (HP / PC)</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Pulihkan Data (Restore Upload) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-6">
            <div class="space-y-4">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                        <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900">2. Pulihkan Data dari File Cadangan</h2>
                        <p class="text-xs text-slate-500">Restore database dari file backup JSON yang pernah diunduh</p>
                    </div>
                </div>

                <form id="restoreForm" action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <!-- File Drop / Select Area -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih File Cadangan (.json) *</label>
                        <div class="relative border-2 border-dashed border-slate-300 hover:border-brand-blue rounded-2xl p-5 text-center transition bg-slate-50/50 hover:bg-blue-50/30 cursor-pointer">
                            <input type="file" name="backup_file" accept=".json,application/json" @change="onFileChange($event)" required
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            
                            <template x-if="!fileName">
                                <div class="space-y-2 pointer-events-none">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-brand-blue flex items-center justify-center mx-auto">
                                        <i data-lucide="file-up" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">Klik untuk memilih file cadangan JSON</p>
                                    <p class="text-[11px] text-slate-500">File bereksistensi .json (Contoh: hokigo_backup_2026-11-09.json)</p>
                                </div>
                            </template>

                            <template x-if="fileName">
                                <div class="space-y-1.5 pointer-events-none">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                                        <i data-lucide="file-check" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-black text-emerald-800" x-text="fileName"></p>
                                    <p class="text-[11px] text-slate-500 font-medium" x-text="'Ukuran file: ' + fileSize"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Restore Mode Selection -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Mode Pemulihan Database *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            
                            <label class="p-3 rounded-2xl border cursor-pointer transition flex items-start gap-2.5"
                                   :class="restoreMode === 'fresh' ? 'border-brand-blue bg-blue-50/60' : 'border-slate-200 bg-white hover:bg-slate-50'">
                                <input type="radio" name="restore_mode" value="fresh" x-model="restoreMode" class="mt-0.5 text-brand-blue focus:ring-brand-blue">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900">Timpa Bersih (Fresh)</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Sangat disarankan saat recovery server atau database baru.</p>
                                </div>
                            </label>

                            <label class="p-3 rounded-2xl border cursor-pointer transition flex items-start gap-2.5"
                                   :class="restoreMode === 'merge' ? 'border-brand-blue bg-blue-50/60' : 'border-slate-200 bg-white hover:bg-slate-50'">
                                <input type="radio" name="restore_mode" value="merge" x-model="restoreMode" class="mt-0.5 text-brand-blue focus:ring-brand-blue">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900">Gabungkan (Merge)</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Memperbarui yang ada & menambahkan yang belum ada.</p>
                                </div>
                            </label>

                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                        <p class="text-[11px] leading-relaxed">
                            <strong>Perhatian:</strong> Proses restore akan menggantikan data sesuai isi file cadangan. Pastikan file yang Anda unggah adalah file cadangan resmi Hokigo WiFi yang valid.
                        </p>
                    </div>

                    <!-- Trigger Button for Confirmation Modal -->
                    <button type="button" @click="if(!fileName) { alert('Silakan pilih file cadangan (.json) terlebih dahulu.'); } else { restoreModalOpen = true; }"
                            class="w-full py-3.5 px-6 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-sm flex items-center justify-center gap-2.5 shadow-lg shadow-amber-600/25 transition">
                        <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                        <span>Mulai Pemulihan Data (Restore)</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Practical Step-by-Step Guide for Google Drive -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4">
        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
            <i data-lucide="folder-sync" class="w-5 h-5 text-brand-blue"></i>
            <span>Panduan Mudah: Cara Menyimpan Cadangan ke Google Drive Pribadi</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
            
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/60 space-y-2">
                <div class="w-8 h-8 rounded-xl bg-brand-blue text-white font-black text-xs flex items-center justify-center">1</div>
                <h4 class="font-bold text-slate-900 text-xs">Klik "Unduh Cadangan"</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Klik tombol hijau/biru di atas untuk mengunduh file cadangan ke galeri berkas di HP atau folder Downloads di Laptop Anda.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/60 space-y-2">
                <div class="w-8 h-8 rounded-xl bg-brand-blue text-white font-black text-xs flex items-center justify-center">2</div>
                <h4 class="font-bold text-slate-900 text-xs">Buka Google Drive</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Buka aplikasi Google Drive di HP atau kunjungi <code class="text-brand-blue font-bold">drive.google.com</code> di komputer Anda.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/60 space-y-2">
                <div class="w-8 h-8 rounded-xl bg-brand-blue text-white font-black text-xs flex items-center justify-center">3</div>
                <h4 class="font-bold text-slate-900 text-xs">Upload File Cadangan</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Upload file JSON tadi ke dalam folder (misal: "Backup Hokigo WiFi"). Data ratusan pelanggan Anda kini aman selamanya.
                </p>
            </div>

        </div>
    </div>

    <!-- Confirmation Modal Before Restoring -->
    <div x-show="restoreModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5"
             @click.outside="restoreModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-black text-slate-900 text-base">Konfirmasi Pemulihan Data</h4>
                    <p class="text-xs text-slate-500">Harap baca dengan teliti sebelum melanjutkan</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-2">
                <p class="font-bold">Anda akan memulihkan data dari file:</p>
                <p class="font-mono bg-white px-2.5 py-1 rounded-lg border border-amber-200 font-bold text-slate-800 break-all" x-text="fileName"></p>
                <p class="text-[11px] text-amber-800">
                    Mode yang dipilih: <strong x-text="restoreMode === 'fresh' ? 'Timpa Bersih (Fresh Restore)' : 'Gabungkan Data (Merge)'"></strong>
                </p>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Seluruh data pelanggan, tagihan, dan rincian transaksi akan diselaraskan ke database. Proses ini tidak dapat dibatalkan setelah selesai.
            </p>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" @click="restoreModalOpen = false"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-xs transition">
                    Batal
                </button>
                <button type="button" @click="$el.disabled = true; $el.innerText = 'Memproses...'; document.getElementById('restoreForm').submit();"
                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs transition shadow-md shadow-rose-600/30 flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Ya, Lanjutkan Pemulihan</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
