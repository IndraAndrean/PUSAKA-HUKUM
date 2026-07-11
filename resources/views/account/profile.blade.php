@extends('layouts.app')

@section('title', 'Profil Saya - PUSAKA HUKUM')

@section('content')
<section class="py-5">
    <div class="container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">Profil Saya</span>
        </nav>
        <div class="mb-4">
            <h1 class="h3">Profil Saya</h1>
            <p class="text-muted mb-0">Kelola identitas akun dan keamanan password.</p>
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
            <div class="col-lg-7">
                <form class="item-card p-4" method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('put')
                    <h2 class="h5 mb-3">Informasi Profil</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Nama</label>
                            <input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="satuan_kerja">Satuan Kerja</label>
                            <input class="form-control" id="satuan_kerja" name="satuan_kerja" value="{{ old('satuan_kerja', $user->satuan_kerja) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="jabatan">Jabatan</label>
                            <input class="form-control" id="jabatan" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="profile_current_password">Password Saat Ini</label>
                            <input class="form-control" id="profile_current_password" type="password" name="current_password">
                            <div class="form-text">Wajib diisi hanya ketika mengubah email.</div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-pusaka" type="submit"><i class="bi bi-save"></i> Simpan Profil</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-5">
                <form class="item-card p-4" method="post" action="{{ route('profile.password') }}">
                    @csrf
                    @method('put')
                    <h2 class="h5 mb-3">Ubah Password</h2>
                    <div class="mb-3">
                        <label class="form-label" for="password_current">Password Saat Ini</label>
                        <input class="form-control" id="password_current" type="password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password Baru</label>
                        <input class="form-control" id="password" type="password" name="password" minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                        <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" minlength="8" required>
                    </div>
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-key"></i> Ubah Password</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
