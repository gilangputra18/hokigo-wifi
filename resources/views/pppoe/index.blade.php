@extends('layouts.app')

@section('title', 'Pencarian Akun PPPoE')
@section('header_title', 'Pencarian & Katalog Akun PPPoE')

@section('content')
<div class="space-y-6" x-data="pppoeDirectoryApp()">

    <!-- Top Action Banner & Overview -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-brand-blue/10 text-brand-blue flex items-center justify-center font-bold shrink-0">
                        <i data-lucide="key-round" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <span>Katalog Akun PPPoE</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">Semua Role</span>
                        </h2>
                        <p class="text-xs text-slate-500">
                            Pencarian akun PPPoE siap pakai per desa. Akun yang sudah terpasang otomatis tersembunyi, dan akan muncul kembali jika pelanggan dihapus/berhenti.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" @click="bulkModalOpen = true" 
                        class="px-4 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-blue-600/25 transition">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <span>Buat Massal PPPoE</span>
                </button>

                <button type="button" @click="singleModalOpen = true" 
                        class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-2 shadow-xs transition">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Satuan</span>
                </button>

                <form action="{{ route('pppoe.sync') }}" method="POST" onsubmit="return confirm('Sinkronkan akun PPPoE yang sudah ada di data pelanggan ke katalog?');" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs flex items-center gap-1.5 transition"
                            title="Deteksi dan masukkan username PPPoE yang sudah ada di data pelanggan">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Sinkronkan Pelanggan</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Summary Stats Counters -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-100">
            <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tersedia (Belum Terpakai)
                    </span>
                    <div class="text-2xl font-black text-emerald-950 mt-0.5">{{ $availableCount }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Sedang Digunakan
                    </span>
                    <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $usedCount }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-brand-blue flex items-center justify-center font-bold">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Akun Terdaftar</span>
                    <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $totalCount }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-200/60 text-slate-600 flex items-center justify-center font-bold">
                    <i data-lucide="database" class="w-5 h-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        
        <form method="GET" action="{{ route('pppoe.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            
            <!-- Village Filter -->
            <div class="sm:col-span-4">
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand-blue"></i>
                    <span>Pilihan Desa / Wilayah</span>
                </label>
                <select name="village_id" onchange="this.form.submit()" 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue bg-white">
                    <option value="">-- Semua Desa (Semua Wilayah) --</option>
                    @foreach($villages as $vil)
                        <option value="{{ $vil->id }}" {{ $selectedVillageId == $vil->id ? 'selected' : '' }}>
                            {{ $vil->name }} {{ $vil->district ? '('.$vil->district.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search Field -->
            <div class="sm:col-span-5">
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Cari Username PPPoE</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-slate-400"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Contoh: Hokigo40, Hokigo..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>
            </div>

            <!-- Submit Filter Button -->
            <div class="sm:col-span-3 flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-brand-blue hover:bg-brand-blueHover text-white rounded-xl font-bold text-xs transition shadow-sm">
                    Terapkan Filter
                </button>
                @if($selectedVillageId || $search)
                    <a href="{{ route('pppoe.index') }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>

        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 pt-3 border-t border-slate-100 overflow-x-auto no-scrollbar">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'available']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 flex items-center gap-1.5 {{ $statusFilter === 'available' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                <span>Tersedia (Belum Dipakai)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'available' ? 'bg-white/25 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $availableCount }}
                </span>
            </a>

            <a href="{{ request()->fullUrlWithQuery(['status' => 'used']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 flex items-center gap-1.5 {{ $statusFilter === 'used' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                <span>Sudah Terpakai</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'used' ? 'bg-white/25 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $usedCount }}
                </span>
            </a>

            <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 flex items-center gap-1.5 {{ $statusFilter === 'all' ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i data-lucide="list" class="w-3.5 h-3.5"></i>
                <span>Semua Akun</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'all' ? 'bg-white/25 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $totalCount }}
                </span>
            </a>
        </div>

    </div>

    <!-- Alert / Village Banner -->
    @if($selectedVillage)
        <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/80 text-blue-900 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="map-pin" class="w-4 h-4 text-brand-blue shrink-0"></i>
                <span>Menampilkan akun PPPoE khusus <strong>{{ $selectedVillage->name }}</strong>. Akun yang belum terpakai di bawah ini siap langsung dipasang oleh teknisi/admin.</span>
            </div>
            <a href="{{ route('customers.create', ['village_id' => $selectedVillage->id]) }}" 
               class="px-3 py-1.5 bg-brand-blue hover:bg-brand-blueHover text-white font-bold rounded-lg text-xs shrink-0 flex items-center gap-1 shadow-xs">
                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                <span>+ Pasang Pelanggan di {{ $selectedVillage->name }}</span>
            </a>
        </div>
    @endif

    <!-- PPPoE Accounts Directory List -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        @if($pppoeAccounts->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 pl-6">Username PPPoE</th>
                            <th class="py-3.5">Sandi (Password)</th>
                            <th class="py-3.5">Desa / Wilayah</th>
                            <th class="py-3.5">Status Penggunaan</th>
                            <th class="py-3.5 pr-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pppoeAccounts as $acc)
                            @php
                                $isUsed = $acc->customer !== null;
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition {{ $isUsed ? 'bg-slate-50/30' : '' }}">
                                
                                <!-- Username -->
                                <td class="py-3.5 pl-6">
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-slate-900 font-mono text-sm tracking-wide {{ !$isUsed ? 'text-brand-blue' : '' }}">
                                            {{ $acc->username }}
                                        </span>
                                        <button type="button" @click="copyText('{{ $acc->username }}')" 
                                                class="text-slate-400 hover:text-brand-blue p-1 rounded transition" title="Salin Username">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                    @if($acc->notes)
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $acc->notes }}</div>
                                    @endif
                                </td>

                                <!-- Password -->
                                <td class="py-3.5">
                                    <div class="flex items-center gap-2">
                                        <code class="px-2 py-0.5 rounded bg-slate-100 font-mono text-xs text-slate-700 font-bold">
                                            {{ $acc->password }}
                                        </code>
                                        <button type="button" @click="copyText('{{ $acc->password }}')" 
                                                class="text-slate-400 hover:text-brand-blue p-1 rounded transition" title="Salin Sandi">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Village -->
                                <td class="py-3.5">
                                    <div class="font-semibold text-slate-800 text-xs flex items-center gap-1.5">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                        <span>{{ $acc->village?->name ?? 'Semua Desa' }}</span>
                                    </div>
                                    @if($acc->village?->district)
                                        <div class="text-[10px] text-slate-400">{{ $acc->village->district }}</div>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="py-3.5">
                                    @if(!$isUsed)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[11px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Belum Terpakai (Tersedia)</span>
                                        </span>
                                    @else
                                        <div class="space-y-0.5">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold text-[11px]">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span>Terpasang di Pelanggan</span>
                                            </span>
                                            <div class="text-xs font-bold text-slate-900 mt-0.5">
                                                <a href="{{ route('customers.show', $acc->customer) }}" class="text-brand-blue hover:underline">
                                                    {{ $acc->customer->name }} ({{ $acc->customer->customer_code }})
                                                </a>
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                WA: {{ $acc->customer->phone }} &bull; Status: <span class="capitalize">{{ $acc->customer->status }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3.5 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(!$isUsed)
                                            <!-- Gunakan Langsung untuk Pelanggan Baru -->
                                            <a href="{{ route('customers.create', ['village_id' => $acc->village_id, 'pppoe_user' => $acc->username, 'pppoe_password' => $acc->password]) }}" 
                                               class="px-3 py-1.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center gap-1 shadow-xs transition"
                                               title="Pasang username ini ke pelanggan baru">
                                                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                                <span>Pakai Akun Ini</span>
                                            </a>
                                        @else
                                            <a href="{{ route('customers.show', $acc->customer) }}" 
                                               class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center gap-1 transition">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                <span>Lihat Pelanggan</span>
                                            </a>
                                        @endif

                                        <!-- Hapus akun (hanya jika belum terpakai) -->
                                        @if(!$isUsed)
                                            <form action="{{ route('pppoe.destroy', $acc) }}" method="POST" onsubmit="return confirm('Hapus akun PPPoE {{ $acc->username }}?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Hapus akun dari katalog">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-slate-100">
                {{ $pppoeAccounts->links() }}
            </div>

        @else
            <!-- Empty State -->
            <div class="text-center py-12 px-4 space-y-3">
                <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto font-bold">
                    <i data-lucide="key-round" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-800">
                    @if($statusFilter === 'available')
                        Tidak Ada Akun PPPoE Tersedia
                    @else
                        Tidak Ada Data Akun PPPoE
                    @endif
                </h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    @if($statusFilter === 'available')
                        Semua akun PPPoE di desa/filter ini sudah dipakai oleh pelanggan, atau belum ada akun dibuat. Klik tombol di bawah untuk membuat akun baru secara massal.
                    @else
                        Belum ada akun PPPoE yang sesuai dengan pencarian atau filter desa yang Anda pilih.
                    @endif
                </p>
                <div class="pt-2 flex justify-center gap-2">
                    <button type="button" @click="bulkModalOpen = true" 
                            class="px-4 py-2 rounded-xl bg-brand-blue text-white text-xs font-bold hover:bg-brand-blueHover transition shadow-xs flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                        <span>Buat Massal Akun (Contoh: Hokigo1 s/d Hokigo50)</span>
                    </button>
                </div>
            </div>
        @endif

    </div>

    <!-- MODAL 1: Buat Massal Akun PPPoE (Bulk Generator) -->
    <div x-show="bulkModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="bulkModalOpen = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <i data-lucide="layers" class="w-5 h-5 text-brand-blue"></i>
                        <span>Buat Massal Akun PPPoE</span>
                    </h3>
                    <p class="text-xs text-slate-500">Buat urutan nomor akun sekaligus untuk desa tertentu</p>
                </div>
                <button @click="bulkModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('pppoe.bulk') }}" method="POST" class="space-y-4" x-data="bulkGeneratorPreview()">
                @csrf

                <!-- Pilih Desa -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Pilih Desa *</label>
                    <select name="village_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <option value="">-- Pilih Desa --</option>
                        @foreach($villages as $vil)
                            <option value="{{ $vil->id }}" {{ $selectedVillageId == $vil->id ? 'selected' : '' }}>
                                {{ $vil->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Format Awalan Username -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Awalan Username *</label>
                    <input type="text" name="prefix" x-model="prefix" required placeholder="Contoh: Hokigo" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <p class="text-[10px] text-slate-400 mt-1">Username akan berurutan: <span x-text="prefix + from"></span>, <span x-text="prefix + (parseInt(from)+1)"></span>, dst.</p>
                </div>

                <!-- Rentang Nomor -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Dari Nomor *</label>
                        <input type="number" name="from_number" x-model="from" min="1" max="9999" required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Sampai Nomor *</label>
                        <input type="number" name="to_number" x-model="to" min="1" max="9999" required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <!-- Kata Sandi PPPoE -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Kata Sandi Default PPPoE *</label>
                    <input type="text" name="password" x-model="password" required value="Irzaq09042003" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    <p class="text-[10px] text-slate-400 mt-1">Semua akun yang dibuat akan menggunakan kata sandi ini.</p>
                </div>

                <!-- Live Preview Box -->
                <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-200/60 text-xs space-y-1.5">
                    <div class="font-bold text-brand-blue flex items-center gap-1.5">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        <span>Pratinjau Hasil Pembuatan:</span>
                    </div>
                    <p class="text-slate-600 text-[11px]">
                        Akan membuat sebanyak <strong class="text-brand-blue" x-text="Math.max(0, to - from + 1)"></strong> akun:
                    </p>
                    <div class="font-mono text-[11px] text-slate-800 bg-white p-2 rounded-xl border border-blue-100 flex items-center justify-between">
                        <span x-text="prefix + from + ' s/d ' + prefix + to"></span>
                        <span class="text-slate-400 font-sans text-[10px]" x-text="'Sandi: ' + password"></span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="bulkModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Generate Akun Sekarang</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- MODAL 2: Tambah Akun Satuan (Single Add) -->
    <div x-show="singleModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="singleModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-5 h-5 text-brand-blue"></i>
                        <span>Tambah Akun PPPoE Satuan</span>
                    </h3>
                    <p class="text-xs text-slate-500">Tambahkan 1 username PPPoE baru ke katalog</p>
                </div>
                <button @click="singleModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('pppoe.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Pilih Desa *</label>
                    <select name="village_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <option value="">-- Pilih Desa --</option>
                        @foreach($villages as $vil)
                            <option value="{{ $vil->id }}" {{ $selectedVillageId == $vil->id ? 'selected' : '' }}>
                                {{ $vil->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Username PPPoE *</label>
                    <input type="text" name="username" required placeholder="Contoh: Hokigo40" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Kata Sandi PPPoE *</label>
                    <input type="text" name="password" required value="Irzaq09042003" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Misal: Paket Khusus / Cadangan" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="singleModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Akun</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- Toast Notification for Copy -->
    <div x-show="copiedToast" x-cloak 
         class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-4 py-2.5 rounded-2xl shadow-xl flex items-center gap-2 text-xs font-bold transition-all transform animate-bounce">
        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
        <span x-text="toastMessage"></span>
    </div>

</div>

<script>
function pppoeDirectoryApp() {
    return {
        bulkModalOpen: false,
        singleModalOpen: false,
        copiedToast: false,
        toastMessage: 'Tersalin ke clipboard!',
        copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.toastMessage = `Tersalin: ${text}`;
                this.copiedToast = true;
                setTimeout(() => {
                    this.copiedToast = false;
                }, 2000);
            }).catch(err => {
                alert(`Gagal menyalin: ${text}`);
            });
        }
    };
}

function bulkGeneratorPreview() {
    return {
        prefix: 'Hokigo',
        from: 1,
        to: 50,
        password: 'Irzaq09042003'
    };
}
</script>
@endsection
