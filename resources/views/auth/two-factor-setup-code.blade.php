@php
    $appLogoUrl = $organizationProfile?->logo_url ?: asset('images/sipakem-logo.png');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konfirmasi Verifikasi Dua Langkah - {{ $organizationProfile?->portal_name ?? 'SIPAKEM' }}</title>
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
                    <p class="login-brand-caption">Konfirmasi kode keamanan</p>
                </div>

                <form class="login-form p-4 pt-0" method="post" action="{{ route('two-factor.setup.store') }}">
                    @csrf
                    <p class="small text-muted mb-3">
                        Masuk sebagai <strong>{{ $user->email }}</strong>. Masukkan 6 digit kode pertama dari Google Authenticator untuk menyelesaikan aktivasi.
                    </p>

                    <div class="mb-4">
                        <label class="form-label auth-form-label" for="code">Kode verifikasi <span class="text-danger">*</span></label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i data-lucide="shield-check"></i></span>
                            <input class="form-control auth-code-input @error('code') is-invalid @enderror" id="code" type="text" name="code" value="{{ old('code') }}" inputmode="numeric" pattern="[0-9 ]*" maxlength="12" placeholder="000000" autocomplete="one-time-code" required autofocus>
                        </div>
                        @error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="form-text">Kode berubah setiap beberapa detik di aplikasi Google Authenticator.</div>
                    </div>

                    <button class="btn btn-pusaka w-100" type="submit"><i data-lucide="shield-check" class="me-1"></i> Aktifkan dan Masuk</button>
                </form>

                <div class="login-form px-4 pb-4 pt-0 text-center">
                    <a class="fw-semibold text-pusaka-navy text-decoration-none" href="{{ route('two-factor.setup') }}">Kembali scan QR</a>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
