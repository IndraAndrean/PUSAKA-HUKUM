@extends('layouts.app')

@section('title', 'Knowledge Center - SIPAKEM')

@section('content')
<section class="py-5">
    <div class="container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">Knowledge Center</span>
        </nav>

        <div class="mb-4">
            <h1 class="h3">Knowledge Center</h1>
            <p class="text-muted mb-0">Artikel dan materi edukasi hukum untuk mendukung literasi hukum personel dan masyarakat.</p>
        </div>

        <form method="get" class="search-toolbar mb-4">
            <div class="input-group flex-grow-1">
                <span class="input-group-text"><i data-lucide="search"></i></span>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari judul, kategori, atau isi artikel">
            </div>
            <select class="form-select" style="max-width: 220px;" name="category" onchange="this.form.submit()" aria-label="Filter kategori">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <button class="btn btn-pusaka" type="submit"><i data-lucide="search"></i> Cari</button>
        </form>

        <div class="result-count mb-3"><strong>{{ $articles->total() }}</strong> artikel ditemukan</div>

        <div class="row g-3">
            @forelse($articles as $article)
                <div class="col-12">
                    @include('public.articles._article-card', ['article' => $article])
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-state">
                        <span class="empty-state-icon"><i data-lucide="search-x"></i></span>
                        <h2 class="h5 mb-0">Belum ada artikel yang sesuai</h2>
                        <p class="text-muted mb-2">Coba ubah kata kunci atau kategori pencarian.</p>
                        @if(request()->hasAny(['q', 'category']))
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('articles.index') }}"><i data-lucide="rotate-ccw"></i> Reset Filter</a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $articles->links('vendor.pagination.pusaka') }}</div>
    </div>
</section>
@endsection
