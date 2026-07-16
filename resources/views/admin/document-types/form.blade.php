@extends('layouts.admin')

@php($isEdit = $documentType->exists)

@section('title', $isEdit ? 'Edit Jenis Dokumen' : 'Tambah Jenis Dokumen')
@section('page_title', $isEdit ? 'Edit Jenis Dokumen' : 'Tambah Jenis Dokumen')

@section('content')
<form class="content-card p-3" method="post" action="{{ $isEdit ? route('admin.document-types.update', $documentType) : route('admin.document-types.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif
    <div class="row g-3">
        <div class="col-lg-6">
            <label class="form-label" for="name">Nama Jenis Dokumen</label>
            <input class="form-control" id="name" name="name" value="{{ old('name', $documentType->name) }}" placeholder="Contoh: Peraturan Kapolri" required>
            <div class="form-text">Gunakan nama kelompok dokumen yang familiar bagi pengelola dan pengguna portal. Slug URL dibuat otomatis dari nama.</div>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="code_prefix">Prefix Kode</label>
            <input class="form-control text-uppercase" id="code_prefix" name="code_prefix" value="{{ old('code_prefix', $documentType->code_prefix) }}" placeholder="Contoh: PERKAP / SE / JUKNIS" required>
            <div class="form-text">Huruf besar, angka, dan tanda hubung. Prefix dipakai sebagai kode singkat pada metadata.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="collection">Kelompok Koleksi</label>
            <select class="form-select" id="collection" name="collection" required>
                @foreach($collections as $value => $label)
                    <option value="{{ $value }}" @selected(old('collection', $documentType->collection ?: 'produk_hukum') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="form-text">Pilih Produk Hukum untuk regulasi resmi, Perpustakaan untuk buku/jurnal/kajian/referensi.</div>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="review_interval_months">Review Berkala</label>
            <div class="input-group">
                <input class="form-control" id="review_interval_months" type="number" name="review_interval_months" min="0" max="60" value="{{ old('review_interval_months', $documentType->review_interval_months ?? 6) }}" required>
                <span class="input-group-text">bulan</span>
            </div>
            <div class="form-text">Contoh: 6 bulan untuk dokumen yang perlu ditinjau berkala. Isi 0 jika tidak periodik.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="5" placeholder="Contoh: Dokumen yang memuat peraturan resmi Kapolri dan menjadi rujukan pelaksanaan tugas kepolisian.">{{ old('description', $documentType->description) }}</textarea>
            <div class="form-text">Deskripsi tampil sebagai konteks pengelompokan dan membantu admin memilih jenis dokumen yang tepat.</div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
        </div>
    </div>
</form>
@endsection
