@extends('layouts.admin')

@php($isEdit = $article->exists)

@section('title', $isEdit ? 'Edit Artikel' : 'Tambah Artikel')
@section('page_title', $isEdit ? 'Edit Artikel' : 'Tambah Artikel')

@section('content')
<form class="content-card p-3" method="post" action="{{ $isEdit ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif
    <div class="row g-3">
        <div class="col-lg-8">
            <label class="form-label" for="title">Judul Artikel</label>
            <input class="form-control" id="title" name="title" value="{{ old('title', $article->title) }}" placeholder="Contoh: Kedudukan Surat Elektronik dalam Pembuktian Perkara Pidana" required>
            <div class="form-text">Gunakan judul edukatif yang jelas dan sesuai topik hukum yang dibahas.</div>
        </div>
        <div class="col-lg-4">
            <label class="form-label" for="category">Kategori</label>
            <input class="form-control" id="category" name="category" value="{{ old('category', $article->category) }}" placeholder="Contoh: Edukasi Hukum / Literasi Digital">
            <div class="form-text">Kategori membantu pengunjung menelusuri Knowledge Center.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="excerpt">Ringkasan Singkat</label>
            <textarea class="form-control" id="excerpt" name="excerpt" rows="3" maxlength="500" placeholder="Contoh: Pembahasan mengenai kekuatan pembuktian surat elektronik berdasarkan peraturan perundang-undangan yang berlaku.">{{ old('excerpt', $article->excerpt) }}</textarea>
            <div class="form-text">Maksimal 500 karakter. Ringkasan ini tampil pada kartu artikel.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="content">Isi Artikel</label>
            <textarea class="form-control" id="content" name="content" rows="14" placeholder="Tulis latar belakang, pembahasan, rujukan hukum, dan kesimpulan secara ringkas serta mudah dipahami." required>{{ old('content', $article->content) }}</textarea>
            <div class="form-text">Gunakan bahasa edukatif, cantumkan rujukan hukum, dan hindari data perkara yang bersifat rahasia.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="status">Status Publikasi</label>
            <select class="form-select" id="status" name="status" required>
                <option value="draft" @selected(old('status', $article->status ?: 'draft') === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $article->status) === 'published')>Terbitkan</option>
            </select>
            <div class="form-text">Pilih Draft untuk menyimpan sementara, Terbitkan jika artikel siap tampil di portal.</div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
        </div>
    </div>
</form>
@endsection
