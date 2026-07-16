@extends('layouts.admin')

@php($isEdit = $legalCategory->exists)

@section('title', $isEdit ? 'Edit Kategori Hukum' : 'Tambah Kategori Hukum')
@section('page_title', $isEdit ? 'Edit Kategori Hukum' : 'Tambah Kategori Hukum')

@section('content')
<form class="content-card p-3" method="post" action="{{ $isEdit ? route('admin.legal-categories.update', $legalCategory) : route('admin.legal-categories.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif
    <div class="row g-3">
        <div class="col-lg-7">
            <label class="form-label" for="name">Nama Kategori Hukum</label>
            <input class="form-control" id="name" name="name" value="{{ old('name', $legalCategory->name) }}" placeholder="Contoh: Hukum Pidana / Hukum Siber / Bantuan Hukum" required>
            <div class="form-text">Kategori dipakai untuk filter dokumen dan artikel hukum. Slug URL dibuat otomatis dari nama.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="5" placeholder="Contoh: Kategori untuk dokumen yang berkaitan dengan penyidikan, pembuktian, dan penanganan perkara pidana.">{{ old('description', $legalCategory->description) }}</textarea>
            <div class="form-text">Isi ringkasan cakupan kategori agar pengelola tidak salah mengelompokkan dokumen.</div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
        </div>
    </div>
</form>
@endsection
