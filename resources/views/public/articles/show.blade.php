@extends('layouts.app')

@section('title', $article->title.' - SIPAKEM')

@section('content')
<section class="py-5">
    <div class="container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item"><a href="{{ route('articles.index') }}">Knowledge Center</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">{{ $article->title }}</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <article class="item-card p-4 p-lg-5">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        @if($article->category)
                            <span class="badge text-bg-secondary">{{ $article->category }}</span>
                        @endif
                        @if($article->published_at)
                            <span class="small text-muted d-inline-flex align-items-center gap-1"><i data-lucide="calendar"></i> {{ $article->published_at->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    <h1 class="h2 mb-3">{{ $article->title }}</h1>
                    @if($article->excerpt)
                        <p class="lead text-muted">{{ $article->excerpt }}</p>
                    @endif
                    <hr>
                    <div class="article-body">{{ $article->content }}</div>
                    <div class="small text-muted mt-4 d-flex align-items-center gap-1"><i data-lucide="user"></i> Penulis: {{ $article->author?->name ?? 'Pengelola SIPAKEM' }}</div>
                </article>

                @if($relatedArticles->isNotEmpty())
                    <div class="mt-5">
                        <h2 class="h5 mb-3">Artikel Terkait</h2>
                        <div class="related-docs">
                            @foreach($relatedArticles as $related)
                                @include('public.articles._article-card', ['article' => $related])
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
