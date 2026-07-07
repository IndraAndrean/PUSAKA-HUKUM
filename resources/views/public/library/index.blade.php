@extends('layouts.app')

@section('title', 'Perpustakaan Digital Hukum - PUSAKA HUKUM')

@section('content')
<section class="page-intro py-5">
    <div class="container">
        <div class="row align-items-end g-4">
            <div class="col-lg-8">
                <div class="section-eyebrow mb-2">Referensi dan Kajian</div>
                <h1 class="h2">Perpustakaan Digital Hukum</h1>
                <p class="text-muted mb-0">Temukan buku, jurnal, naskah akademik, kajian, yurisprudensi, putusan, dan referensi hukum lainnya sesuai hak akses Anda.</p>
            </div>
            <div class="col-lg-4">
                <div class="row g-2">
                    <div class="col-6"><div class="stat-tile p-3"><div class="small text-muted">Total Koleksi</div><div class="h3 mb-0">{{ $totalLibrary }}</div></div></div>
                    <div class="col-6"><div class="stat-tile p-3"><div class="small text-muted">Penulis</div><div class="h3 mb-0">{{ $totalAuthors }}</div></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <aside class="col-lg-3">
                <form method="get" class="content-card p-3">
                    <div class="d-flex align-items-center gap-2 fw-semibold mb-3"><i class="bi bi-funnel"></i><span>Filter Koleksi</span></div>
                    <div class="mb-3">
                        <label class="form-label small" for="library_q">Pencarian</label>
                        <input class="form-control" id="library_q" name="q" value="{{ request('q') }}" placeholder="Judul, penulis, ISBN, kata kunci">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="library_type">Jenis referensi</label>
                        <select class="form-select" id="library_type" name="type">
                            <option value="">Semua jenis</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->name }} ({{ $type->documents_count }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="library_category">Kategori hukum</label>
                        <select class="form-select" id="library_category" name="category">
                            <option value="">Semua kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="library_year">Tahun terbit</label>
                        <select class="form-select" id="library_year" name="year">
                            <option value="">Semua tahun</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-pusaka" type="submit"><i class="bi bi-search me-1"></i> Cari</button>
                        @if(request()->hasAny(['q', 'type', 'category', 'year']))
                            <a class="btn btn-outline-secondary" href="{{ route('library.index') }}">Bersihkan</a>
                        @endif
                    </div>
                </form>
            </aside>

            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Daftar Koleksi</h2>
                    <span class="small text-muted">{{ $documents->total() }} hasil</span>
                </div>
                <div class="row g-3">
                    @forelse($documents as $document)
                        <div class="col-md-6">
                            <article class="item-card p-3 h-100 d-flex flex-column">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge text-bg-secondary">{{ $document->type?->name }}</span>
                                    <span class="badge badge-access">{{ ucfirst($document->access_level) }}</span>
                                </div>
                                <h3 class="h5"><a class="text-dark text-decoration-none" href="{{ route('documents.show', $document) }}">{{ $document->title }}</a></h3>
                                <dl class="row small mb-2">
                                    <dt class="col-4 text-muted fw-normal">Penulis</dt><dd class="col-8 mb-1">{{ $document->author ?: '-' }}</dd>
                                    <dt class="col-4 text-muted fw-normal">Penerbit</dt><dd class="col-8 mb-1">{{ $document->publisher ?: $document->issuing_institution }}</dd>
                                    <dt class="col-4 text-muted fw-normal">Tahun</dt><dd class="col-8 mb-1">{{ $document->year ?: '-' }}</dd>
                                </dl>
                                <p class="small text-muted flex-grow-1">{{ str($document->summary)->limit(150) }}</p>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('documents.show', $document) }}"><i class="bi bi-eye me-1"></i> Detail</a>
                                    <a class="btn btn-sm btn-pusaka" href="{{ route('documents.download', $document) }}"><i class="bi bi-download me-1"></i> Unduh</a>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-info mb-0">Belum ada koleksi perpustakaan yang sesuai dengan pencarian.</div></div>
                    @endforelse
                </div>
                <div class="mt-4">{{ $documents->links() }}</div>
            </div>
        </div>
    </div>
</section>
@endsection
