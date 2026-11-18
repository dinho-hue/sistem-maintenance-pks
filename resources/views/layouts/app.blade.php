<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Monitoring HM') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/css/monitoring.css', 'resources/js/app.js'])
</head>
<body class="monitoring-body">
    <div class="monitoring-app" id="monitoringApp">
        <button class="sidebar-overlay" id="sidebarOverlay" type="button" aria-label="Tutup menu"></button>

        <aside class="sidebar" id="sidebar">
            <a class="brand" href="{{ route('hm.input') }}">
                <span class="brand-logo"><img src="{{ asset('images/wilmar-auth-logo.png') }}" alt="Wilmar"></span>
                <span class="brand-text">
                    <strong>PT. BUMI SAWIT KENCANA</strong>
                </span>
            </a>

            <nav class="menu" aria-label="Navigasi utama">
                <span class="menu-label">MENU UTAMA</span>
                <a class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fa-solid fa-house"></i><span>Dashboard</span>
                </a>

                <div class="menu-group">
                    <button class="menu-item menu-parent expanded" type="button" aria-expanded="true">
                        <i class="fa-solid fa-database"></i><span>Data Master</span>
                        <i class="fa-solid fa-chevron-down chevron-icon"></i>
                    </button>
                    <div class="submenu open">
                        <a class="menu-subitem {{ request()->routeIs('stations.*') ? 'active' : '' }}" href="{{ route('stations.index') }}">
                            <i class="fa-solid fa-industry"></i><span>Data Stasiun</span>
                        </a>
                        <a class="menu-subitem {{ request()->routeIs('machines.*') ? 'active' : '' }}" href="{{ route('machines.index') }}">
                            <i class="fa-solid fa-gears"></i><span>Data Mesin</span>
                        </a>
                        <a class="menu-subitem {{ request()->routeIs('components.*') ? 'active' : '' }}" href="{{ route('components.index') }}">
                            <i class="fa-solid fa-screwdriver-wrench"></i><span>Data Komponen</span>
                        </a>
                        <a class="menu-subitem {{ request()->routeIs('component-replacements.*') ? 'active' : '' }}" href="{{ route('component-replacements.index') }}">
                            <i class="fa-solid fa-clock-rotate-left"></i><span>Riwayat Pergantian</span>
                        </a>
                    </div>
                </div>

                <div class="menu-group">
                    <button class="menu-item menu-parent expanded" type="button" aria-expanded="true">
                        <i class="fa-solid fa-screwdriver-wrench"></i><span>Maintenance</span>
                        <i class="fa-solid fa-chevron-down chevron-icon"></i>
                    </button>
                    <div class="submenu open">
                        <a class="menu-subitem {{ request()->routeIs('check-ups.*') ? 'active' : '' }}" href="{{ route('check-ups.index') }}">
                            <i class="fa-solid fa-clipboard-check"></i><span>Check Up</span>
                        </a>
                        <a class="menu-subitem {{ request()->routeIs('hm.input') ? 'active' : '' }}" href="{{ route('hm.input') }}">
                            <i class="fa-solid fa-gauge-high"></i><span>Check HM</span>
                        </a>
                    </div>
                </div>

                <div class="menu-group">
                    <button class="menu-item menu-parent expanded" type="button" aria-expanded="true">
                        <i class="fa-solid fa-file-lines"></i><span>Laporan</span>
                        <i class="fa-solid fa-chevron-down chevron-icon"></i>
                    </button>
                    <div class="submenu open">
                        <a class="menu-subitem {{ request()->routeIs('hm.reports') ? 'active' : '' }}" href="{{ route('hm.reports') }}">
                            <i class="fa-solid fa-file-excel"></i><span>Laporan</span>
                        </a>
                    </div>
                </div>

                <div class="menu-group">
                    <button class="menu-item menu-parent expanded" type="button" aria-expanded="true">
                        <i class="fa-solid fa-gear"></i><span>Pengaturan</span>
                        <i class="fa-solid fa-chevron-down chevron-icon"></i>
                    </button>
                    <div class="submenu open">
                        <a class="menu-subitem" href="{{ route('profile.edit') }}">
                            <i class="fa-solid fa-user-gear"></i><span>Profil Pengguna</span>
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button class="menu-item logout-link" type="submit">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i><span>Logout</span>
                    </button>
                </form>
            </nav>

            <div class="sidebar-watermark" aria-hidden="true">
                <img src="{{ asset('images/palm-watermark.svg') }}" alt="">
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="mobile-menu" id="mobileMenu" type="button" aria-label="Buka navigasi" aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="topbar-heading">
                        @isset($header)
                            {{ $header }}
                        @else
                            <h1>Dashboard</h1>
                            <p>Sistem Informasi Manajemen Perawatan Mesin PKS</p>
                        @endisset
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-context">
                        <span class="status-dot"></span>
                        <span>Sistem Monitoring HM</span>
                    </div>
                    <div class="profile">
                        <div class="avatar"><i class="fa-solid fa-user"></i></div>
                        <div class="profile-details">
                            <strong>{{ Auth::user()->name }}</strong>
                            <small>{{ Auth::user()->email }}</small>
                        </div>
                        <a class="profile-settings" href="{{ route('profile.edit') }}" aria-label="Pengaturan profil">
                            <i class="fa-solid fa-chevron-down"></i>
                        </a>
                    </div>
                </div>
            </header>

            <section class="content">
                {{ $slot }}
            </section>
            <footer class="app-footer">© {{ date('Y') }} PT. Bumi Sawit Kencana · Monitoring Hourmeter Mesin</footer>
        </main>
    </div>

    <script>
        const appShell = document.getElementById('monitoringApp');
        const sidebar = document.getElementById('sidebar');
        const mobileMenu = document.getElementById('mobileMenu');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        mobileMenu.addEventListener('click', () => {
            const isOpen = sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('active', isOpen);
            mobileMenu.setAttribute('aria-expanded', String(isOpen));
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
            mobileMenu.setAttribute('aria-expanded', 'false');
        });

        document.querySelectorAll('.menu-parent').forEach((button) => {
            button.addEventListener('click', () => {
                const submenu = button.nextElementSibling;
                const isOpen = submenu.classList.toggle('open');
                button.classList.toggle('expanded', isOpen);
                button.setAttribute('aria-expanded', String(isOpen));
            });
        });
    </script>
</body>
</html>
