@extends('layouts.app')

@section('title', 'Knowledge Center - PUSAKA HUKUM')

@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="h3">Knowledge Center</h1>
        <p class="text-muted">Artikel dan materi edukasi hukum untuk mendukung literasi hukum.</p>
        <div class="row g-3">
            @forelse($articles as $article)
                <div class="col-md-6 col-xl-4">
                    <article class="item-card p-3 h-100">
                        <div class="small text-muted mb-2">{{ $article->category }}</div>
                        <h2 class="h5"><a class="text-dark text-decoration-none" href="{{ route('articles.show', $article->slug) }}">{{ $article->title }}</a></h2>
                        <p class="mb-0">{{ $article->excerpt }}</p>
                        <a class="btn btn-sm btn-outline-secondary mt-3" href="{{ route('articles.show', $article->slug) }}">Baca artikel</a>
                    </article>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">Belum ada artikel.</div></div>
            @endforelse
        </div>
        <div class="mt-4">{{ $articles->links() }}</div>
    </div>
</section>
@endsection
