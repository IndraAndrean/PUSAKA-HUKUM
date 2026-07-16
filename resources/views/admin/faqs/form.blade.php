@extends('layouts.admin')

@php($isEdit = $faq->exists)

@section('title', $isEdit ? 'Edit FAQ' : 'Tambah FAQ')
@section('page_title', $isEdit ? 'Edit FAQ' : 'Tambah FAQ')

@section('content')
<form class="content-card p-3" method="post" action="{{ $isEdit ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif
    <div class="row g-3">
        <div class="col-lg-8">
            <label class="form-label" for="question">Pertanyaan</label>
            <input class="form-control" id="question" name="question" value="{{ old('question', $faq->question) }}" placeholder="Contoh: Bagaimana cara mengunduh dokumen internal?" required>
            <div class="form-text">Tulis pertanyaan seperti bahasa yang biasa dipakai pengguna portal.</div>
        </div>
        <div class="col-lg-4">
            <label class="form-label" for="category">Kategori</label>
            <input class="form-control" id="category" name="category" value="{{ old('category', $faq->category) }}" placeholder="Contoh: Akses Dokumen / Konsultasi / Akun">
            <div class="form-text">Kategori memudahkan FAQ dikelompokkan pada halaman publik.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="answer">Jawaban</label>
            <textarea class="form-control" id="answer" name="answer" rows="8" placeholder="Contoh: Masuk menggunakan akun internal, buka detail dokumen, lalu pilih tombol Unduh jika dokumen tersedia." required>{{ old('answer', $faq->answer) }}</textarea>
            <div class="form-text">Jawaban sebaiknya singkat, operasional, dan tidak memuat informasi rahasia.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="draft" @selected(old('status', $faq->status) === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $faq->status ?: 'published') === 'published')>Terbit</option>
            </select>
            <div class="form-text">Draft tidak tampil di portal; Terbit tampil untuk pengguna.</div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
        </div>
    </div>
</form>
@endsection
