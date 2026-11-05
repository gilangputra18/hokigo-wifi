<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hokigo') - Provider WiFi & Billing Management</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN with Brand Color config) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#1055CC',
                            blueHover: '#0b419e',
                            blueLight: '#eef4ff',
                            dark: '#141414',
                            darkSecondary: '#1d1d1f',
                            light: '#FEFEFE',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Leaflet Maps CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex" x-data="{ sidebarOpen: false, profileModalOpen: false, showProfilePassword: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden transition-opacity"></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-brand-dark text-white flex flex-col transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 border-r border-slate-800">
        
        <!-- Sidebar Brand / Logo Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-white/10 gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('images/logo.png') }}" alt="Hokigo Logo" class="h-10 w-auto rounded-lg shadow-sm bg-black/30 p-1 shrink-0">
                <div class="flex flex-col truncate">
                    <span class="text-xl font-bold tracking-tight text-white flex items-center gap-1.5">
                        Hoki<span class="text-blue-400">go</span>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-brand-blue text-white">WiFi</span>
                    </span>
                    <span class="text-[11px] text-slate-400 font-medium truncate">ISP & Billing System</span>
                </div>
            </div>
            <!-- Close Button on Mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition shrink-0" aria-label="Tutup Menu">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Current User Role Quick Badge -->
        <div class="px-6 py-3 bg-white/5 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-brand-blue text-white flex-shrink-0 flex items-center justify-center font-bold text-xs overflow-hidden border border-white/10">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                    @else
                        {{ substr(auth()->user()->name, 0, 1) }}
                    @endif
                </div>
                <div class="truncate">
                    <span class="text-xs font-semibold text-slate-300 truncate max-w-[110px] block">{{ auth()->user()->name }}</span>
                </div>
            </div>
            @if(auth()->user()->isOwner())
                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30">Owner</span>
            @elseif(auth()->user()->isTeknisi())
                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">Teknisi</span>
            @else
                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 border border-blue-500/30">Admin</span>
            @endif
        </div>

        <!-- Sidebar Menu Links -->
        <nav class="flex-1 overflow-y-auto px-4 py-5 space-y-1.5">
            <div class="px-2 pb-1 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">
                {{ auth()->user()->isTeknisi() ? 'Menu Teknisi' : 'Menu Utama' }}
            </div>

            @if(!auth()->user()->isTeknisi())
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>
            @endif

            <!-- Peta Jaringan & Penitikan Rumah Pelanggan (Semua Role) -->
            <a href="{{ route('maps.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('maps.*') ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="map-pinned" class="w-5 h-5 text-cyan-400"></i>
                    <span>Peta & Penitikan</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-cyan-400/20 text-cyan-300 border border-cyan-400/30">GPS</span>
            </a>

            <!-- Pelanggan -->
            <a href="{{ route('customers.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('customers.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Data Pelanggan</span>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('customers.*') ? 'bg-white/20 text-white' : 'bg-white/10 text-slate-400' }}">
                    {{ $sidebarCounts['total_customers'] ?? 0 }}
                </span>
            </a>

            @if(!auth()->user()->isTeknisi())
            <!-- Tagihan & Pembayaran -->
            <a href="{{ route('invoices.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('invoices.index') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                    <span>Tagihan WiFi</span>
                </div>
                @if(($sidebarCounts['unpaid_month'] ?? 0) > 0)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 font-semibold">
                        {{ $sidebarCounts['unpaid_month'] }}
                    </span>
                @endif
            </a>

            <!-- Reminder WhatsApp Jatuh Tempo -->
            <a href="{{ route('invoices.reminders') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('invoices.reminders') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="message-circle" class="w-5 h-5 text-emerald-400"></i>
                    <span>WhatsApp Tagihan</span>
                </div>
                @if(($sidebarCounts['due_count'] ?? 0) > 0)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-rose-500 text-white font-bold animate-pulse">
                        {{ $sidebarCounts['due_count'] }}
                    </span>
                @endif
            </a>

            <!-- Voucheran Hotspot (Khusus Owner & Admin) -->
            <a href="{{ route('vouchers.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('vouchers.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="ticket" class="w-5 h-5 text-amber-400"></i>
                    <span>Voucher Hotspot</span>
                </div>
                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-amber-400/20 text-amber-300">Desa</span>
            </a>

            <!-- Keuangan & Laporan (Omzet & Laba Bersih) -->
            <div class="pt-4 px-2 pb-1 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Finansial & Bisnis</div>

            <a href="{{ route('finance.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('finance.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="wallet" class="w-5 h-5"></i>
                <span>Data Keuangan</span>
            </a>

            <!-- Master Data -->
            <div class="pt-4 px-2 pb-1 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Master Data</div>
            @endif

            <!-- Desa / Wilayah (Tampil juga untuk Teknisi) -->
            <a href="{{ route('villages.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('villages.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="map-pin" class="w-5 h-5"></i>
                <span>Kelompok Desa</span>
            </a>

            @if(!auth()->user()->isTeknisi())
            <!-- Paket Internet -->
            <a href="{{ route('packages.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('packages.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="wifi" class="w-5 h-5"></i>
                <span>Paket Internet</span>
            </a>

            <!-- Pengaturan -->
            <a href="{{ route('settings.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('settings.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="settings" class="w-5 h-5"></i>
                <span>Pengaturan Sistem</span>
            </a>
            @endif
        </nav>

        <!-- Sidebar Footer / Logout -->
        <div class="p-4 border-t border-white/10">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-300 hover:bg-rose-500/10 hover:text-rose-200 border border-rose-500/20 transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Keluar (Logout)</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        
        <!-- Top Navbar -->
        <header class="min-h-14 sm:min-h-16 lg:min-h-20 py-1.5 sm:py-0 bg-white border-b border-slate-200/80 sticky top-0 z-20 flex items-center justify-between px-3 sm:px-6 lg:px-8 shadow-xs" style="padding-top: max(6px, env(safe-area-inset-top, 0px));">
            
            <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 mr-2">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = true" class="p-2 -ml-1 rounded-xl text-slate-600 hover:bg-slate-100 lg:hidden shrink-0" aria-label="Buka Menu Navigasi">
                    <i data-lucide="menu" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </button>

                <!-- Page Header Title / Breadcrumbs -->
                <div class="min-w-0 flex-1">
                    <h1 class="text-sm sm:text-lg md:text-xl font-extrabold text-slate-900 tracking-tight truncate block">
                        @yield('header_title', 'Hokigo Dashboard')
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium hidden sm:block truncate">
                        {{ now()->isoFormat('dddd, D MMMM Y') }} &bull; Provider WiFi & Sistem Penagihan Otomatis
                    </p>
                </div>
            </div>

            <!-- Top Right Action Icons -->
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                
                @if(!auth()->user()->isTeknisi())
                <!-- Quick WhatsApp Due Reminders Button -->
                <a href="{{ route('invoices.reminders') }}" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/80 transition-all shadow-xs">
                    <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                    <span>Chat Tagihan Hari Ini</span>
                    @if(($sidebarCounts['due_count'] ?? 0) > 0)
                        <span class="px-1.5 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-bold">{{ $sidebarCounts['due_count'] }}</span>
                    @endif
                </a>
                @elseif(!request()->routeIs('customers.create'))
                <!-- Quick Tambah Pelanggan Button for Teknisi (Sembunyi saat sedang di formulir tambah pelanggan) -->
                <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-1.5 p-2 sm:px-3 sm:py-2 rounded-xl text-xs font-semibold bg-brand-blue text-white hover:bg-brand-blueHover shadow-xs transition-all shrink-0" title="Tambah Pelanggan Baru">
                    <i data-lucide="user-plus" class="w-4 h-4 shrink-0"></i>
                    <span class="hidden md:inline">Tambah Pelanggan</span>
                </a>
                @endif

                <!-- User profile pill (Bisa diklik untuk ganti foto profil, email & kata sandi) -->
                <button type="button" @click="profileModalOpen = true" 
                        class="flex items-center gap-2 sm:gap-3 pl-1.5 sm:pl-3 border-l border-slate-200 hover:opacity-85 transition group text-left cursor-pointer shrink-0"
                        title="Klik untuk ganti foto profil, email & kata sandi akun Anda">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-brand-blue text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-sm group-hover:scale-105 transition overflow-hidden border border-slate-200 shrink-0">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                            {{ substr(auth()->user()->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="hidden lg:block text-left">
                        <div class="text-xs font-bold text-slate-900 leading-tight group-hover:text-brand-blue flex items-center gap-1">
                            <span>{{ auth()->user()->name }}</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                        </div>
                        <div class="text-[11px] text-slate-500 capitalize flex items-center gap-1">
                            <span>{{ auth()->user()->role }}</span>
                            <span class="text-[10px] text-brand-blue font-semibold">&bull; Edit Profil</span>
                        </div>
                    </div>
                </button>

            </div>
        </header>

        <!-- Main Body Wrapper -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto relative z-30">
            
            <!-- Alert Messages -->
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </div>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if(session('warning'))
                <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-600 flex items-center justify-center shrink-0">
                            <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        </div>
                        <span class="text-sm font-semibold">{{ session('warning') }}</span>
                    </div>
                    <button @click="show = false" class="text-amber-500 hover:text-amber-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-600 flex items-center justify-center shrink-0">
                            <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                        </div>
                        <span class="text-sm font-semibold">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start justify-between shadow-xs">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-sm font-bold block mb-1">Perhatian: Terjadi kesalahan</span>
                            <ul class="text-xs list-disc list-inside space-y-0.5 text-rose-700">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            <!-- Content Slot -->
            @yield('content')

        </main>

        <!-- Footer -->
        <footer class="mt-auto py-4 px-6 border-t border-slate-200 text-center text-xs text-slate-500 bg-white">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 max-w-7xl mx-auto">
                <p>&copy; {{ date('Y') }} <strong class="text-slate-700 font-semibold">Hokigo WiFi</strong> &mdash; Internet Desa Cepat & Andal.</p>
                <p class="text-slate-400">Sistem Manajemen & Billing Terpadu</p>
            </div>
        </footer>

    </div>

    @auth
    <!-- Modal Ganti Kata Sandi, Email & Foto Profil Akun (Bisa diakses Owner, Admin, & Teknisi) -->
    <div x-show="profileModalOpen" x-cloak 
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4">
        
        <div @click.away="profileModalOpen = false" 
             x-data="userProfileModalComponent()"
             class="bg-white rounded-2xl sm:rounded-3xl max-w-sm sm:max-w-md w-full my-auto shadow-2xl border border-slate-100 flex flex-col max-h-[88vh] overflow-hidden">
            
            <!-- Header Modal (Ringkas & Proporsional) -->
            <div class="flex items-center justify-between p-4 sm:p-5 border-b border-slate-100 bg-white shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-brand-blue flex items-center justify-center font-bold shrink-0">
                        <i data-lucide="user-cog" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm sm:text-base leading-tight">Profil & Kata Sandi Akun</h3>
                        <p class="text-[11px] text-slate-400">Perbarui profil dan sandi login akun Anda</p>
                    </div>
                </div>
                <button type="button" @click="profileModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Form Body (Scrollable bila layar HP kecil) -->
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-4 sm:p-5 space-y-3 flex-1" autocomplete="off">
                @csrf

                <!-- Upload Foto Profil (Ringkas & Pas) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto Profil</label>
                    <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="relative w-11 h-11 rounded-full bg-slate-200 border border-brand-blue/30 shadow-xs flex items-center justify-center overflow-hidden shrink-0">
                            <template x-if="avatarPreview">
                                <img :src="avatarPreview" alt="Foto Profil" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!avatarPreview">
                                <div class="w-full h-full bg-brand-blue text-white font-extrabold text-sm flex items-center justify-center">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            </template>
                            <div x-show="compressing" class="absolute inset-0 bg-black/60 flex items-center justify-center text-white text-[9px] font-bold">
                                ...
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <input type="file" id="user_avatar_file_input" name="avatar_file" accept="image/*" @change="handleAvatarFile($event)" class="hidden">
                            <input type="hidden" name="avatar" :value="avatarPreview">
                            <input type="hidden" name="remove_avatar" :value="removeAvatar ? '1' : '0'">

                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="document.getElementById('user_avatar_file_input').click()"
                                        class="px-2.5 py-1 rounded-lg bg-brand-blue hover:bg-brand-blueHover text-white text-[11px] font-bold shadow-xs transition flex items-center gap-1">
                                    <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                                    <span>Pilih Foto</span>
                                </button>
                                <button type="button" x-show="avatarPreview" @click="clearAvatar()"
                                        class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-bold transition">
                                    Hapus
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5 truncate">
                                JPG, PNG atau foto kamera langsung.
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ auth()->user()->name }}" required
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Login *</label>
                    <input type="email" name="email" value="{{ auth()->user()->email }}" required autocomplete="off"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru</label>
                    <div class="relative">
                        <input :type="showProfilePassword ? 'text' : 'password'" name="password" placeholder="Kosongkan jika tidak ingin ganti" autocomplete="new-password"
                               class="w-full pl-3 pr-9 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                        <button type="button" @click="showProfilePassword = !showProfilePassword"
                                class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 p-0.5"
                                title="Lihat / Sembunyikan Sandi">
                            <span x-text="showProfilePassword ? '🙈' : '👁️'" class="text-xs"></span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-0.5">Min 6 karakter. Kosongkan jika tetap.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp</label>
                    <input type="text" name="phone" value="{{ auth()->user()->phone }}" placeholder="08xxxxxxxxxx"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue">
                </div>

                <!-- Footer Aksi -->
                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="profileModalOpen = false" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold shadow-md shadow-blue-600/30 transition">
                        Simpan Perubahan Akun
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function userProfileModalComponent() {
            return {
                avatarPreview: @json(auth()->user()->avatar ?? ''),
                removeAvatar: false,
                compressing: false,
                handleAvatarFile(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.compressing = true;
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
                            this.avatarPreview = canvas.toDataURL('image/jpeg', 0.85);
                            this.removeAvatar = false;
                            this.compressing = false;
                            if (window.lucide) {
                                setTimeout(() => window.lucide.createIcons(), 50);
                            }
                        };
                        img.src = ev.target.result;
                    };
                    reader.readAsDataURL(file);
                },
                clearAvatar() {
                    this.avatarPreview = '';
                    this.removeAvatar = true;
                    const fileInput = document.getElementById('user_avatar_file_input');
                    if (fileInput) fileInput.value = '';
                    if (window.lucide) {
                        setTimeout(() => window.lucide.createIcons(), 50);
                    }
                }
            };
        }
    </script>
    @endauth

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
