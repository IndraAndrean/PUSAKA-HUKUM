<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ $organizationProfile?->portal_name ?? 'PUSAKA HUKUM' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100">
<main class="auth-section min-h-screen py-5">
    <div class="container">
        <div class="mx-auto w-full max-w-lg">
            <div class="item-card auth-card">
                <div class="auth-card-header p-4">
                    <div class="d-flex align-items-center gap-3">
                        @if($organizationProfile?->logo_url)
                            <img class="brand-logo" src="{{ $organizationProfile->logo_url }}" alt="Logo {{ $organizationProfile->organization_name }}">
                        @else
                            <span class="brand-mark">PH</span>
                        @endif
                        <div>
                            <h1 class="h4 mb-1">Masuk ke {{ $organizationProfile?->portal_name ?? 'PUSAKA HUKUM' }}</h1>
                            <p class="mb-0 text-white-50 small">Akses akun internal dan administrasi portal</p>
                        </div>
                    </div>
                </div>
                <form class="p-4" method="post" action="{{ route('login.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Kata sandi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                            <input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required>
                        </div>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" id="remember" type="checkbox" name="remember">
                        <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
                    </div>
                    <button class="btn btn-pusaka w-100" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</button>
                    <div class="small text-muted mt-4">
                        Akun demo: superadmin@pusakahukum.test, admin@pusakahukum.test, internal@pusakahukum.test. Kata sandi: password.
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>
