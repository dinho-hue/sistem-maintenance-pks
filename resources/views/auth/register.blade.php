<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="auth-field">
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="auth-field">
            <x-input-label for="password" value="Kata Sandi" />
            <div class="password-input-wrap">
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" placeholder="Buat kata sandi" />
                <button class="password-toggle" type="button" data-target="password"
                        aria-label="Tampilkan kata sandi" title="Tampilkan kata sandi">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="auth-field">
            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
            <div class="password-input-wrap">
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <button class="password-toggle" type="button" data-target="password_confirmation"
                        aria-label="Tampilkan kata sandi" title="Tampilkan kata sandi">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="auth-register-actions">
            <a href="{{ route('login') }}">
                Sudah punya akun? Masuk
            </a>
            <x-primary-button class="auth-submit">Daftar</x-primary-button>
        </div>
    </form>
</x-guest-layout>
