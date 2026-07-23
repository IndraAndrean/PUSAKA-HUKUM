@extends('layouts.app')

@section('title', 'Verifikasi Dua Langkah - SIPAKEM')

@section('content')
<section class="py-5">
    <div class="container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item"><a href="{{ route('profile.edit') }}">Profil Saya</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">Verifikasi Dua Langkah</span>
        </nav>

        <div class="mb-4">
            <h1 class="h3">Verifikasi Dua Langkah</h1>
            <p class="text-muted mb-0">Tambahkan kode Google Authenticator agar akun lebih aman saat masuk.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Periksa kembali input.</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="item-card p-4">
                    @if($user->hasTwoFactorEnabled())
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="feature-icon flex-shrink-0"><i data-lucide="shield-check"></i></div>
                            <div>
                                <h2 class="h5 mb-1">Verifikasi dua langkah aktif</h2>
                                <p class="text-muted mb-0">Setiap login berikutnya akan meminta kode 6 digit dari Google Authenticator.</p>
                            </div>
                        </div>

                        <form method="post" action="{{ route('profile.two-factor.destroy') }}">
                            @csrf
                            @method('delete')
                            <div class="mb-3">
                                <label class="form-label" for="current_password">Kata Sandi Saat Ini</label>
                                <input class="form-control" id="current_password" type="password" name="current_password" placeholder="Masukkan kata sandi saat ini" autocomplete="current-password" required>
                                <div class="form-text">Diperlukan untuk menonaktifkan verifikasi dua langkah.</div>
                            </div>
                            <button class="btn btn-outline-danger" type="submit"><i data-lucide="shield-off"></i> Nonaktifkan Verifikasi</button>
                        </form>
                    @else
                        <h2 class="h5 mb-3">Aktifkan Google Authenticator</h2>
                        <div class="two-factor-setup-grid mb-4">
                            <div class="text-center">
                                <div class="two-factor-qr">{!! $qrCodeSvg !!}</div>
                            </div>
                            <div>
                                <p class="text-muted mb-2">Pindai QR berikut memakai aplikasi Google Authenticator. Jika kamera tidak bisa digunakan, masukkan kunci manual ini.</p>
                                <div class="two-factor-secret">{{ $manualKey }}</div>
                            </div>
                        </div>

                        <form method="post" action="{{ route('profile.two-factor.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="current_password">Kata Sandi Saat Ini</label>
                                    <input class="form-control" id="current_password" type="password" name="current_password" placeholder="Masukkan kata sandi saat ini" autocomplete="current-password" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="code">Kode Verifikasi</label>
                                    <input class="form-control auth-code-input" id="code" type="text" name="code" value="{{ old('code') }}" inputmode="numeric" pattern="[0-9 ]*" maxlength="12" placeholder="000000" autocomplete="one-time-code" required>
                                    <div class="form-text">Masukkan 6 digit kode dari Google Authenticator.</div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-pusaka" type="submit"><i data-lucide="shield-check"></i> Aktifkan Verifikasi</button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="item-card p-4">
                    <h2 class="h5 mb-3">Cara Menggunakan</h2>
                    <ol class="two-factor-step-list mb-0">
                        <li>Buka aplikasi Google Authenticator di ponsel.</li>
                        <li>Pilih tambah akun, lalu pindai QR atau masukkan kunci manual.</li>
                        <li>Masukkan kode 6 digit yang muncul di aplikasi.</li>
                        <li>Simpan perubahan. Setelah aktif, login akan meminta kode tersebut.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
