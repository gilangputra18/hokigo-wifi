<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gradient-to-br from-[#141414] via-[#1a1c23] to-[#0f172a] min-h-screen flex items-center justify-center p-4 antialiased text-slate-800">

    <div class="w-full max-w-md">
        
        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 border border-white/10 relative overflow-hidden">
            
            <!-- Top Accent Glow -->
            <div class="absolute -top-16 -right-16 w-40 h-40 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Logo & Brand Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center p-3 rounded-2xl bg-brand-dark shadow-md mb-4 border border-white/10">
                    <img src="{{ asset('images/logo.png') }}" alt="Hokigo Logo" class="h-14 w-auto">
                </div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Selamat Datang di Hokigo</h2>
                <p class="text-xs text-slate-500 mt-1">Sistem Manajemen Pelanggan, Tagihan WiFi & Keuangan</p>
            </div>

            <!-- Error Alerts -->
            @if(isset($errors) && $errors->any())
                <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 text-rose-500"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 shrink-0 text-emerald-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Form Login -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Email Akun</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email', 'owner@hokigo.net') }}" required
                               placeholder="nama@hokigo.net"
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue text-sm transition">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input type="password" id="password" name="password" value="password" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue text-sm transition">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-blue focus:ring-brand-blue">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full py-3.5 px-4 bg-brand-blue hover:bg-brand-blueHover text-white rounded-xl font-bold text-sm tracking-wide shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Dashboard</span>
                </button>
            </form>

            <!-- Quick Demo Credentials Box -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-3">Akun Akses Cepat</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillAccount('owner@hokigo.net', 'password')" 
                            class="px-3 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-left transition group">
                        <span class="block text-xs font-bold text-slate-800 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Owner (Saya)
                        </span>
                        <span class="text-[10px] text-slate-500">owner@hokigo.net</span>
                    </button>
                    
                    <button type="button" onclick="fillAccount('admin@hokigo.net', 'password')" 
                            class="px-3 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-left transition group">
                        <span class="block text-xs font-bold text-slate-800 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Admin (Staff)
                        </span>
                        <span class="text-[10px] text-slate-500">admin@hokigo.net</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 text-center mt-2">Password default: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700">password</code></p>
            </div>

        </div>

        <!-- Footer watermark -->
        <p class="text-center text-xs text-slate-500 mt-6 font-medium">
            &copy; {{ date('Y') }} Hokigo WiFi. Solusi Internet Desa Masa Depan.
        </p>

    </div>

    <script>
        lucide.createIcons();
        function fillAccount(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
