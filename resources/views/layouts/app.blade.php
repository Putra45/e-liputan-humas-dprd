<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Liputan Humas DPRD Kota Tangerang')</title>

    <!-- Tailwind CSS (Vite build + CDN fallback for guarantee) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dprd: {
                            navy: '#0f2e5a',
                            navyDark: '#0a1d37',
                            blue: '#1d4ed8',
                            sky: '#0284c7',
                            light: '#f0f5fa',
                            accent: '#f59e0b'
                        }
                    }
                }
            }
        }
    </script>

    <!-- FullCalendar CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: #e2e8f0;
        }
        .fc-header-toolbar {
            margin-bottom: 1rem !important;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .fc-toolbar-title {
            font-size: 1.15rem !important;
            font-weight: 700 !important;
            color: #0f2e5a !important;
        }
        .fc-button-primary {
            background-color: #0f2e5a !important;
            border-color: #0f2e5a !important;
            text-transform: capitalize;
            font-weight: 600;
            border-radius: 0.5rem !important;
            padding: 0.35rem 0.85rem !important;
        }
        .fc-button-primary:hover {
            background-color: #1e40af !important;
            border-color: #1e40af !important;
        }
        .fc-button-active {
            background-color: #1e3a8a !important;
        }
        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
            font-size: 0.78rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
            transition: transform 0.15s ease;
        }
        .fc-event:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="bg-[#0f2e5a] text-white shadow-md border-b-4 border-amber-500 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand / Logo -->
                <a href="{{ route('portal.index') }}" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 rounded-xl bg-white/10 p-1 flex items-center justify-center border border-white/20 shadow-inner group-hover:bg-white/20 transition">
                        <!-- Tangerang Emblem / Official Logo representation -->
                        <svg class="w-8 h-8 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5zm0 4.18l6 3.33v4.49c0 3.7-2.55 7.17-6 8.12-3.45-.95-6-4.42-6-8.12v-4.49l6-3.33zM12 9a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold tracking-wider uppercase text-amber-400">Sekretariat DPRD Kota Tangerang</div>
                        <div class="text-lg font-bold leading-tight tracking-tight">TIM PUBLIKASI HUMAS</div>
                        <div class="text-[11px] text-slate-300">Sistem Pemesanan Peliputan & Kalender Publikasi</div>
                    </div>
                </a>

                <!-- Nav Links -->
                <nav class="flex items-center gap-2 sm:gap-4">
                    <a href="{{ route('portal.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('portal.*') ? 'bg-white/20 text-white font-semibold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                        Portal Dewan
                    </a>

                    @auth
                        <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.*') ? 'bg-amber-500 text-slate-900 font-bold' : 'text-slate-200 hover:bg-white/10' }}">
                            Dashboard Admin
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-lg text-sm font-medium text-rose-200 hover:bg-rose-950/40 hover:text-rose-100 transition">
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Login Admin
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 text-xs py-6 border-t border-slate-800 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Sub Bagian Humas dan Protokol — Sekretariat DPRD Kota Tangerang</span>
            </div>
            <div>
                © {{ date('Y') }} Sistem Pemesanan Peliputan Internal. Versi 1.0
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
