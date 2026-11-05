<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Dibatasi | Hokigo WiFi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl text-center space-y-6">
        
        <!-- Icon Badge -->
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mx-auto shadow-inner">
            <i data-lucide="shield-alert" class="w-8 h-8"></i>
        </div>

        <!-- Title & Code -->
        <div class="space-y-2">
            <span class="text-xs font-mono font-bold uppercase tracking-widest px-3 py-1 rounded-full bg-slate-800 text-slate-400 border border-slate-700">
                Error 403 &bull; Akses Dibatasi
            </span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight pt-2">
                Hak Akses Dibatasi
            </h1>
            <p class="text-xs text-slate-400 leading-relaxed">
                Halaman ini hanya dapat dibuka oleh akun dengan wewenang <strong>Owner</strong> atau <strong>Admin</strong> (seperti Pengaturan Sistem, Keuangan, dan Tagihan).
            </p>
        </div>

        @auth
        <!-- Current User Info -->
        <div class="bg-slate-800/80 rounded-2xl p-4 border border-slate-700/80 text-left space-y-1.5 text-xs">
            <div class="flex items-center justify-between">
                <span class="text-slate-400">Akun yang sedang login:</span>
                <span class="px-2 py-0.5 rounded-md font-bold text-[10px] uppercase {{ auth()->user()->isTeknisi() ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'bg-brand-blue/20 text-blue-300' }}">
                    {{ auth()->user()->role }}
                </span>
            </div>
            <p class="font-extrabold text-white text-sm truncate">{{ auth()->user()->name }}</p>
            <p class="font-mono text-slate-400 text-[11px] truncate">{{ auth()->user()->email }}</p>
        </div>
        @endauth

        <!-- Navigation Action Buttons -->
        <div class="space-y-2 pt-2">
            <a href="{{ route('maps.index') }}" 
               class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-lg shadow-blue-600/25">
                <i data-lucide="map-pinned" class="w-4 h-4"></i>
                <span>Buka Peta & Penitikan Satelit</span>
            </a>

            <a href="{{ route('customers.index') }}" 
               class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs flex items-center justify-center gap-2 transition border border-slate-700">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Buka Data Pelanggan</span>
            </a>

            @auth
            <form action="{{ route('logout') }}" method="POST" class="pt-2">
                @csrf
                <button type="submit" class="w-full py-2 px-3 text-xs text-slate-400 hover:text-rose-400 font-semibold transition flex items-center justify-center gap-1.5">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                    <span>Keluar & Ganti Akun (Owner / Admin)</span>
                </button>
            </form>
            @endauth
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>