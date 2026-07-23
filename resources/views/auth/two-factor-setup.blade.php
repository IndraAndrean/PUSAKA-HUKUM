@php
    $appLogoUrl = $organizationProfile?->logo_url ?: asset('images/sipakem-logo.png');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aktivasi Verifikasi Dua Langkah - {{ $organizationProfile?->portal_name ?? 'SIPAKEM' }}</title>
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
                    <p class="login-brand-caption">Aktivasi Google Authenticator</p>
                </div>

                <div class="login-form p-4 pt-0">
                    @if(session('info'))
                        <div class="rounded-xl border border-pusaka-navy/20 bg-pusaka-soft px-4 py-3 text-sm font-semibold text-pusaka-navy mb-4">
                            {{ session('info') }}
                        </div>
                    @endif

                    <p class="small text-muted mb-4">
                        Masuk sebagai <strong>{{ $user->email }}</strong>. Pindai QR berikut dengan aplikasi Google Authenticator untuk mengaktifkan keamanan akun.
                    </p>

                    <div class="mb-4 text-center">
                        <div class="two-factor-qr mx-auto mb-3">
                            {!! $qrCodeSvg !!}
                        </div>
                        <p class="mb-2 text-sm font-semibold text-slate-800">Kunci manual</p>
                        <div class="two-factor-secret">{{ $manualKey }}</div>
                        <p class="form-text mb-0 mt-2">Gunakan kunci ini jika kamera ponsel tidak bisa memindai QR.</p>
                    </div>

            <a class="btn btn-pusaka w-100 text-white hover:text-white" style="color: #fff !important;" href="{{ route('two-factor.setup.confirm') }}">
                <span class="text-white">Lanjut masukkan kode</span>
                <i data-lucide="arrow-right" class="ms-1 text-white"></i>
            </a>
                </div>

                <form class="login-form px-4 pb-4 pt-0 text-center" method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="border-0 bg-transparent p-0 fw-semibold text-pusaka-navy" type="submit">Keluar dari akun ini</button>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>
