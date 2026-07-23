@php
    $appLogoUrl = $organizationProfile?->logo_url ?: asset('images/sipakem-logo.png');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Kata Sandi - {{ $organizationProfile?->portal_name ?? 'SIPAKEM' }}</title>
    <link rel="icon" type="image/png" href="{{ $appLogoUrl }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
<main class="auth-section min-h-screen py-5">
    <div class="container">
        <div class="mx-auto w-full max-w-md">
            <div class="item-card auth-card auth-login-card">
                <div class="login-brand-panel">
                    <img class="login-brand-logo" src="{{ $appLogoUrl }}" alt="Logo {{ $organizationProfile?->organization_name ?? 'SIPAKEM' }}">
                    <h1 class="login-brand-title">{{ $organizationProfile?->portal_name ?? 'SIPAKEM' }}</h1>
                    <p class="login-brand-subtitle">SISTEM INFORMASI ARSIP KONSULTASI EDUKASI DAN MANAJEMEN HUKUM</p>
                    <p class="login-brand-caption">Buat kata sandi baru untuk akun Anda</p>
                </div>
                <form class="login-form p-4 pt-0" method="post" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label auth-form-label" for="email">Email</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email', $email) }}" placeholder="Masukkan email pengguna" autocomplete="email" required autofocus>
                        </div>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label auth-form-label" for="password">Kata sandi baru</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" minlength="8" placeholder="Masukkan kata sandi baru" autocomplete="new-password" required>
                            <button class="auth-password-toggle" type="button" data-password-toggle data-password-target="#password" aria-label="Tampilkan kata sandi baru">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="form-text">Minimal 8 karakter.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label auth-form-label" for="password_confirmation">Konfirmasi kata sandi baru</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" minlength="8" placeholder="Ulangi kata sandi baru" autocomplete="new-password" required>
                            <button class="auth-password-toggle" type="button" data-password-toggle data-password-target="#password_confirmation" aria-label="Tampilkan konfirmasi kata sandi">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-pusaka w-100" type="submit"><i class="bi bi-check2-circle me-1"></i> Simpan Kata Sandi Baru</button>
                    <div class="text-center mt-4">
                        <a class="text-decoration-none fw-semibold" href="{{ route('login') }}">Kembali ke halaman masuk</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>
