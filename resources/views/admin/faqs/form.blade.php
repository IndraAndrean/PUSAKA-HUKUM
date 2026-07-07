@extends('layouts.admin')

@php($isEdit = $faq->exists)

@section('title', $isEdit ? 'Edit FAQ' : 'Tambah FAQ')
@section('page_title', $isEdit ? 'Edit FAQ' : 'Tambah FAQ')

@section('page_actions')
    <a class="btn btn-outline-secondary" href="{{ route('admin.faqs.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection

@section('content')
<form class="content-card p-3" method="post" action="{{ $isEdit ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif
    <div class="row g-3">
        <div class="col-lg-8">
            <label class="form-label" for="question">Pertanyaan</label>
            <input class="form-control" id="question" name="question" value="{{ old('question', $faq->question) }}" required>
        </div>
        <div class="col-lg-4">
            <label class="form-label" for="category">Kategori</label>
            <input class="form-control" id="category" name="category" value="{{ old('category', $faq->category) }}" placeholder="Contoh: Umum">
        </div>
        <div class="col-12">
            <label class="form-label" for="answer">Jawaban</label>
            <textarea class="form-control" id="answer" name="answer" rows="8" required>{{ old('answer', $faq->answer) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="draft" @selected(old('status', $faq->status) === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $faq->status ?: 'published') === 'published')>Terbit</option>
            </select>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.faqs.index') }}">Batal</a>
        </div>
    </div>
</form>
@endsection
