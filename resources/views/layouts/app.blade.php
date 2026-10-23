<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Liputan Humas DPRD Kota Tangerang')</title>

    <!-- Dark Mode Initial Script -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
            -webkit-tap-highlight-color: transparent;
        }
        /* FullCalendar Custom Clean Styling */
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: #e2e8f0;
        }
        .fc-header-toolbar {
            margin-bottom: 1.25rem !important;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }
        .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 800 !important;
            color: #0f2e5a !important;
            letter-spacing: -0.025em;
        }
        .fc-button-primary {
            background-color: #0f2e5a !important;
            border-color: #0f2e5a !important;
            text-transform: capitalize;
            font-weight: 600;
            font-size: 0.85rem !important;
            border-radius: 0.75rem !important;
            padding: 0.45rem 0.95rem !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease;
        }
        .fc-button-primary:hover {
            background-color: #1e3a8a !important;
            border-color: #1e3a8a !important;
            transform: translateY(-1px);
        }
        .fc-button-active {
            background-color: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }
        .fc-event {
            cursor: pointer;
            border-radius: 6px;
            padding: 3px 6px;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.3;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            margin-bottom: 2px !important;
        }
        .fc-event:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.12);
        }
        .fc-daygrid-day-number {
            font-weight: 600;
            font-size: 0.85rem;
            color: #475569;
            padding: 4px 8px !important;
        }
        .fc-day-today {
            background-color: rgba(239, 246, 255, 0.7) !important;
        }
        /* FullCalendar List View */
        .fc-list-event {
            cursor: pointer;
        }
        .fc-list-event-dot {
            border-radius: 50% !important;
        }
        .fc-list-day-cushion {
            background-color: #f1f5f9 !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            color: #0f2e5a !important;
        }
        /* Dark Mode Calendar adjustments */
        .dark .fc-theme-standard td, .dark .fc-theme-standard th {
            border-color: #334155 !important;
        }
        .dark .fc-toolbar-title {
            color: #f8fafc !important;
        }
        .dark .fc-col-header-cell-cushion, .dark .fc-daygrid-day-number {
            color: #cbd5e1 !important;
        }
        .dark .fc-day-today {
            background-color: rgba(30, 58, 138, 0.3) !important;
        }
        .dark .fc-list-day-cushion {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }
        .dark .fc-list-event:hover td {
            background-color: #1e293b !important;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-200">

    <!-- Top Navigation Bar -->
    <header class="bg-[#0f2e5a] text-white shadow-md border-b-4 border-amber-500 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand / Logo -->
                <a href="{{ route('portal.index') }}" class="flex items-center gap-2.5 sm:gap-3 group">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-white/10 p-1 flex items-center justify-center border border-white/20 shadow-inner group-hover:bg-white/20 transition shrink-0">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5zm0 4.18l6 3.33v4.49c0 3.7-2.55 7.17-6 8.12-3.45-.95-6-4.42-6-8.12v-4.49l6-3.33zM12 9a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[10px] sm:text-[11px] font-bold tracking-wider uppercase text-amber-400">DPRD KOTA TANGERANG</div>
                        <div class="text-sm sm:text-base font-extrabold leading-tight tracking-tight">E-LIPUTAN HUMAS</div>
                        <div class="hidden sm:block text-[10px] text-blue-200">Sistem Penjadwalan & Dokumentasi Publikasi</div>
                    </div>
                </a>

                <!-- Nav Links & Actions -->
                <nav class="flex items-center gap-2 sm:gap-3">
                    <!-- Theme Toggle Button -->
                    <button type="button" id="theme-toggle" class="p-2 sm:p-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-amber-300 border border-white/20 transition" title="Ganti Mode Gelap / Terang">
                        <!-- Sun icon (shown when dark) -->
                        <svg id="theme-toggle-light-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Moon icon (shown when light) -->
                        <svg id="theme-toggle-dark-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <a href="{{ route('portal.index') }}" class="px-3 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('portal.*') ? 'bg-white/20 text-white' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        Portal
                    </a>

                    @auth
                        <a href="{{ route('admin.orders.index') }}" class="px-3 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm font-bold transition {{ request()->routeIs('admin.*') ? 'bg-amber-400 text-slate-950' : 'bg-amber-500 hover:bg-amber-400 text-slate-950' }}">
                            Admin
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-rose-200 hover:bg-rose-950/40 hover:text-rose-100 transition">
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-1.5 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden xs:inline">Login</span> Admin
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

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        function updateToggleIcons() {
            if (document.documentElement.classList.contains('dark')) {
                themeToggleLightIcon.classList.remove('hidden');
                themeToggleDarkIcon.classList.add('hidden');
            } else {
                themeToggleLightIcon.classList.add('hidden');
                themeToggleDarkIcon.classList.remove('hidden');
            }
        }

        updateToggleIcons();

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
                updateToggleIcons();
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
