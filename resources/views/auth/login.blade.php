@extends('layouts.app')

@section('title', 'Login - PUSAKA HUKUM')

@section('content')
<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="item-card auth-card">
                    <div class="auth-card-header p-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="brand-mark">PH</span>
                            <div>
                                <h1 class="h4 mb-1">Masuk ke PUSAKA HUKUM</h1>
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
                            <label class="form-label" for="password">Password</label>
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
                            Akun demo: superadmin@pusakahukum.test, admin@pusakahukum.test, internal@pusakahukum.test. Password: password.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
