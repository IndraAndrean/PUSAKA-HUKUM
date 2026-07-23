@extends('layouts.admin')

@php($isEdit = $documentDivision->exists)

@section('title', $isEdit ? 'Edit Bidang/Subbidang' : 'Tambah Bidang/Subbidang')
@section('page_title', $isEdit ? 'Edit Bidang/Subbidang' : 'Tambah Bidang/Subbidang')

@section('content')
<form class="content-card p-3" method="post" action="{{ $isEdit ? route('admin.document-divisions.update', $documentDivision) : route('admin.document-divisions.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif
    <div class="row g-3">
        <div class="col-lg-6">
            <label class="form-label" for="name">Nama Bidang/Subbidang</label>
            <input class="form-control" id="name" name="name" value="{{ old('name', $documentDivision->name) }}" placeholder="Contoh: Bankum" required>
            <div class="form-text">Gunakan nama unit pengampu dokumen sesuai struktur Bidkum atau kebutuhan instansi.</div>
        </div>
        <div class="col-lg-6">
            <label class="form-label" for="code">Kode Bidang</label>
            <input class="form-control text-lowercase" id="code" name="code" value="{{ old('code', $documentDivision->code) }}" placeholder="Contoh: bankum" required>
            <div class="form-text">Gunakan huruf kecil, angka, garis bawah, atau tanda hubung. Kode dipakai sebagai nilai metadata dokumen.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="5" placeholder="Contoh: Bidang/Subbidang bantuan hukum yang mengelola dokumen bantuan hukum dan arahan terkait perkara.">{{ old('description', $documentDivision->description) }}</textarea>
            <div class="form-text">Deskripsi membantu admin memilih bidang/subbidang yang tepat saat input dokumen.</div>
        </div>
        <div class="col-12 d-flex justify-content-end">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
        </div>
    </div>
</form>
@endsection
