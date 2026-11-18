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
<body class="auth-body">
    <main class="auth-page">
        <div class="auth-glow"></div>
        <section class="auth-card">
            <a href="{{ route('login') }}" class="auth-brand">
                <img src="{{ asset('images/wilmar-auth-logo.png') }}" alt="Wilmar">
                <strong>PT. BUMI SAWIT KENCANA</strong>
            </a>
            <div class="auth-title">
                <h1>{{ request()->routeIs('register') ? 'Buat Akun' : 'Selamat Datang' }}</h1>
                <p>{{ request()->routeIs('register') ? 'Daftarkan akun untuk mengakses sistem.' : 'Masuk untuk melanjutkan ke dashboard.' }}</p>
            </div>
            {{ $slot }}
            <p class="auth-footer">Sistem Informasi Manajemen Perawatan Mesin PKS</p>
        </section>
    </main>
    <script>
        document.querySelectorAll('.password-toggle').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.dataset.target);
                const isVisible = input.type === 'text';

                input.type = isVisible ? 'password' : 'text';
                toggle.innerHTML = `<i class="fa-regular ${isVisible ? 'fa-eye' : 'fa-eye-slash'}" aria-hidden="true"></i>`;
                toggle.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
                toggle.setAttribute('title', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
            });
        });
    </script>
</body>
</html>
