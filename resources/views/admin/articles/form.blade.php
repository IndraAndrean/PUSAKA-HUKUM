@extends('layouts.admin')

@php($isEdit = $article->exists)

@section('title', $isEdit ? 'Edit Artikel' : 'Tambah Artikel')
@section('page_title', $isEdit ? 'Edit Artikel' : 'Tambah Artikel')

@section('page_actions')
    <a class="btn btn-outline-secondary" href="{{ route('admin.articles.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection

@section('content')
<form class="content-card p-3" method="post" action="{{ $isEdit ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif
    <div class="row g-3">
        <div class="col-lg-8">
            <label class="form-label" for="title">Judul Artikel</label>
            <input class="form-control" id="title" name="title" value="{{ old('title', $article->title) }}" required>
        </div>
        <div class="col-lg-4">
            <label class="form-label" for="category">Kategori</label>
            <input class="form-control" id="category" name="category" value="{{ old('category', $article->category) }}" placeholder="Contoh: Edukasi Hukum">
        </div>
        <div class="col-12">
            <label class="form-label" for="excerpt">Ringkasan Singkat</label>
            <textarea class="form-control" id="excerpt" name="excerpt" rows="3" maxlength="500">{{ old('excerpt', $article->excerpt) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="content">Isi Artikel</label>
            <textarea class="form-control" id="content" name="content" rows="14" required>{{ old('content', $article->content) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="status">Status Publikasi</label>
            <select class="form-select" id="status" name="status" required>
                <option value="draft" @selected(old('status', $article->status ?: 'draft') === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $article->status) === 'published')>Terbitkan</option>
            </select>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.articles.index') }}">Batal</a>
        </div>
    </div>
</form>
@endsection
