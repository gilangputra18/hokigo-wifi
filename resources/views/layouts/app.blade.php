<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden transition-opacity"></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-brand-dark text-white flex flex-col transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 border-r border-slate-800">
        
        <!-- Sidebar Brand / Logo Header -->
        <div class="h-20 flex items-center px-6 border-b border-white/10 gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="Hokigo Logo" class="h-10 w-auto rounded-lg shadow-sm bg-black/30 p-1">
            <div class="flex flex-col">
                <span class="text-xl font-bold tracking-tight text-white flex items-center gap-1.5">
                    Hoki<span class="text-blue-400">go</span>
                    <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-brand-blue text-white">WiFi</span>
                </span>
                <span class="text-[11px] text-slate-400 font-medium">ISP & Billing System</span>
            </div>
        </div>

        <!-- Current User Role Quick Badge -->
        <div class="px-6 py-3 bg-white/5 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>
                <span class="text-xs font-semibold text-slate-300">{{ auth()->user()->name }}</span>
            </div>
            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ auth()->user()->isOwner() ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30' }}">
                {{ auth()->user()->role }}
            </span>
        </div>

        <!-- Sidebar Menu Links -->
        <nav class="flex-1 overflow-y-auto px-4 py-5 space-y-1.5">
            <div class="px-2 pb-1 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Menu Utama</div>

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>

            <!-- Pelanggan -->
            <a href="{{ route('customers.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('customers.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Pelanggan</span>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('customers.*') ? 'bg-white/20 text-white' : 'bg-white/10 text-slate-400' }}">
                    {{ $sidebarCounts['total_customers'] ?? 0 }}
                </span>
            </a>

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

            <!-- Keuangan & Laporan (Omzet & Laba Bersih) -->
            <div class="pt-4 px-2 pb-1 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Finansial & Bisnis</div>

            <a href="{{ route('finance.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('finance.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="wallet" class="w-5 h-5"></i>
                <span>Data Keuangan</span>
            </a>

            <!-- Master Data -->
            <div class="pt-4 px-2 pb-1 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Master Data</div>

            <!-- Desa / Wilayah -->
            <a href="{{ route('villages.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('villages.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="map-pin" class="w-5 h-5"></i>
                <span>Kelompok Desa</span>
            </a>

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
    <div class="flex-1 flex flex-col min-w-0 min-h-screen overflow-x-hidden">
        
        <!-- Top Navbar -->
        <header class="h-20 bg-white border-b border-slate-200/80 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-8 shadow-sm">
            
            <div class="flex items-center gap-3">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = true" class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 lg:hidden">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>

                <!-- Page Header Title / Breadcrumbs -->
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        @yield('header_title', 'Hokigo Dashboard')
                    </h1>
                    <p class="text-xs text-slate-500 font-medium hidden sm:block">
                        {{ now()->isoFormat('dddd, D MMMM Y') }} &bull; Provider WiFi & Sistem Penagihan Otomatis
                    </p>
                </div>
            </div>

            <!-- Top Right Action Icons -->
            <div class="flex items-center gap-3 sm:gap-4">
                
                <!-- Quick WhatsApp Due Reminders Button -->
                <a href="{{ route('invoices.reminders') }}" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/80 transition-all shadow-xs">
                    <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                    <span>Chat Tagihan Hari Ini</span>
                    @if(($sidebarCounts['due_count'] ?? 0) > 0)
                        <span class="px-1.5 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-bold">{{ $sidebarCounts['due_count'] }}</span>
                    @endif
                </a>

                <!-- User profile pill -->
                <div class="flex items-center gap-3 pl-3 border-l border-slate-200">
                    <div class="w-9 h-9 rounded-xl bg-brand-blue text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="hidden md:block text-left">
                        <div class="text-xs font-bold text-slate-900 leading-tight">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-500 capitalize">{{ auth()->user()->role }}</div>
                    </div>
                </div>

            </div>
        </header>

        <!-- Main Body Wrapper -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            
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

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
