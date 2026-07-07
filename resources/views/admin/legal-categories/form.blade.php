@extends('layouts.admin')

@php($isEdit = $legalCategory->exists)

@section('title', $isEdit ? 'Edit Kategori Hukum' : 'Tambah Kategori Hukum')
@section('page_title', $isEdit ? 'Edit Kategori Hukum' : 'Tambah Kategori Hukum')

@section('page_actions')
    <a class="btn btn-outline-secondary" href="{{ route('admin.legal-categories.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection

@section('content')
<form class="content-card p-3" method="post" action="{{ $isEdit ? route('admin.legal-categories.update', $legalCategory) : route('admin.legal-categories.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif
    <div class="row g-3">
        <div class="col-lg-7">
            <label class="form-label" for="name">Nama Kategori Hukum</label>
            <input class="form-control" id="name" name="name" value="{{ old('name', $legalCategory->name) }}" placeholder="Contoh: Hukum Siber" required>
            <div class="form-text">Slug URL dibuat otomatis dari nama.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="5">{{ old('description', $legalCategory->description) }}</textarea>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.legal-categories.index') }}">Batal</a>
        </div>
    </div>
</form>
@endsection
