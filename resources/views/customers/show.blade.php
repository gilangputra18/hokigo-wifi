@extends('layouts.app')

@section('title', 'Detail Pelanggan - ' . $customer->name)
@section('header_title', 'Detail Pelanggan: ' . $customer->name)

@section('content')
<div class="space-y-6">

    <!-- Top Card / Profile Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-2xl bg-brand-blue/10 text-brand-blue flex items-center justify-center font-extrabold text-2xl shrink-0">
                {{ substr($customer->name, 0, 1) }}
            </div>
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $customer->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-600 font-mono text-xs font-bold">
                        {{ $customer->customer_code }}
                    </span>
                    
                    @if($customer->status === 'active')
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Aktif</span>
                    @elseif($customer->status === 'isolated')
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">Isolir</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-xs font-bold">Nonaktif</span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 font-medium">
                    @if(!auth()->user()->isTeknisi() && $customer->phone)
                    <span class="flex items-center gap-1 text-emerald-600 font-bold">
                        <i data-lucide="phone" class="w-3.5 h-3.5"></i> {{ $customer->phone }}
                    </span>
                    @endif
                    <span class="flex items-center gap-1 text-slate-700">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand-blue"></i> {{ $customer->village?->name ?? 'Luar Wilayah' }}
                    </span>
                    @if($customer->house_number)
                        <span class="flex items-center gap-1 text-slate-700 font-semibold">
                            <i data-lucide="home" class="w-3.5 h-3.5 text-amber-600"></i> {{ $customer->house_number }}
                        </span>
                    @endif
                    <span>Tgl Pemasangan: {{ $customer->installation_date ? $customer->installation_date->isoFormat('D MMMM Y') : '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @if(!auth()->user()->isTeknisi())
            <!-- Direct WA (Membuka Chat WhatsApp ke No HP Pelanggan) -->
            <a href="https://api.whatsapp.com/send?phone={{ $customer->clean_phone }}" target="_blank"
               title="Membuka chat WhatsApp langsung ke nomor pelanggan ini ({{ $customer->phone }})"
               class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs flex items-center gap-2 transition shadow-xs">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>Chat WA Pelanggan</span>
            </a>
            @endif

            <!-- Edit -->
            <a href="{{ route('customers.edit', $customer) }}" 
               class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-2 transition">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                <span>Edit</span>
            </a>

            <!-- Toggle Status Dropdown or Form -->
            <form action="{{ route('customers.toggle-status', $customer) }}" method="POST" class="inline">
                @csrf
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-brand-blue">
                    <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Set: Aktif</option>
                    <option value="isolated" {{ $customer->status === 'isolated' ? 'selected' : '' }}>Set: Isolir</option>
                    <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Set: Nonaktif</option>
                </select>
            </form>
        </div>

    </div>

    <!-- 2 Column Details: Left Details + Notes, Right Invoices -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Specs, Photo & Notes -->
        <div class="space-y-6">
            
            <!-- Foto Rumah Pelanggan Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i data-lucide="image" class="w-4 h-4 text-brand-blue"></i>
                        <span>Foto Rumah / Titik Pemasangan</span>
                    </h3>
                    <a href="{{ route('customers.edit', $customer) }}" class="text-[11px] font-bold text-brand-blue hover:underline">
                        {{ $customer->house_photo ? 'Ganti Foto' : '+ Upload Foto' }}
                    </a>
                </div>

                @if($customer->house_photo)
                    <div class="relative group rounded-2xl overflow-hidden border border-slate-200 bg-slate-900">
                        <img src="{{ route('customers.photo', $customer) }}" alt="Foto Rumah {{ $customer->name }}" class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                        <a href="{{ route('customers.photo', $customer) }}" target="_blank" 
                           class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-xs gap-1.5">
                            <i data-lucide="maximize-2" class="w-4 h-4"></i>
                            <span>Buka Ukuran Penuh</span>
                        </a>
                    </div>
                @else
                    <div class="py-6 px-4 text-center rounded-2xl bg-slate-50 border border-dashed border-slate-200">
                        <i data-lucide="camera" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                        <p class="text-xs text-slate-500 font-medium">Belum ada foto rumah yang diunggah.</p>
                        <a href="{{ route('customers.edit', $customer) }}" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-brand-blue hover:underline">
                            <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                            <span>Jepret Foto Sekarang</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Notes Card (Catatan Khusus Pelanggan) -->
            <div class="bg-amber-50/80 rounded-3xl p-6 border border-amber-200/80 shadow-xs">
                <div class="flex items-center gap-2 text-amber-900 font-bold text-sm mb-3">
                    <i data-lucide="sticky-note" class="w-5 h-5 text-amber-600"></i>
                    <span>Catatan Keterangan Lapangan</span>
                </div>
                <div class="bg-white/80 p-4 rounded-2xl border border-amber-200/50 text-xs text-amber-950 font-medium leading-relaxed whitespace-pre-line">
                    {{ $customer->notes ?: 'Belum ada catatan khusus untuk pelanggan ini.' }}
                </div>
            </div>

            <!-- Network & Subscription Specs -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i data-lucide="wifi" class="w-4 h-4 text-brand-blue"></i>
                    <span>Informasi Layanan & Perangkat</span>
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Paket Internet</span>
                        <span class="font-bold text-slate-800">{{ $customer->package?->name ?? 'Internet' }} ({{ $customer->package?->speed ?? '-' }})</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Biaya Bulanan</span>
                        <span class="font-bold text-brand-blue text-sm">Rp {{ number_format($customer->monthly_fee, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Jatuh Tempo</span>
                        <span class="font-bold text-slate-800">Setiap tanggal {{ $customer->due_date_day }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Model Router (ONT)</span>
                        <span class="font-mono text-slate-700">{{ $customer->router_model ?: '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Nilai Redaman</span>
                        @if($customer->redaman)
                            <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-lg text-xs border border-emerald-200 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ $customer->redaman }}</span>
                            </span>
                        @else
                            <span class="text-slate-400 italic text-[11px]">-</span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Username PPPoE</span>
                        <span class="font-mono text-slate-700 font-semibold">{{ $customer->pppoe_user ?: '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Sandi PPPoE</span>
                        <span class="font-mono text-blue-700 font-bold bg-blue-50 px-2 py-0.5 rounded">{{ $customer->pppoe_password ?: '-' }}</span>
                    </div>

                    <div class="py-1 border-b border-slate-50">
                        <span class="text-slate-400 block mb-1">Titik Lokasi GPS</span>
                        @if($customer->latitude && $customer->longitude)
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-[11px] text-slate-700">{{ $customer->latitude }}, {{ $customer->longitude }}</span>
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank"
                                   class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] flex items-center gap-1 shadow-xs transition">
                                    <i data-lucide="navigation" class="w-3 h-3"></i>
                                    <span>Buka Maps</span>
                                </a>
                            </div>
                        @else
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-amber-600 text-xs font-semibold">Belum ada titik GPS</span>
                                <a href="{{ route('maps.index') }}" 
                                    class="px-2 py-1 rounded-lg bg-brand-blue hover:bg-brand-blueHover text-white text-[10px] font-bold">
                                    Titik di Peta
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="py-1 border-b border-slate-50">
                        <span class="text-slate-400 block mb-1">No. Rumah / Gang / Blok</span>
                        <span class="text-slate-800 font-bold">{{ $customer->house_number ?: '-' }}</span>
                    </div>

                    <div class="py-1">
                        <span class="text-slate-400 block mb-1">Alamat Pemasangan</span>
                        <span class="text-slate-700 font-medium">{{ $customer->address ?: '-' }}</span>
                    </div>
                </div>

                @if(!auth()->user()->isTeknisi())
                <!-- Delete Customer Button (Owner & Admin Only) -->
                <div class="pt-4 border-t border-slate-100">
                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini beserta seluruh tagihannya? Data yang terhapus tidak dapat dikembalikan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition flex items-center justify-center gap-1.5 border border-rose-200">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Hapus Data Pelanggan</span>
                        </button>
                    </form>
                </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Invoices & Payment History -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <i data-lucide="history" class="w-5 h-5 text-brand-blue"></i>
                        <span>Riwayat Tagihan & Pembayaran</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar semua invoice bulanan pelanggan ini</p>
                </div>
            </div>

            @if($customer->invoices->isEmpty())
                <div class="text-center py-10 bg-slate-50 rounded-2xl">
                    <i data-lucide="receipt" class="w-10 h-10 text-slate-300 mx-auto mb-2"></i>
                    <p class="text-xs text-slate-500 font-medium">Belum ada tagihan untuk pelanggan ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th class="pb-3">No. Faktur</th>
                                <th class="pb-3">Periode</th>
                                <th class="pb-3">Jatuh Tempo</th>
                                <th class="pb-3">Nominal</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach($customer->invoices as $inv)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3.5 font-mono font-bold text-slate-800">
                                        {{ $inv->invoice_number }}
                                    </td>
                                    <td class="py-3.5 font-semibold text-slate-700">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $inv->period)->translatedFormat('F Y') }}
                                    </td>
                                    <td class="py-3.5 text-slate-600">
                                        {{ $inv->due_date->isoFormat('D MMM Y') }}
                                    </td>
                                    <td class="py-3.5 font-bold text-slate-900">
                                        Rp {{ number_format($inv->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5">
                                        @if($inv->status === 'paid')
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold inline-flex items-center gap-1">
                                                <i data-lucide="check" class="w-3 h-3"></i> Lunas
                                            </span>
                                            <div class="text-[10px] text-slate-400 mt-0.5">
                                                via {{ strtoupper($inv->payment_method ?? 'Cash') }} &bull; {{ $inv->paid_at ? $inv->paid_at->format('d/m/Y') : '' }}
                                            </div>
                                        @elseif($inv->status === 'overdue')
                                            <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold inline-flex items-center gap-1 animate-pulse">
                                                <i data-lucide="alert-circle" class="w-3 h-3"></i> Lewat Tempo
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold inline-flex items-center gap-1">
                                                <i data-lucide="clock" class="w-3 h-3"></i> Belum Bayar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if(!auth()->user()->isTeknisi())
                                                @if($inv->status === 'paid')
                                                    <!-- Send WhatsApp Struk / Receipt -->
                                                    <a href="{{ $inv->whatsapp_receipt_url }}" target="_blank"
                                                       class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition" title="Kirim Bukti Lunas WA">
                                                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                                                    </a>
                                                @else
                                                    <!-- Send WhatsApp Bill -->
                                                    <a href="{{ $inv->whatsapp_url }}" target="_blank"
                                                       class="p-1.5 rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 transition" title="Kirim Tagihan WA">
                                                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                                                    </a>
                                                @endif
                                            @endif

                                            <!-- Print Invoice -->
                                            <a href="{{ route('invoices.print', $inv) }}" target="_blank"
                                               class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition" title="Cetak Struk">
                                                <i data-lucide="printer" class="w-4 h-4"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
