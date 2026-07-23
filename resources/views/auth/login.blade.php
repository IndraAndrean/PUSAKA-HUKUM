@php
    $appLogoUrl = $organizationProfile?->logo_url ?: asset('images/sipakem-logo.png');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ $organizationProfile?->portal_name ?? 'SIPAKEM' }}</title>
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
                    <p class="login-brand-caption">Masuk untuk mengakses portal sesuai hak akun Anda</p>
                </div>
                <form class="login-form p-4 pt-0" method="post" action="{{ route('login.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label auth-form-label" for="email">Email</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email pengguna" autocomplete="email" required autofocus>
                        </div>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label auth-form-label" for="password">Kata sandi</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input class="form-control" id="password" type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                            <button class="auth-password-toggle" type="button" data-password-toggle data-password-target="#password" aria-label="Tampilkan kata sandi">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                        <div class="form-check mb-0">
                            <input class="form-check-input" id="remember" type="checkbox" name="remember">
                            <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
                        </div>
                        <a class="text-decoration-none fw-semibold" href="{{ route('password.request') }}">Lupa kata sandi?</a>
                    </div>
                    <button class="btn btn-pusaka w-100" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</button>
                    <div class="small text-muted mt-4">
                        Akun demo: superadmin@sipakem.test, admin@sipakem.test, internal@sipakem.test. Kata sandi: password.
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@if(session('success'))
    <div class="modal auth-info-modal" id="loginInfoModal" tabindex="-1" role="dialog" aria-modal="true" data-auto-open-modal>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="auth-modal-icon">
                    <i data-lucide="circle-check-big"></i>
                </div>
                <h2 class="auth-modal-title">Berhasil</h2>
                <p class="auth-modal-text">{{ session('success') }}</p>
                <button class="btn btn-pusaka auth-modal-button" type="button" data-ui-dismiss="modal">Oke</button>
            </div>
        </div>
    </div>
@endif
</body>
</html>
