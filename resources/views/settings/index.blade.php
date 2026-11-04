@extends('layouts.app')

@section('title', 'Pengaturan Sistem & Pengguna')
@section('header_title', 'Pengaturan Hokigo & Pengguna')

@section('content')
<div class="space-y-8" x-data="{ 
    userModalOpen: false, 
    editUserModalOpen: false,
    selectedUser: null,
    openEditUser(u) {
        this.selectedUser = u;
        this.editUserModalOpen = true;
    }
}">

    <!-- Section 1: Manajemen Akun (Requirement 6: Bisa dibuka di saya sama di admin) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-6 h-6 text-brand-blue"></i>
                    <span>Hak Akses: Akun Owner & Admin</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola akun staf admin dan owner yang memiliki akses masuk ke aplikasi Hokigo</p>
            </div>

            <button type="button" @click="userModalOpen = true" 
                    class="px-4 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-blue-600/25 transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Pengguna Baru</span>
            </button>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 pl-4">Nama & Email</th>
                        <th class="py-3">Peran (Role)</th>
                        <th class="py-3">No. WhatsApp</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 pr-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 pl-4">
                                <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                <div class="text-slate-400 font-mono text-[11px]">{{ $user->email }}</div>
                            </td>
                            <td class="py-3.5">
                                @if($user->role === 'owner')
                                    <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold border border-amber-300">
                                        👑 Owner (Pemilik)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 font-bold border border-blue-300">
                                        🛡️ Admin (Staf)
                                    </span>
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
                        <input type="password" name="password" required placeholder="Min 6 karakter"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran (Role) *</label>
                        <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="admin">Admin (Staff)</option>
                            <option value="owner">Owner (Pemilik)</option>
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
    <div x-show="editUserModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="editUserModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-base">Edit Pengguna</h3>
                <button @click="editUserModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('settings/users') }}/' + (selectedUser ? selectedUser.id : '')" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" :value="selectedUser ? selectedUser.name : ''" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Login *</label>
                    <input type="email" name="email" :value="selectedUser ? selectedUser.email : ''" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tetap"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran (Role) *</label>
                        <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                            <option value="admin" :selected="selectedUser && selectedUser.role === 'admin'">Admin (Staff)</option>
                            <option value="owner" :selected="selectedUser && selectedUser.role === 'owner'">Owner (Pemilik)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp</label>
                    <input type="text" name="phone" :value="selectedUser ? selectedUser.phone : ''"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" :checked="selectedUser && selectedUser.is_active"
                               class="rounded border-slate-300 text-brand-blue focus:ring-brand-blue">
                        <span>Akun Aktif (Dapat Login)</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="editUserModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition">
                        Perbarui Pengguna
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
