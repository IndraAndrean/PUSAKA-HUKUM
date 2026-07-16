@extends('layouts.app')

@section('title', 'Materi Penyuluhan Hukum - PUSAKA HUKUM')

@section('content')
<section class="page-intro py-5">
    <div class="container">
        <nav class="breadcrumb mb-3">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">Materi Penyuluhan Hukum</span>
        </nav>
        <div class="row align-items-end g-4">
            <div class="col-lg-8">
                <div class="section-eyebrow mb-2">Knowledge Center</div>
                <h1 class="h2">Materi Penyuluhan Hukum</h1>
                <p class="text-muted mb-0">Bahan edukasi dan penyuluhan hukum untuk personel Polri dan masyarakat &mdash; pelengkap Artikel dan FAQ Hukum.</p>
            </div>
            <div class="col-lg-4">
                <div class="stat-tile p-3"><div class="small text-muted">Total Materi</div><div class="h3 mb-0">{{ $totalMaterials }}</div></div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <form method="get" class="search-toolbar mb-4">
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-white"><i data-lucide="search"></i></span>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Judul, kategori, jenis, atau kata kunci">
            </div>
            @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            <button class="btn btn-pusaka" type="submit"><i data-lucide="search"></i> Cari</button>
            <button class="btn btn-outline-secondary lg:hidden" type="button" data-ui-toggle="collapse" data-ui-target="#educationFacetPanel" aria-expanded="false" aria-controls="educationFacetPanel">
                <i data-lucide="filter"></i> Filter
            </button>
        </form>

        <div class="row g-4">
            <div class="col-lg-3">
                <div class="facet-collapse lg:sticky" style="top: 90px;" id="educationFacetPanel">
                    <div class="facet-panel">
                        <div class="facet-group">
                            <div class="facet-group-title">Kategori Hukum</div>
                            <div class="facet-list">
                                @forelse($categories as $category)
                                    @php $isActive = (string) request('category') === (string) $category->id; @endphp
                                    <a class="facet-link {{ $isActive ? 'active' : '' }}"
                                       href="{{ $isActive ? route('education-materials.index', request()->except(['category', 'page'])) : route('education-materials.index', array_merge(request()->except('page'), ['category' => $category->id])) }}">
                                        <span>{{ $category->name }}</span>
                                    </a>
                                @empty
                                    <div class="small text-muted">Belum ada kategori.</div>
                                @endforelse
                            </div>
                        </div>

                        @if(request()->hasAny(['q', 'category']))
                            <a class="btn btn-outline-secondary btn-sm w-100" href="{{ route('education-materials.index') }}"><i data-lucide="rotate-ccw"></i> Bersihkan Filter</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="result-count mb-3"><strong>{{ $documents->total() }}</strong> materi ditemukan</div>

                <div class="row g-3">
                    @forelse($documents as $document)
                        <div class="col-12">
                            @include('public.documents._document-card', ['document' => $document])
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <span class="empty-state-icon"><i data-lucide="search-x"></i></span>
                                <h2 class="h5 mb-0">Belum ada materi yang sesuai</h2>
                                <p class="text-muted mb-2">Coba ubah kata kunci atau hapus sebagian filter pencarian.</p>
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('education-materials.index') }}"><i data-lucide="rotate-ccw"></i> Reset Filter</a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">{{ $documents->links('vendor.pagination.pusaka') }}</div>
            </div>
        </div>
    </div>
</section>
@endsection
