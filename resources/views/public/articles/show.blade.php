@extends('layouts.app')

@section('title', $article->title.' - PUSAKA HUKUM')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <a class="btn btn-sm btn-outline-secondary mb-3" href="{{ route('articles.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
                <article class="item-card p-4 p-lg-5">
                    <div class="text-muted small mb-2">
                        {{ $article->category ?: 'Artikel Hukum' }}
                        @if($article->published_at)
                            | {{ $article->published_at->format('d/m/Y') }}
                        @endif
                    </div>
                    <h1 class="h2 mb-3">{{ $article->title }}</h1>
                    @if($article->excerpt)
                        <p class="lead text-muted">{{ $article->excerpt }}</p>
                    @endif
                    <hr>
                    <div style="white-space: pre-line; line-height: 1.8;">{{ $article->content }}</div>
                    <div class="small text-muted mt-4">Penulis: {{ $article->author?->name ?? 'Pengelola PUSAKA HUKUM' }}</div>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
