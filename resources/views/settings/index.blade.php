@extends('layouts.app')

@section('title', 'Pengaturan Sistem & Pengguna')
@section('header_title', 'Pengaturan Hokigo & Pengguna')

@section('content')
<div class="space-y-8" x-data="userSettingsApp()">

    <!-- Section 1: Manajemen Akun (Owner, Admin & Teknisi) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-6 h-6 text-brand-blue"></i>
                    <span>Daftar Akun Pengguna: Owner, Admin & Teknisi</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Atur nama dan identitas siapa pemilik (owner), staf admin, dan teknisi lapangan yang bertugas</p>
            </div>

            <button type="button" @click="userModalOpen = true" 
                    class="px-4 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-blue-600/25 transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Pengguna Baru</span>
            </button>
        </div>

        <!-- 3 Highlighted Identity Cards (Siapa Owner, Admin, Teknisi) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Owner Card -->
            @php $ownerUser = $users->firstWhere('role', 'owner'); @endphp
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 text-[10px] font-black uppercase tracking-wider">
                            👑 Owner (Pemilik)
                        </span>
                        <span class="w-2 h-2 rounded-full {{ $ownerUser?->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-base">{{ $ownerUser?->name ?? 'Belum Diatur' }}</h4>
                        <p class="text-xs font-mono text-slate-500">{{ $ownerUser?->email ?? 'owner@hokigo.net' }}</p>
                        @if($ownerUser?->phone)
                            <p class="text-xs text-amber-800 font-semibold mt-1">WA: {{ $ownerUser->phone }}</p>
                        @endif
                        @if($ownerUser?->plain_password)
                            <div x-data="{ showPass: false }" class="mt-1 flex items-center gap-1.5 text-xs text-amber-900 font-mono">
                                <span class="text-[11px] text-slate-500">Sandi:</span>
                                <span x-text="showPass ? '{{ $ownerUser->plain_password }}' : '••••••••'" class="font-bold"></span>
                                <button type="button" @click="showPass = !showPass" class="text-amber-700 hover:text-amber-950 p-0.5">
                                    <span x-text="showPass ? '🙈' : '👁️'" class="text-[11px]"></span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                @if($ownerUser)
                    <button type="button" @click="openEditUser({{ json_encode($ownerUser) }})"
                            class="w-full py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-xs">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Isi / Ubah Nama Owner</span>
                    </button>
                @endif
            </div>

            <!-- Admin Card -->
            @php $adminUser = $users->firstWhere('role', 'admin'); @endphp
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/80 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-200 text-blue-900 text-[10px] font-black uppercase tracking-wider">
                            🛡️ Admin (Staf Kantor)
                        </span>
                        <span class="w-2 h-2 rounded-full {{ $adminUser?->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-base">{{ $adminUser?->name ?? 'Belum Diatur' }}</h4>
                        <p class="text-xs font-mono text-slate-500">{{ $adminUser?->email ?? 'admin@hokigo.net' }}</p>
                        @if($adminUser?->phone)
                            <p class="text-xs text-blue-800 font-semibold mt-1">WA: {{ $adminUser->phone }}</p>
                        @endif
                        @if($adminUser?->plain_password)
                            <div x-data="{ showPass: false }" class="mt-1 flex items-center gap-1.5 text-xs text-blue-900 font-mono">
                                <span class="text-[11px] text-slate-500">Sandi:</span>
                                <span x-text="showPass ? '{{ $adminUser->plain_password }}' : '••••••••'" class="font-bold"></span>
                                <button type="button" @click="showPass = !showPass" class="text-blue-700 hover:text-blue-950 p-0.5">
                                    <span x-text="showPass ? '🙈' : '👁️'" class="text-[11px]"></span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                @if($adminUser)
                    <button type="button" @click="openEditUser({{ json_encode($adminUser) }})"
                            class="w-full py-2 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-xs">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Isi / Ubah Nama Admin</span>
                    </button>
                @endif
            </div>

            <!-- Teknisi Card -->
            @php $teknisiUser = $users->firstWhere('role', 'teknisi'); @endphp
            <div class="p-4 rounded-2xl bg-cyan-50/70 border border-cyan-200/80 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full bg-cyan-200 text-cyan-900 text-[10px] font-black uppercase tracking-wider">
                            🔧 Teknisi (Lapangan)
                        </span>
                        <span class="w-2 h-2 rounded-full {{ $teknisiUser?->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-base">{{ $teknisiUser?->name ?? 'Belum Diatur' }}</h4>
                        <p class="text-xs font-mono text-slate-500">{{ $teknisiUser?->email ?? 'teknisi@hokigo.net' }}</p>
                        @if($teknisiUser?->phone)
                            <p class="text-xs text-cyan-800 font-semibold mt-1">WA: {{ $teknisiUser->phone }}</p>
                        @endif
                        @if($teknisiUser?->plain_password)
                            <div x-data="{ showPass: false }" class="mt-1 flex items-center gap-1.5 text-xs text-cyan-900 font-mono">
                                <span class="text-[11px] text-slate-500">Sandi:</span>
                                <span x-text="showPass ? '{{ $teknisiUser->plain_password }}' : '••••••••'" class="font-bold"></span>
                                <button type="button" @click="showPass = !showPass" class="text-cyan-700 hover:text-cyan-950 p-0.5">
                                    <span x-text="showPass ? '🙈' : '👁️'" class="text-[11px]"></span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                @if($teknisiUser)
                    <button type="button" @click="openEditUser({{ json_encode($teknisiUser) }})"
                            class="w-full py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-xs">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Isi / Ubah Nama Teknisi</span>
                    </button>
                @else
                    <button type="button" @click="userModalOpen = true"
                            class="w-full py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-xs">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Akun Teknisi</span>
                    </button>
                @endif
            </div>

        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto pt-2">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 pl-4">Nama Lengkap & Email</th>
                        <th class="py-3">Peran (Role)</th>
                        <th class="py-3">Kata Sandi</th>
                        <th class="py-3">No. WhatsApp</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 pr-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 pl-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-brand-blue flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden border border-slate-200">
                                        @if($user->avatar)
                                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($user->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                        <div class="text-slate-400 font-mono text-[11px]">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5">
                                @if($user->role === 'owner')
                                    <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold border border-amber-300">
                                        👑 Owner (Pemilik)
                                    </span>
                                @elseif($user->role === 'teknisi')
                                    <span class="px-2.5 py-1 rounded-full bg-cyan-100 text-cyan-800 font-bold border border-cyan-300">
                                        🔧 Teknisi Lapangan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 font-bold border border-blue-300">
                                        🛡️ Admin (Staf)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 font-mono text-xs">
                                @if($user->plain_password)
                                    <div x-data="{ showP: false }" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200">
                                        <span x-text="showP ? '{{ $user->plain_password }}' : '••••••••'" class="font-bold text-slate-800 tracking-wider"></span>
                                        <button type="button" @click="showP = !showP" class="text-slate-400 hover:text-slate-700 transition" title="Lihat / Sembunyikan Kata Sandi">
                                            <span x-text="showP ? '🙈' : '👁️'" class="text-xs"></span>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">(Belum diset)</span>
                                @endif
                            </td>
                            <td class="py-3.5 font-mono text-slate-600">
                                {{ $user->phone ?: '-' }}
                            </td>
                            <td class="py-3.5">
                                @if($user->is_active)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3.5 pr-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                            @click="openEditUser({{ json_encode($user) }})"
                                            class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Edit Akun">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('settings.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition" title="Hapus Akun">
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

    </div>

    <!-- Section 2: Profil Usaha & Rekening Bank Pembayaran (Masuk ke Template WA) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Form Pengaturan Usaha -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="building" class="w-6 h-6 text-brand-blue"></i>
                    <span>Informasi Usaha & Rekening Tagihan</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Informasi ini otomatis tertera pada invoice cetak dan pesan WhatsApp tagihan</p>
            </div>

            <form action="{{ route('settings.general') }}" method="POST" class="space-y-4 pt-2">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Usaha / Provider *</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Slogan / Tagline Usaha</label>
                    <input type="text" name="company_tagline" value="{{ old('company_tagline', $settings['company_tagline']) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Admin / CS *</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Kantor / Sentral</label>
                        <input type="text" name="company_address" value="{{ old('company_address', $settings['company_address']) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Daftar Rekening Bank & E-Wallet *</label>
                    <textarea name="bank_accounts" rows="4" required
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">{{ old('bank_accounts', $settings['bank_accounts']) }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Daftar ini akan otomatis disisipkan ke dalam pesan WhatsApp setiap kali tombol penagihan diklik.</p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs shadow-md shadow-blue-600/30 transition flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Pesan WhatsApp Otomatis (Requirement 2) -->
        <div class="bg-emerald-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-white text-base">Preview Format Tagihan WhatsApp</h3>
                        <p class="text-xs text-emerald-300">Tampilan pesan otomatis saat staf klik tombol "Chat WA"</p>
                    </div>
                </div>

                <div class="bg-emerald-900/60 border border-emerald-700/50 rounded-2xl p-4 text-xs font-mono text-emerald-100 leading-relaxed whitespace-pre-line space-y-2">
Yth, Pelanggan {{ $settings['company_name'] }}..
*Bapak Slamet Raharjo* (ID: *HKG-001*)

Sebagai pengingat, Hari ini pembayaran wifi🙏🏻

📦 Paket: *Hoki Home 10 Mbps*
💰 Total Tagihan: *Rp 150.000*
📅 Jatuh Tempo: *10 {{ now()->translatedFormat('F Y') }}*

Bisa dibayarkan melalui rekening berikut:

{{ $settings['bank_accounts'] }}

Mohon kirimkan bukti transfer jika sudah membayar ya Kak. Terima kasih! 🙏✨
                </div>
            </div>

            <p class="text-[11px] text-emerald-300/80 mt-4">
                💡 Tombol direct WA di sistem langsung membuka chat WhatsApp dengan nomor tujuan yang sudah diformat ke standar internasional (+62).
            </p>
        </div>

    </div>

    <!-- Section 3: Integrasi WhatsApp Gateway & Penjadwalan Chat Otomatis -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="bot" class="w-6 h-6 text-emerald-600"></i>
                    <span>Integrasi WhatsApp Gateway & Chat Tagihan Otomatis</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Hubungkan WhatsApp kantor Hokigo agar server dapat mengirim tagihan secara otomatis setiap hari tanpa perlu membuka aplikasi WhatsApp
                </p>
            </div>
            <div>
                @if(!empty($settings['wa_gateway_token']))
                    <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold flex items-center gap-1.5 border border-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Gateway Terhubung ({{ strtoupper($settings['wa_gateway_provider']) }})</span>
                    </span>
                @else
                    <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold flex items-center gap-1.5 border border-amber-300">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Gateway Belum Terhubung</span>
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Form Pengaturan Gateway -->
            <div class="lg:col-span-7 space-y-4">
                <form action="{{ route('settings.whatsapp') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Penyedia Gateway (Provider) *</label>
                            <select name="wa_gateway_provider" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                                <option value="fonnte" {{ ($settings['wa_gateway_provider'] ?? 'fonnte') === 'fonnte' ? 'selected' : '' }}>
                                    Fonnte (Rekomendasi - Sangat Mudah)
                                </option>
                                <option value="wablas" {{ ($settings['wa_gateway_provider'] ?? '') === 'wablas' ? 'selected' : '' }}>
                                    Wablas
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jadwal Jam Kirim Otomatis *</label>
                            <select name="wa_reminder_time" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                                <option value="07:00" {{ ($settings['wa_reminder_time'] ?? '08:00') === '07:00' ? 'selected' : '' }}>07:00 WIB (Pagi)</option>
                                <option value="08:00" {{ ($settings['wa_reminder_time'] ?? '08:00') === '08:00' ? 'selected' : '' }}>08:00 WIB (Standar - Rekomendasi)</option>
                                <option value="09:00" {{ ($settings['wa_reminder_time'] ?? '08:00') === '09:00' ? 'selected' : '' }}>09:00 WIB (Pagi)</option>
                                <option value="10:00" {{ ($settings['wa_reminder_time'] ?? '08:00') === '10:00' ? 'selected' : '' }}>10:00 WIB (Siang)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">API Token WhatsApp Gateway *</label>
                        <input type="text" name="wa_gateway_token" value="{{ old('wa_gateway_token', $settings['wa_gateway_token']) }}" 
                               placeholder="Tempelkan token API dari Fonnte / Wablas di sini"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                        <p class="text-[11px] text-slate-400 mt-1">
                            Dapatkan token dengan mendaftar di <strong>fonnte.com</strong>, lalu sambungkan WhatsApp dengan scan QR di menu Device.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div class="space-y-0.5">
                            <span class="text-xs font-bold text-slate-900 block">Kirim Otomatis Setiap Hari</span>
                            <span class="text-[11px] text-slate-500 block">Sistem otomatis mengirim WA ke semua pelanggan yang jatuh tempo hari ini pada jam di atas.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="wa_auto_reminder" value="1" {{ ($settings['wa_auto_reminder'] ?? '1') === '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Pengaturan Gateway</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card Tes Kirim WhatsApp & Petunjuk -->
            <div class="lg:col-span-5 space-y-4">
                <div class="p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-900 flex items-center gap-1.5">
                        <i data-lucide="send" class="w-4 h-4 text-emerald-600"></i>
                        <span>Uji Coba Kirim WhatsApp</span>
                    </h4>
                    <p class="text-xs text-emerald-800">
                        Kirim pesan uji coba ke nomor Anda untuk memastikan koneksi WhatsApp Gateway berhasil aktif.
                    </p>

                    <form action="{{ route('settings.whatsapp.test') }}" method="POST" class="space-y-2.5">
                        @csrf
                        <div class="relative">
                            <input type="text" name="test_phone" value="{{ old('test_phone', $settings['company_phone']) }}" required
                                   placeholder="Contoh: 081227391262"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-emerald-300 bg-white text-xs font-mono font-bold focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                        </div>
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition">
                            <i data-lucide="message-square" class="w-4 h-4"></i>
                            <span>Kirim Pesan Uji Coba Sekarang</span>
                        </button>
                    </form>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-2">
                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                        <i data-lucide="help-circle" class="w-4 h-4 text-brand-blue"></i>
                        <span>Cara Menghubungkan WhatsApp Hokigo:</span>
                    </span>
                    <ol class="list-decimal list-inside space-y-1 text-[11px] text-slate-500">
                        <li>Buka website <strong>fonnte.com</strong> dan daftar akun.</li>
                        <li>Masuk ke menu <strong>Device</strong> lalu klik <strong>Scan QR</strong> menggunakan WhatsApp nomor kantor.</li>
                        <li>Salin <strong>API Token</strong> dan tempelkan pada kolom token di samping.</li>
                        <li>Klik <strong>Simpan Pengaturan Gateway</strong> dan lakukan uji coba kirim pesan.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Cadangan & Pemulihan Database (Backup & Restore) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="database-backup" class="w-6 h-6 text-emerald-600"></i>
                    <span>Cadangan & Pemulihan Database (Backup & Restore)</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Unduh file cadangan mandiri ke HP/Laptop atau pulihkan seluruh data pelanggan, tagihan, dan konfigurasi sistem.
                </p>
            </div>
            <a href="{{ route('backup.index') }}" 
               class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/25 transition">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Buka Pusat Backup & Restore</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-emerald-900 block">Unduh Salinan Cadangan (.json)</span>
                    <span class="text-[11px] text-emerald-700 block">1-klik unduh seluruh database lengkap ke perangkat Anda untuk disimpan di Google Drive.</span>
                </div>
                <a href="{{ route('backup.download') }}" 
                   class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shrink-0 shadow-xs transition">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Unduh Sekarang</span>
                </a>
            </div>

            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/80 flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-slate-900 block">Unduh Data Pelanggan Excel (.csv)</span>
                    <span class="text-[11px] text-slate-600 block">Format tabel spreadsheet praktis dibuka di Microsoft Excel atau Google Sheets.</span>
                </div>
                <a href="{{ route('backup.export-customers-csv') }}" 
                   class="px-4 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center gap-2 shrink-0 shadow-xs transition">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <span>Unduh Excel</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal 1: Tambah Pengguna Baru -->
    <div x-show="userModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="userModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-base">Tambah Akun Masuk Baru</h3>
                <button @click="userModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('settings.users.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" required placeholder="Contoh: Dani Teknisi Lapangan"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Login *</label>
                    <input type="email" name="email" required placeholder="dani@hokigo.net"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi (Password) *</label>
                        <div class="relative" x-data="{ showNewPass: false }">
                            <input :type="showNewPass ? 'text' : 'password'" name="password" required placeholder="Min 6 karakter"
                                   class="w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <button type="button" @click="showNewPass = !showNewPass"
                                    class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 p-0.5"
                                    title="Tampilkan / Sembunyikan Kata Sandi">
                                <span x-text="showNewPass ? '🙈' : '👁️'" class="text-xs"></span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran (Role) *</label>
                        <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="owner">👑 Owner (Pemilik Usaha)</option>
                            <option value="admin">🛡️ Admin (Staf Kantor)</option>
                            <option value="teknisi">🔧 Teknisi (Lapangan)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp</label>
                    <input type="text" name="phone" placeholder="08xxxxxxxxxx"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="userModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition">
                        Simpan Pengguna
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Modal 2: Edit Pengguna -->
    <div x-show="editUserModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="editUserModalOpen = false" class="bg-white rounded-2xl sm:rounded-3xl max-w-sm sm:max-w-md w-full my-auto shadow-2xl border border-slate-100 flex flex-col max-h-[88vh] overflow-hidden">
            
            <div class="flex items-center justify-between p-4 sm:p-5 border-b border-slate-100 bg-white shrink-0">
                <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">Edit Pengguna</h3>
                <button @click="editUserModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'{{ url('settings/users') }}/' + (selectedUser ? selectedUser.id : '')" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-4 sm:p-5 space-y-3 flex-1" autocomplete="off">
                @csrf
                @method('PUT')

                <!-- Upload Foto Profil (Ringkas & Pas) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto Profil Pengguna</label>
                    <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="relative w-11 h-11 rounded-full bg-slate-200 border border-brand-blue/30 shadow-xs flex items-center justify-center overflow-hidden shrink-0">
                            <template x-if="editAvatar">
                                <img :src="editAvatar" alt="Foto Profil" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!editAvatar">
                                <div class="w-full h-full bg-brand-blue text-white font-extrabold text-sm flex items-center justify-center"
                                     x-text="editName ? editName.charAt(0).toUpperCase() : '?'">
                                </div>
                            </template>
                        </div>

                        <div class="flex-1 min-w-0">
                            <input type="file" id="edit_user_avatar_file" name="avatar_file" accept="image/*" @change="handleEditAvatarFile($event)" class="hidden">
                            <input type="hidden" name="avatar" :value="editAvatar">
                            <input type="hidden" name="remove_avatar" :value="removeEditAvatar ? '1' : '0'">

                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="document.getElementById('edit_user_avatar_file').click()"
                                        class="px-2.5 py-1 rounded-lg bg-brand-blue hover:bg-brand-blueHover text-white text-[11px] font-bold shadow-xs transition flex items-center gap-1">
                                    <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                                    <span>Pilih Foto</span>
                                </button>
                                <button type="button" x-show="editAvatar" @click="clearEditAvatar()"
                                        class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-bold transition">
                                    Hapus
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5 truncate">
                                JPG, PNG (Otomatis pas & ringan).
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" x-model="editName" required
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Login *</label>
                    <input type="email" name="email" x-model="editEmail" required autocomplete="off"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ubah Kata Sandi</label>
                        <div class="relative">
                            <input :type="showEditPassword ? 'text' : 'password'" name="password" x-model="editPassword"
                                   placeholder="Ketik jika ingin ganti..." autocomplete="new-password"
                                   class="w-full pl-3 pr-9 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <button type="button" @click="showEditPassword = !showEditPassword"
                                    class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 p-0.5"
                                    title="Tampilkan / Sembunyikan Kata Sandi">
                                <span x-text="showEditPassword ? '🙈' : '👁️'" class="text-xs"></span>
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5">Min 6 karakter. Kosongkan jika tetap.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran (Role) *</label>
                        <select name="role" x-model="editRole" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="owner">👑 Owner</option>
                            <option value="admin">🛡️ Admin</option>
                            <option value="teknisi">🔧 Teknisi</option>
                        </select>
                    </div>
                </div>

                <!-- Kotak Lihat Kata Sandi Yang Sudah Dimasukkan -->
                <div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex items-center justify-between gap-3 text-xs">
                    <div class="min-w-0 flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                            <i data-lucide="key" class="w-4 h-4"></i>
                        </div>
                        <div class="truncate">
                            <span class="text-slate-500 font-semibold block text-[11px]">Kata Sandi Yang Tersimpan:</span>
                            <span class="font-mono font-extrabold text-slate-900 text-xs tracking-wider"
                                  x-text="selectedUser && selectedUser.plain_password ? (showSavedPassword ? selectedUser.plain_password : '••••••••') : '(Belum diset, isi sandi di atas lalu Simpan)'">
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0" x-show="selectedUser && selectedUser.plain_password">
                        <button type="button" @click="showSavedPassword = !showSavedPassword"
                                class="px-2.5 py-1 rounded-xl bg-white border border-amber-200 text-amber-900 hover:bg-amber-100 text-[11px] font-bold shadow-xs transition flex items-center gap-1">
                            <span x-text="showSavedPassword ? '🙈 Sembunyikan' : '👁️ Lihat Sandi'"></span>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp</label>
                    <input type="text" name="phone" x-model="editPhone" placeholder="08xxxxxxxxxx"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" x-model="editIsActive"
                               class="rounded border-slate-300 text-brand-blue focus:ring-brand-blue">
                        <span>Akun Aktif (Dapat Login)</span>
                    </label>
                </div>

                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="editUserModalOpen = false" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition">
                        Perbarui Pengguna
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    function userSettingsApp() {
        return {
            userModalOpen: false, 
            editUserModalOpen: false,
            selectedUser: null,
            editName: '',
            editEmail: '',
            editRole: 'admin',
            editPhone: '',
            editIsActive: true,
            editPassword: '',
            showEditPassword: false,
            showSavedPassword: false,
            editAvatar: '',
            removeEditAvatar: false,
            handleEditAvatarFile(e) {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        const maxDim = 400;
                        let width = img.width;
                        let height = img.height;
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
                        this.editAvatar = canvas.toDataURL('image/jpeg', 0.85);
                        this.removeEditAvatar = false;
                    };
                    img.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            },
            clearEditAvatar() {
                this.editAvatar = '';
                this.removeEditAvatar = true;
                const input = document.getElementById('edit_user_avatar_file');
                if (input) input.value = '';
            },
            openEditUser(u) {
                this.selectedUser = u;
                this.editName = u.name;
                this.editEmail = u.email;
                this.editRole = u.role;
                this.editPhone = u.phone || '';
                this.editIsActive = !!u.is_active;
                this.editPassword = '';
                this.showEditPassword = false;
                this.showSavedPassword = false;
                this.editAvatar = u.avatar || '';
                this.removeEditAvatar = false;
                this.editUserModalOpen = true;
                if (window.lucide) {
                    setTimeout(() => window.lucide.createIcons(), 50);
                }
            }
        };
    }
</script>
@endsection
