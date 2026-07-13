<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin DLH - Simpasda')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
    </style>
    @stack('styles')
</head>
<body class="text-slate-800 antialiased flex flex-col min-h-screen">

    <nav class="bg-emerald-700 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center h-16">
            
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">🌱</span>
                    <div>
                        <h2 class="text-base font-bold tracking-tight leading-none">SIMPASDA</h2>
                        <span class="text-[10px] text-emerald-200 font-medium tracking-wider uppercase">Panel DLH</span>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-1 text-sm font-medium">
                    <a href="{{ route('dlh.dashboard') }}" class="px-3 py-2 rounded-lg transition-colors {{ Route::is('dlh.dashboard') ? 'bg-emerald-800 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-600/50 hover:text-white' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('dlh.bank-sampah.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ Route::is('dlh.bank-sampah.*') ? 'bg-emerald-800 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-600/50 hover:text-white' }}">
                        Bank Sampah
                    </a>
                    <a href="{{ route('dlh.aduan.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ Route::is('dlh.aduan.*') ? 'bg-emerald-800 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-600/50 hover:text-white' }}">
                        Kelola Aduan
                    </a>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex flex-col text-right text-xs">
                    <span class="font-semibold text-white">{{ Auth::user()->name ?? 'Petugas DLH Pusat' }}</span>
                    <span class="text-[10px] text-emerald-200 italic">Otoritas Dinas</span>
                </div>
                
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs bg-emerald-600 hover:bg-rose-600 border border-emerald-500/30 hover:border-rose-500/30 px-3 py-1.5 rounded-lg transition-all font-medium shadow-sm">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Simpasda & Dinas Lingkungan Hidup. All Rights Reserved.
    </footer>

    @stack('scripts')
</body>
</html>