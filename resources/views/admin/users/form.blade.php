@extends('layouts.admin')

@php($isEdit = $user->exists)

@section('title', $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('page_title', $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
<form class="content-card p-3" method="post" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="name">Nama</label>
            <input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Contoh: AKP Rini Astuti, S.H." required>
            <div class="form-text">Isi nama lengkap sesuai identitas kedinasan atau penugasan.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Contoh: rini.astuti@polri.go.id" required>
            <div class="form-text">Email digunakan untuk login dan harus unik untuk setiap akun.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="satuan_kerja">Satuan Kerja</label>
            <input class="form-control" id="satuan_kerja" name="satuan_kerja" value="{{ old('satuan_kerja', $user->satuan_kerja) }}" placeholder="Contoh: Bidkum Polda Lampung / Polres Lampung Selatan">
            <div class="form-text">Isi unit kerja untuk memudahkan identifikasi pengguna internal.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="jabatan">Jabatan</label>
            <input class="form-control" id="jabatan" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" placeholder="Contoh: Kasubbidbankum / Admin Pengelola">
            <div class="form-text">Jabatan membantu audit akses dan riwayat pengelolaan dokumen.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="role">Role</label>
            <select class="form-select" id="role" name="role" required @disabled($isEdit && auth()->user()->is($user))>
                <option value="super_admin" @selected(old('role', $user->role) === 'super_admin')>Super Admin</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin/Pengelola</option>
                <option value="internal" @selected(old('role', $user->role ?: 'internal') === 'internal')>User Internal</option>
            </select>
            @if($isEdit && auth()->user()->is($user))
                <input type="hidden" name="role" value="super_admin">
            @endif
            <div class="form-text">Super Admin mengelola seluruh sistem, Admin mengelola konten, User Internal mengakses dokumen internal.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="is_active">Status Akun</label>
            <select class="form-select" id="is_active" name="is_active" required @disabled($isEdit && auth()->user()->is($user))>
                <option value="1" @selected((string) old('is_active', (int) ($user->is_active ?? true)) === '1')>Aktif</option>
                <option value="0" @selected((string) old('is_active', (int) $user->is_active) === '0')>Nonaktif</option>
            </select>
            @if($isEdit && auth()->user()->is($user))
                <input type="hidden" name="is_active" value="1">
            @endif
            <div class="form-text">Nonaktifkan akun jika pengguna tidak lagi bertugas atau akses perlu dihentikan sementara.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password">Kata Sandi {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}</label>
            <input class="form-control" id="password" type="password" name="password" placeholder="Minimal 8 karakter" @required(! $isEdit)>
            <div class="form-text">Gunakan kata sandi yang tidak mudah ditebak dan jangan dibagikan ke pengguna lain.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi kata sandi yang sama" @required(! $isEdit)>
            <div class="form-text">Harus sama persis dengan kata sandi baru.</div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
        </div>
    </div>
</form>
@endsection
