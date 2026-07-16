@extends('layouts.app')

@section('title', ($allCollections ? 'Hasil Pencarian' : 'Bank Produk Hukum').' - PUSAKA HUKUM')

@section('content')
@php
    $activeFilters = collect([
        'type' => ['label' => 'Jenis', 'value' => request('type') ? $types->firstWhere('id', (int) request('type'))?->name : null],
        'category' => ['label' => 'Kategori', 'value' => request('category') ? $categories->firstWhere('id', (int) request('category'))?->name : null],
        'year' => ['label' => 'Tahun', 'value' => request('year')],
        'status' => ['label' => 'Status', 'value' => request('status') ? ($statusFacets->firstWhere('value', request('status'))['label'] ?? null) : null],
    ])->filter(fn ($filter) => filled($filter['value']));

    $sortLabels = [
        'terbaru' => 'Terbaru',
        'terlama' => 'Terlama',
        'populer' => 'Terpopuler',
        'judul' => 'Judul A-Z',
        'relevansi' => 'Relevansi',
    ];
@endphp
<section class="py-5">
    <div class="container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">{{ $allCollections ? 'Hasil Pencarian' : 'Bank Produk Hukum' }}</span>
        </nav>

        <div class="mb-4">
            <h1 class="h3">{{ $allCollections ? 'Hasil Pencarian PUSAKA HUKUM' : 'Bank Produk Hukum' }}</h1>
            <p class="text-muted mb-0">{{ $allCollections ? 'Hasil pencarian dari bank produk hukum, perpustakaan digital, dan materi edukasi.' : 'Cari produk hukum berdasarkan judul, jenis, kategori, status, tahun, dan kata kunci.' }}</p>
        </div>

        <form method="get" class="search-toolbar mb-3">
            @if($allCollections)<input type="hidden" name="collection" value="all">@endif
            @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
            @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            @if(request('year'))<input type="hidden" name="year" value="{{ request('year') }}">@endif
            @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif

            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-white"><i data-lucide="search"></i></span>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari judul, jenis, kategori, status, tahun, atau kata kunci">
            </div>

            <select class="form-select" style="max-width: 190px;" name="sort" onchange="this.form.submit()" aria-label="Urutkan hasil">
                @foreach($sortLabels as $value => $label)
                    @if($value !== 'relevansi' || request('q'))
                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                    @endif
                @endforeach
            </select>

            <button class="btn btn-pusaka" type="submit"><i data-lucide="search"></i> Cari</button>
            <button class="btn btn-outline-secondary lg:hidden" type="button" data-ui-toggle="collapse" data-ui-target="#facetPanel" aria-expanded="false" aria-controls="facetPanel">
                <i data-lucide="filter"></i> Filter
            </button>
        </form>

        @if($activeFilters->isNotEmpty())
            <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                @foreach($activeFilters as $key => $filter)
                    <a class="filter-chip" href="{{ route('documents.index', request()->except([$key, 'page'])) }}">
                        {{ $filter['label'] }}: {{ $filter['value'] }} <i data-lucide="x"></i>
                    </a>
                @endforeach
                <a class="filter-chip-clear" href="{{ route('documents.index', $allCollections ? ['collection' => 'all'] : []) }}">
                    <i data-lucide="x"></i> Hapus semua filter
                </a>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-3">
                <div class="facet-collapse lg:sticky" style="top: 90px;" id="facetPanel">
                    <div class="facet-panel">
                        <div class="facet-group">
                            <div class="facet-group-title">Jenis Dokumen</div>
                            <div class="facet-list">
                                @foreach($types as $type)
                                    @php $isActive = (string) request('type') === (string) $type->id; @endphp
                                    <a class="facet-link {{ $isActive ? 'active' : '' }}"
                                       href="{{ $isActive ? route('documents.index', request()->except(['type', 'page'])) : route('documents.index', array_merge(request()->except('page'), ['type' => $type->id])) }}">
                                        <span>{{ $type->name }}</span>
                                        <span class="facet-link-count">{{ $type->facet_count }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="facet-group">
                            <div class="facet-group-title">Kategori Hukum</div>
                            <div class="facet-list">
                                @foreach($categories as $category)
                                    @php $isActive = (string) request('category') === (string) $category->id; @endphp
                                    <a class="facet-link {{ $isActive ? 'active' : '' }}"
                                       href="{{ $isActive ? route('documents.index', request()->except(['category', 'page'])) : route('documents.index', array_merge(request()->except('page'), ['category' => $category->id])) }}">
                                        <span>{{ $category->name }}</span>
                                        <span class="facet-link-count">{{ $category->facet_count }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="facet-group">
                            <div class="facet-group-title">Status Dokumen</div>
                            <div class="facet-list">
                                @foreach($statusFacets as $statusFacet)
                                    @php $isActive = request('status') === $statusFacet['value']; @endphp
                                    <a class="facet-link {{ $isActive ? 'active' : '' }}"
                                       href="{{ $isActive ? route('documents.index', request()->except(['status', 'page'])) : route('documents.index', array_merge(request()->except('page'), ['status' => $statusFacet['value']])) }}">
                                        <span>{{ $statusFacet['label'] }}</span>
                                        <span class="facet-link-count">{{ $statusFacet['count'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        @if($years->isNotEmpty())
                            <div class="facet-group">
                                <div class="facet-group-title">Tahun</div>
                                <select class="form-select" onchange="if(this.value){window.location.href=this.value}" aria-label="Filter tahun">
                                    <option value="{{ route('documents.index', request()->except(['year', 'page'])) }}">Semua Tahun</option>
                                    @foreach($years as $year)
                                        <option value="{{ route('documents.index', array_merge(request()->except('page'), ['year' => $year])) }}" @selected(request('year') == $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="result-count mb-3">
                    <strong>{{ $documents->total() }}</strong> dokumen ditemukan
                    @if(request('q'))untuk &ldquo;{{ request('q') }}&rdquo;@endif
                </div>

                <div class="row g-3">
                    @forelse($documents as $document)
                        <div class="col-12">
                            @include('public.documents._document-card', ['document' => $document])
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <span class="empty-state-icon"><i data-lucide="search-x"></i></span>
                                <h2 class="h5 mb-0">Tidak ada dokumen sesuai filter</h2>
                                <p class="text-muted mb-2">Coba ubah kata kunci atau hapus sebagian filter pencarian.</p>
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('documents.index', $allCollections ? ['collection' => 'all'] : []) }}">
                                    <i data-lucide="rotate-ccw"></i> Reset Filter
                                </a>
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
