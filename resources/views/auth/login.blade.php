<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk - Hokigo WiFi Management</title>
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
                            dark: '#141414',
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
<body class="bg-gradient-to-br from-[#141414] via-[#1a1c23] to-[#0f172a] min-h-[100dvh] flex flex-col justify-center items-center p-3 sm:p-6 antialiased text-slate-800">

    <div class="w-full max-w-[420px] my-auto">
        
        <!-- Main Card with responsive padding & radius -->
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl p-5 sm:p-7 border border-white/10 relative overflow-hidden backdrop-blur-md">
            
            <!-- Top Accent Glow -->
            <div class="absolute -top-12 -right-12 w-36 h-36 bg-blue-500/15 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Logo & Brand Header (Optimized for Android & PC) -->
            <div class="text-center mb-4 sm:mb-5">
                <div class="inline-flex items-center justify-center p-2 rounded-xl bg-brand-dark shadow-md mb-2.5 border border-white/10">
                    <img src="{{ asset('images/logo.png') }}" alt="Hokigo Logo" class="h-8 sm:h-10 w-auto object-contain">
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Selamat Datang di Hokigo</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Sistem Manajemen Pelanggan, Tagihan & Keuangan</p>
            </div>

            <!-- Alert Flash Messages -->
            @if(isset($errors) && $errors->any())
                <div class="mb-3.5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2.5">
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
            <form action="{{ route('login.post') }}" method="POST" class="space-y-3 sm:space-y-3.5" x-data="{ showPassword: false }">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Email Akun</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email', 'owner@hokigo.net') }}" required
                               placeholder="nama@hokigo.net"
                               class="w-full pl-10 pr-3.5 py-2.5 sm:py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue text-xs sm:text-sm transition">
                    </div>
                </div>

                <!-- Password Input with Toggle Show/Hide Password -->
                <div>
                    <label for="password" class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" value="password" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-11 py-2.5 sm:py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue text-xs sm:text-sm transition">
                        
                        <!-- Toggle Password Visibility Button -->
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-brand-blue transition focus:outline-none"
                                title="Lihat / Sembunyikan Kata Sandi"
                                aria-label="Toggle password visibility">
                            <i x-show="!showPassword" data-lucide="eye" class="w-4 h-4"></i>
                            <i x-show="showPassword" x-cloak data-lucide="eye-off" class="w-4 h-4 text-brand-blue"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-brand-blue focus:ring-brand-blue">
                        <span class="text-[11px] sm:text-xs">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-brand-blue hover:bg-brand-blueHover active:scale-[0.99] text-white rounded-xl font-bold text-xs sm:text-sm tracking-wide shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Dashboard</span>
                </button>
            </form>

            <!-- Quick Demo Credentials Box (Responsive grid) -->
            <div class="mt-4 pt-3.5 border-t border-slate-100">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 text-center mb-2">Akun Akses Cepat</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillAccount('owner@hokigo.net', 'password')" 
                            class="px-2.5 py-2 bg-slate-50 hover:bg-slate-100 active:bg-slate-200 border border-slate-200/80 rounded-xl text-left transition group">
                        <span class="block text-[11px] font-bold text-slate-800 flex items-center gap-1.5 truncate">
                            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span> Owner (Saya)
                        </span>
                        <span class="text-[10px] text-slate-500 font-mono truncate block">owner@hokigo.net</span>
                    </button>
                    
                    <button type="button" onclick="fillAccount('admin@hokigo.net', 'password')" 
                            class="px-2.5 py-2 bg-slate-50 hover:bg-slate-100 active:bg-slate-200 border border-slate-200/80 rounded-xl text-left transition group">
                        <span class="block text-[11px] font-bold text-slate-800 flex items-center gap-1.5 truncate">
                            <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span> Admin (Staff)
                        </span>
                        <span class="text-[10px] text-slate-500 font-mono truncate block">admin@hokigo.net</span>
                    </button>
                </div>
                <p class="text-[10px] text-slate-400 text-center mt-1.5">Password default: <code class="bg-slate-100 px-1 py-0.2 rounded text-slate-700 font-mono">password</code></p>
            </div>

        </div>

        <!-- Watermark footer -->
        <p class="text-center text-[11px] text-slate-400 mt-3 font-medium">
            &copy; {{ date('Y') }} Hokigo WiFi &bull; Sistem Terpadu
        </p>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        function fillAccount(email, password) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            emailInput.value = email;
            passInput.value = password;

            // Subtle flash effect on inputs
            emailInput.classList.add('ring-2', 'ring-brand-blue');
            passInput.classList.add('ring-2', 'ring-brand-blue');
            setTimeout(() => {
                emailInput.classList.remove('ring-2', 'ring-brand-blue');
                passInput.classList.remove('ring-2', 'ring-brand-blue');
            }, 400);
        }
    </script>
</body>
</html>
