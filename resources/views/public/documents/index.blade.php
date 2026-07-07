@extends('layouts.app')

@section('title', 'Dokumen Hukum - PUSAKA HUKUM')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="mb-4">
            <h1 class="h3">{{ $allCollections ? 'Hasil Pencarian PUSAKA HUKUM' : 'Bank Produk Hukum' }}</h1>
            <p class="text-muted mb-0">{{ $allCollections ? 'Hasil pencarian dari bank produk hukum, perpustakaan digital, dan materi edukasi.' : 'Cari produk hukum berdasarkan judul, jenis, kategori, status, tahun, dan kata kunci.' }}</p>
        </div>

        <form method="get" class="item-card p-3 mb-4">
            @if($allCollections)<input type="hidden" name="collection" value="all">@endif
            <div class="row g-2">
                <div class="col-lg-4">
                    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Kata kunci pencarian">
                </div>
                <div class="col-md-6 col-lg-2">
                    <select class="form-select" name="type">
                        <option value="">Jenis</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <select class="form-select" name="category">
                        <option value="">Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <select class="form-select" name="year">
                        <option value="">Tahun</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2 d-grid">
                    <button class="btn btn-pusaka" type="submit"><i class="bi bi-search"></i> Cari</button>
                </div>
            </div>
        </form>

        <div class="row g-3">
            @forelse($documents as $document)
                <div class="col-12">
                    <div class="item-card p-3">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge text-bg-secondary">{{ $document->type?->name }}</span>
                                    <span class="badge badge-access">{{ ucfirst($document->access_level) }}</span>
                                    <span class="badge text-bg-light">{{ str_replace('_', ' ', ucfirst($document->document_status)) }}</span>
                                </div>
                                <h2 class="h5 mb-1"><a class="text-dark text-decoration-none" href="{{ route('documents.show', $document) }}">{{ $document->title }}</a></h2>
                                <div class="small text-muted">
                                    Nomor: {{ $document->document_number ?? '-' }} | Tahun: {{ $document->year ?? '-' }} | Kategori: {{ $document->category?->name ?? '-' }}
                                </div>
                                <p class="mb-0 mt-2">{{ str($document->summary)->limit(180) }}</p>
                            </div>
                            <div class="d-flex flex-lg-column gap-2 justify-content-lg-center">
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('documents.show', $document) }}"><i class="bi bi-eye"></i> Detail</a>
                                <a class="btn btn-pusaka btn-sm" href="{{ route('documents.download', $document) }}"><i class="bi bi-download"></i> Unduh</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">Tidak ada dokumen sesuai filter.</div></div>
            @endforelse
        </div>

        <div class="mt-4">{{ $documents->links() }}</div>
    </div>
</section>
@endsection
