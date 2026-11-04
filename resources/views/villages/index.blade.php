@extends('layouts.app')

@section('title', 'Master Desa / Wilayah')
@section('header_title', 'Kelompok Desa & Wilayah')

@section('content')
<div class="space-y-6" x-data="{ 
    createModalOpen: false, 
    editModalOpen: false,
    selectedVillage: null,
    openEdit(v) {
        this.selectedVillage = v;
        this.editModalOpen = true;
    }
}">

    <!-- Header Action Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="map-pin" class="w-6 h-6 text-brand-blue"></i>
                <span>Wilayah & Titik Distribusi Desa</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola daftar desa tempat jaringan WiFi Hokigo beroperasi untuk pengelompokan pelanggan</p>
        </div>

        <button type="button" @click="createModalOpen = true" 
                class="px-4 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-blue-600/25 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Desa Baru</span>
        </button>
    </div>

    <!-- Villages Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($villages as $village)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between space-y-4">
                
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-brand-blue/10 text-brand-blue flex items-center justify-center font-bold">
                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base">{{ $village->name }}</h3>
                                <div class="text-xs text-slate-400 font-mono">{{ $village->code ? 'Kode: ' . $village->code : '' }} {{ $village->district ? '&bull; ' . $village->district : '' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Customers Count Badge -->
                    <div class="p-3 rounded-2xl bg-slate-50 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Pelanggan Aktif:</span>
                        <a href="{{ route('customers.index', ['village_id' => $village->id, 'view' => 'grouped']) }}" 
                           class="font-extrabold text-brand-blue hover:underline">
                            {{ $village->customers_count }} Pelanggan &rarr;
                        </a>
                    </div>

                    @if($village->notes)
                        <div class="text-xs text-slate-600 bg-amber-50/50 border border-amber-200/40 p-3 rounded-xl line-clamp-2">
                            <strong class="text-amber-800">Catatan Jaringan:</strong> {{ $village->notes }}
                        </div>
                    @endif
                </div>

                <!-- Footer Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" 
                            @click="openEdit({{ json_encode($village) }})"
                            class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Edit</span>
                    </button>

                    <form action="{{ route('villages.destroy', $village) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus desa {{ $village->name }}?')">
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
                <i data-lucide="map" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                <h3 class="font-bold text-slate-700 text-base">Belum ada desa yang terdaftar</h3>
                <p class="text-xs text-slate-400 mt-1">Tambahkan desa pertama Anda untuk mulai mengelompokkan pelanggan.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal 1: Tambah Desa -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="createModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-base">Tambah Desa / Wilayah Baru</h3>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('villages.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Desa *</label>
                    <input type="text" name="name" required placeholder="Contoh: Desa Sukamaju"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Singkatan</label>
                        <input type="text" name="code" placeholder="SKM"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs uppercase font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan</label>
                        <input type="text" name="district" placeholder="Kecamatan Makmur"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Topologi / Jaringan</label>
                    <textarea name="notes" rows="2" placeholder="Misal: 3 ODP terpasang, jalur fiber optic dari tiang PLN utama..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition">
                        Simpan Desa
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Modal 2: Edit Desa -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="editModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-base">Edit Desa / Wilayah</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('villages') }}/' + (selectedVillage ? selectedVillage.id : '')" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Desa *</label>
                    <input type="text" name="name" :value="selectedVillage ? selectedVillage.name : ''" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Singkatan</label>
                        <input type="text" name="code" :value="selectedVillage ? selectedVillage.code : ''"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs uppercase font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan</label>
                        <input type="text" name="district" :value="selectedVillage ? selectedVillage.district : ''"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Topologi / Jaringan</label>
                    <textarea name="notes" rows="2" x-text="selectedVillage ? selectedVillage.notes : ''"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition">
                        Perbarui Desa
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
