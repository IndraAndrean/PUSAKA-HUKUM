@extends('layouts.admin')

@php($isEdit = $user->exists)

@section('title', $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('page_title', $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('page_actions')
    <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection

@section('content')
<form class="content-card p-3" method="post" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif
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
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password">Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}</label>
            <input class="form-control" id="password" type="password" name="password" @required(! $isEdit)>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" @required(! $isEdit)>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Batal</a>
        </div>
    </div>
</form>
@endsection
