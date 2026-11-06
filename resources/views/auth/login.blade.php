<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="author" content="Hokigo Network">
    <meta name="description" content="Portal Internal Manajemen Layanan Hokigo WiFi">
    <title>Masuk - Hokigo WiFi Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#1055CC',
                            blueHover: '#0b419e',
                            dark: '#070F2B',
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
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-[100dvh] flex flex-col justify-center items-center p-3 sm:p-6 antialiased text-slate-800 relative overflow-x-hidden bg-[#070F2B]">

    <!-- Decorative Luxury Blue Glowing Ambient Backdrop -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <!-- Deep Royal Blue & Sapphire Gradient Spheres -->
        <div class="absolute -top-[15%] -left-[10%] w-[550px] h-[550px] rounded-full bg-gradient-to-br from-blue-600/35 via-indigo-700/25 to-transparent blur-[130px]"></div>
        <div class="absolute -bottom-[20%] -right-[10%] w-[650px] h-[650px] rounded-full bg-gradient-to-tl from-[#1055CC]/40 via-sky-500/20 to-transparent blur-[140px]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] rounded-full bg-blue-700/15 blur-[160px]"></div>
        
        <!-- Subtle High-Tech Geometric Grid Lines -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.07]" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="network-grid" width="48" height="48" patternUnits="userSpaceOnUse">
                    <path d="M 48 0 L 0 0 0 48" fill="none" stroke="#60a5fa" stroke-width="0.75" />
                    <circle cx="48" cy="48" r="1.5" fill="#93c5fd" />
                    <circle cx="0" cy="0" r="1.5" fill="#93c5fd" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#network-grid)" />
        </svg>

        <!-- Top Luxury Cyan Accent Line -->
        <div class="absolute top-0 inset-x-0 h-[2px] bg-gradient-to-r from-transparent via-cyan-400/80 to-transparent"></div>
    </div>

    <!-- Login Container -->
    <div class="w-full max-w-[420px] my-auto relative z-10">
        
        <!-- Subtle Glowing Halo around Card -->
        <div class="absolute -inset-1 bg-gradient-to-r from-blue-600/30 via-cyan-500/20 to-indigo-600/30 rounded-3xl blur-xl pointer-events-none"></div>

        <!-- Main Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-2xl sm:rounded-3xl shadow-2xl shadow-blue-950/70 p-5 sm:p-7 border border-white/80 relative overflow-hidden">
            
            <!-- Top Inner Glow Accent -->
            <div class="absolute -top-12 -right-12 w-40 h-40 bg-blue-500/15 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Logo & Brand Header -->
            <div class="text-center mb-4 sm:mb-5">
                <div class="inline-flex items-center justify-center p-2 rounded-2xl bg-gradient-to-b from-[#0B1A40] to-[#070F2B] shadow-lg shadow-blue-950/40 mb-2.5 border border-blue-400/20">
                    <img src="{{ asset('images/logo.png') }}" alt="Hokigo Logo" class="h-8 sm:h-10 w-auto object-contain">
                </div>
                
                <div class="flex items-center justify-center gap-1.5 mb-1">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Hokigo <span class="text-[#1055CC]">WiFi</span></h1>
                </div>
                
                <p class="text-[11px] sm:text-xs text-slate-500 font-medium">Sistem Manajemen Pelanggan, Tagihan & Keuangan</p>
                <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-[10px] font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Portal ISP & Billing Terintegrasi
                </div>
            </div>

            <!-- Alert Flash Messages -->
            @if(isset($errors) && $errors->any())
                <div class="mb-3.5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2.5 animate-shake">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-rose-500"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-3.5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center gap-2.5">
                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-emerald-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Form Login -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-3 sm:space-y-3.5" x-data="{ showPassword: false, submitting: false }" @submit="submitting = true">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Email Akun</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               autocomplete="username"
                               placeholder="nama@hokigo.net"
                               class="w-full pl-10 pr-3.5 py-2.5 sm:py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-[#1055CC] text-xs sm:text-sm transition bg-white/80">
                    </div>
                </div>

                <!-- Password Input with Toggle Show/Hide Password -->
                <div>
                    <label for="password" class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" value="" required
                               autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full pl-10 pr-11 py-2.5 sm:py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-[#1055CC] text-xs sm:text-sm transition bg-white/80">
                        
                        <!-- Toggle Password Visibility Button -->
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition focus:outline-none"
                                title="Lihat / Sembunyikan Kata Sandi"
                                aria-label="Toggle password visibility">
                            <i x-show="!showPassword" data-lucide="eye" class="w-4 h-4"></i>
                            <i x-show="showPassword" x-cloak data-lucide="eye-off" class="w-4 h-4 text-blue-600"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[11px] sm:text-xs">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" :disabled="submitting"
                        class="w-full py-3 px-4 bg-gradient-to-r from-[#1055CC] via-[#1A64E8] to-[#0D44A6] hover:from-[#0E49B0] hover:to-[#0A398D] active:scale-[0.99] text-white rounded-xl font-bold text-xs sm:text-sm tracking-wide shadow-lg shadow-blue-600/35 hover:shadow-blue-600/50 transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-wait">
                    <span x-show="!submitting" class="flex items-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Masuk ke Dashboard</span>
                    </span>
                    <span x-show="submitting" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Memproses Masuk...</span>
                    </span>
                </button>
            </form>

        </div>

        <!-- Luxury Watermark footer with soft blue glow -->
        <div class="text-center mt-3.5 space-y-0.5">
            <p class="text-[11px] text-blue-200/80 font-medium tracking-wide">
                &copy; {{ date('Y') }} Hokigo WiFi &bull; Sistem Terpadu
            </p>
            <p class="text-[10px] text-blue-300/40">
                Layanan Internet Cepat & Stabil
            </p>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
