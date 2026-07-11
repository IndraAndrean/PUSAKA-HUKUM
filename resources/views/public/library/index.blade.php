@extends('layouts.app')

@section('title', 'Perpustakaan Digital Hukum - PUSAKA HUKUM')

@section('content')
<section class="page-intro py-5">
    <div class="container">
        <nav class="breadcrumb mb-3">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">Perpustakaan Digital</span>
        </nav>
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
        <form method="get" class="search-toolbar mb-4">
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-white"><i data-lucide="search"></i></span>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Judul, penulis, ISBN, kata kunci">
            </div>
            @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
            @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            @if(request('year'))<input type="hidden" name="year" value="{{ request('year') }}">@endif
            <button class="btn btn-pusaka" type="submit"><i data-lucide="search"></i> Cari</button>
            <button class="btn btn-outline-secondary lg:hidden" type="button" data-ui-toggle="collapse" data-ui-target="#libraryFacetPanel" aria-expanded="false" aria-controls="libraryFacetPanel">
                <i data-lucide="filter"></i> Filter
            </button>
        </form>

        <div class="row g-4">
            <div class="col-lg-3">
                <div class="facet-collapse lg:sticky" style="top: 90px;" id="libraryFacetPanel">
                    <div class="facet-panel">
                        <div class="facet-group">
                            <div class="facet-group-title">Jenis Referensi</div>
                            <div class="facet-list">
                                @foreach($types as $type)
                                    @php $isActive = (string) request('type') === (string) $type->id; @endphp
                                    <a class="facet-link {{ $isActive ? 'active' : '' }}"
                                       href="{{ $isActive ? route('library.index', request()->except(['type', 'page'])) : route('library.index', array_merge(request()->except('page'), ['type' => $type->id])) }}">
                                        <span>{{ $type->name }}</span>
                                        <span class="facet-link-count">{{ $type->documents_count }}</span>
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
                                       href="{{ $isActive ? route('library.index', request()->except(['category', 'page'])) : route('library.index', array_merge(request()->except('page'), ['category' => $category->id])) }}">
                                        <span>{{ $category->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        @if($years->isNotEmpty())
                            <div class="facet-group">
                                <div class="facet-group-title">Tahun Terbit</div>
                                <select class="form-select" onchange="if(this.value){window.location.href=this.value}" aria-label="Filter tahun">
                                    <option value="{{ route('library.index', request()->except(['year', 'page'])) }}">Semua Tahun</option>
                                    @foreach($years as $year)
                                        <option value="{{ route('library.index', array_merge(request()->except('page'), ['year' => $year])) }}" @selected(request('year') == $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if(request()->hasAny(['q', 'type', 'category', 'year']))
                            <a class="btn btn-outline-secondary btn-sm w-100" href="{{ route('library.index') }}"><i data-lucide="rotate-ccw"></i> Bersihkan Filter</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="result-count mb-3"><strong>{{ $documents->total() }}</strong> koleksi ditemukan</div>

                <div class="row g-3">
                    @forelse($documents as $document)
                        <div class="col-12">
                            @include('public.documents._document-card', ['document' => $document])
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <span class="empty-state-icon"><i data-lucide="search-x"></i></span>
                                <h2 class="h5 mb-0">Belum ada koleksi yang sesuai</h2>
                                <p class="text-muted mb-2">Coba ubah kata kunci atau hapus sebagian filter pencarian.</p>
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.index') }}"><i data-lucide="rotate-ccw"></i> Reset Filter</a>
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
