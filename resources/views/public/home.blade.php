@extends('layouts.app')

@section('title', ($organizationProfile?->portal_name ?? 'PUSAKA HUKUM').' - '.$organizationProfile?->organization_name)

@section('content')
<section class="hero">
    <div class="container py-5">
        <div class="row align-items-center g-4">
            <div class="col-xl-9">
                <p class="hero-eyebrow mb-3">{{ $organizationProfile?->eyebrow ?? $organizationProfile?->portal_full_name }}</p>
                <h1 class="hero-title mb-3">{{ $organizationProfile?->portal_name ?? 'PUSAKA HUKUM' }}</h1>
                <p class="lead mb-2">{{ $organizationProfile?->tagline ?? 'Satu Akses untuk Semua Pengetahuan Hukum' }}</p>
                <p class="hero-copy mb-4">{{ $organizationProfile?->hero_description }}</p>
                <form action="{{ route('documents.index') }}" method="get" class="hero-search p-2">
                    <input type="hidden" name="collection" value="all">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                        <input class="form-control border-0" name="q" aria-label="Cari dokumen hukum" placeholder="Cari judul, nomor, penulis, atau kata kunci">
                        <button class="btn btn-warning px-4" type="submit"><i class="bi bi-search me-1"></i> Cari</button>
                    </div>
                </form>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-light" href="{{ route('documents.index') }}"><i class="bi bi-file-earmark-text me-1"></i> Produk Hukum</a>
                    <a class="btn btn-sm btn-outline-light" href="{{ route('library.index') }}"><i class="bi bi-book me-1"></i> Perpustakaan Digital</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4 section-band">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-lg-3"><div class="stat-tile p-3 h-100"><div class="d-flex align-items-center gap-3"><span class="metric-icon"><i class="bi bi-files"></i></span><div><div class="text-muted small">Total Dokumen</div><div class="h3 mb-0">{{ $totalDocuments }}</div></div></div></div></div>
            <div class="col-6 col-lg-3"><div class="stat-tile p-3 h-100"><div class="d-flex align-items-center gap-3"><span class="metric-icon"><i class="bi bi-unlock"></i></span><div><div class="text-muted small">Dokumen Publik</div><div class="h3 mb-0">{{ $totalPublicDocuments }}</div></div></div></div></div>
            <div class="col-6 col-lg-3"><div class="stat-tile p-3 h-100"><div class="d-flex align-items-center gap-3"><span class="metric-icon"><i class="bi bi-book"></i></span><div><div class="text-muted small">Perpustakaan</div><div class="h3 mb-0">{{ $totalLibrary }}</div></div></div></div></div>
            <div class="col-6 col-lg-3"><div class="stat-tile p-3 h-100"><div class="d-flex align-items-center gap-3"><span class="metric-icon"><i class="bi bi-question-circle"></i></span><div><div class="text-muted small">FAQ Hukum</div><div class="h3 mb-0">{{ $totalFaqs }}</div></div></div></div></div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="section-eyebrow mb-1">Referensi Hukum</div>
                <h2 class="h4 mb-0">Perpustakaan Digital</h2>
            </div>
            <a href="{{ route('library.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-right"></i> Buka Perpustakaan</a>
        </div>
        <div class="row g-3">
            @foreach($libraryTypes as $type)
                <div class="col-6 col-lg-3">
                    <a class="item-card p-3 d-block text-decoration-none text-dark h-100" href="{{ route('library.index', ['type' => $type->id]) }}">
                        <i class="bi bi-book text-success fs-4"></i>
                        <div class="fw-semibold mt-2">{{ $type->name }}</div>
                        <div class="small text-muted">{{ $type->documents_count }} koleksi</div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-5 section-band">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="section-eyebrow mb-2">{{ $organizationProfile?->organization_name }}</div>
                <h2 class="h3">Pusat pengetahuan hukum digital yang terintegrasi</h2>
                <p class="text-muted mb-0">{{ $organizationProfile?->about }}</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a class="btn btn-pusaka" href="{{ route('organization-profile.show') }}">
                    <i class="bi bi-building"></i> Profil dan Layanan
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">Dokumen Terbaru</h2>
            <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-right"></i> Semua</a>
        </div>
        <div class="row g-3">
            @forelse($latestDocuments as $document)
                <div class="col-md-6 col-xl-4">
                    <div class="item-card p-3 h-100">
                        <span class="badge badge-access mb-2">{{ ucfirst($document->access_level) }}</span>
                        <h3 class="h6"><a class="text-decoration-none text-dark" href="{{ route('documents.show', $document) }}">{{ $document->title }}</a></h3>
                        <div class="small text-muted">{{ $document->type?->name }} @if($document->year) - {{ $document->year }} @endif</div>
                        <p class="small mt-2 mb-0">{{ str($document->summary)->limit(110) }}</p>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">Belum ada dokumen yang tersedia.</div></div>
            @endforelse
        </div>
    </div>
</section>

<section class="py-5 section-band">
    <div class="container">
        <h2 class="h4 mb-3">Kategori Akses Cepat</h2>
        <div class="row g-3">
            @foreach($documentTypes as $type)
                <div class="col-6 col-lg-3">
                    <a class="item-card p-3 d-block text-decoration-none text-dark h-100" href="{{ route('documents.index', ['type' => $type->id]) }}">
                        <div class="fw-semibold">{{ $type->name }}</div>
                        <div class="small text-muted">{{ $type->documents_count }} dokumen</div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">Pusat Pengetahuan</h2>
            <a href="{{ route('articles.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-right"></i> Artikel</a>
        </div>
        <div class="row g-3">
            @forelse($latestArticles as $article)
                <div class="col-md-4">
                    <div class="item-card p-3 h-100">
                        <div class="small text-muted mb-2">{{ $article->category }}</div>
                        <h3 class="h6">{{ $article->title }}</h3>
                        <p class="small mb-0">{{ $article->excerpt }}</p>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">Belum ada artikel.</div></div>
            @endforelse
        </div>
    </div>
</section>
@endsection
