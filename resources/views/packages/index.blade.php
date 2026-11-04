@extends('layouts.app')

@section('title', 'Master Paket Internet')
@section('header_title', 'Paket Layanan Internet')

@section('content')
<div class="space-y-6" x-data="{ 
    createModalOpen: false, 
    editModalOpen: false,
    selectedPackage: null,
    openEdit(p) {
        this.selectedPackage = p;
        this.editModalOpen = true;
    }
}">

    <!-- Header Action Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="wifi" class="w-6 h-6 text-brand-blue"></i>
                <span>Katalog Paket WiFi Hokigo</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Atur nama paket, batas kecepatan bandwidth, dan tarif langganan bulanan</p>
        </div>

        <button type="button" @click="createModalOpen = true" 
                class="px-4 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-blue-600/25 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Paket Baru</span>
        </button>
    </div>

    <!-- Packages Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @forelse($packages as $pkg)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between space-y-4">
                
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="px-3 py-1 rounded-full bg-blue-50 text-brand-blue font-extrabold text-xs">
                            {{ $pkg->speed ?: 'WiFi' }}
                        </span>
                        
                        @if($pkg->is_active)
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500" title="Aktif"></span>
                        @else
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-300" title="Nonaktif"></span>
                        @endif
                    </div>

                    <div>
                        <h3 class="font-extrabold text-slate-900 text-lg">{{ $pkg->name }}</h3>
                        <div class="text-2xl font-black text-brand-blue mt-1">
                            Rp {{ number_format($pkg->price, 0, ',', '.') }}
                            <span class="text-xs font-normal text-slate-400">/bln</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed min-h-[36px]">
                        {{ $pkg->description ?: 'Layanan internet tanpa kuota (unlimited).' }}
                    </p>

                    <div class="p-2.5 rounded-xl bg-slate-50 text-xs flex items-center justify-between text-slate-600">
                        <span>Digunakan oleh:</span>
                        <span class="font-bold text-slate-900">{{ $pkg->customers_count }} Pelanggan</span>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" 
                            @click="openEdit({{ json_encode($pkg) }})"
                            class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Edit</span>
                    </button>

                    <form action="{{ route('packages.destroy', $pkg) }}" method="POST" onsubmit="return confirm('Hapus paket {{ $pkg->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-slate-200/80">
                <i data-lucide="wifi-off" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                <h3 class="font-bold text-slate-700 text-base">Belum ada paket internet</h3>
                <p class="text-xs text-slate-400 mt-1">Buat paket internet pertama Anda sekarang.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal 1: Tambah Paket -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="createModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-base">Tambah Paket Baru</h3>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('packages.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Paket *</label>
                    <input type="text" name="name" required placeholder="Contoh: Hoki Fast 20 Mbps"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kecepatan (Speed)</label>
                        <input type="text" name="speed" placeholder="20 Mbps"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Bulanan (Rp) *</label>
                        <input type="number" name="price" required placeholder="220000" min="0"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Paket</label>
                    <textarea name="description" rows="2" placeholder="Cocok untuk streaming 4K, smart TV, dan game online tanpa lag..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition">
                        Simpan Paket
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Modal 2: Edit Paket -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="editModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-base">Edit Paket Internet</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('packages') }}/' + (selectedPackage ? selectedPackage.id : '')" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Paket *</label>
                    <input type="text" name="name" :value="selectedPackage ? selectedPackage.name : ''" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kecepatan</label>
                        <input type="text" name="speed" :value="selectedPackage ? selectedPackage.speed : ''"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga (Rp) *</label>
                        <input type="number" name="price" :value="selectedPackage ? Math.round(selectedPackage.price) : 0" required min="0"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Paket</label>
                    <textarea name="description" rows="2" x-text="selectedPackage ? selectedPackage.description : ''"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue"></textarea>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" :checked="selectedPackage && selectedPackage.is_active"
                               class="rounded border-slate-300 text-brand-blue focus:ring-brand-blue">
                        <span>Status Aktif (Bisa dipilih pelanggan baru)</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition">
                        Perbarui Paket
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
