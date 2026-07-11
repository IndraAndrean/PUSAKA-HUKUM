<div class="doc-card border-l-4 border-l-pusaka-gold p-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div class="min-w-0">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="doc-card-icon"><i data-lucide="newspaper"></i></span>
                @if($article->category)
                    <span class="badge text-bg-secondary">{{ $article->category }}</span>
                @endif
                @if($article->published_at)
                    <span class="doc-card-meta small text-muted d-inline-flex align-items-center gap-1"><i data-lucide="calendar"></i> {{ $article->published_at->format('d/m/Y') }}</span>
                @endif
            </div>
            <h3 class="h5 mb-1">
                <a class="text-dark text-decoration-none" href="{{ route('articles.show', $article->slug) }}">{{ $article->title }}</a>
            </h3>
            @if($article->excerpt)
                <p class="mb-0 mt-2 text-truncate-3">{{ $article->excerpt }}</p>
            @endif
        </div>
        <div class="d-flex flex-lg-column gap-2 justify-content-lg-center">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('articles.show', $article->slug) }}"><i data-lucide="eye"></i> Baca Artikel</a>
        </div>
    </div>
</div>
