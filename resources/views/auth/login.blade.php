<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="auth-field">
            <x-input-label for="password" value="Kata Sandi" />
            <div class="password-input-wrap">
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" placeholder="Masukkan kata sandi" />
                <button class="password-toggle" type="button" data-target="password"
                        aria-label="Tampilkan kata sandi" title="Tampilkan kata sandi">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="auth-actions">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>
        <x-primary-button class="auth-submit">
                Masuk
            </x-primary-button>
    </form>

    <p class="auth-signup-link">
        Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
    </p>
</x-guest-layout>
