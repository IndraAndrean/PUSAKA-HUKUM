@php
    $appLogoUrl = $organizationProfile?->logo_url ?: asset('images/sipakem-logo.png');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Kata Sandi - {{ $organizationProfile?->portal_name ?? 'SIPAKEM' }}</title>
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
                    <p class="login-brand-caption">Pemulihan kata sandi akun</p>
                </div>
                <form class="login-form p-4 pt-0" method="post" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label auth-form-label" for="email">Email</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email akun SIPAKEM" autocomplete="email" required autofocus>
                        </div>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="form-text">Jika email terdaftar, sistem akan mengirim tautan reset ke email tersebut.</div>
                    </div>
                    <button class="btn btn-pusaka w-100" type="submit"><i class="bi bi-send me-1"></i> Kirim Tautan Reset</button>
                    <div class="text-center mt-4">
                        <a class="text-decoration-none fw-semibold" href="{{ route('login') }}">Kembali ke halaman masuk</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@if(session('success'))
    <div class="modal auth-info-modal" id="passwordResetInfoModal" tabindex="-1" role="dialog" aria-modal="true" data-auto-open-modal>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="auth-modal-icon">
                    <i data-lucide="circle-check-big"></i>
                </div>
                <h2 class="auth-modal-title">Informasi Terkirim</h2>
                <p class="auth-modal-text">{{ session('success') }}</p>
                <button class="btn btn-pusaka auth-modal-button" type="button" data-ui-dismiss="modal">Oke</button>
            </div>
        </div>
    </div>
@endif
</body>
</html>
