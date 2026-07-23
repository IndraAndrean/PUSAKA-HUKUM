@php
    $collection = $document->type?->collection;
    $collectionIcon = match ($collection) {
        'perpustakaan' => 'book',
        'edukasi' => 'newspaper',
        default => 'file-text',
    };
    $collectionBorder = match ($collection) {
        'perpustakaan' => 'border-l-pusaka-navy',
        'edukasi' => 'border-l-pusaka-gold',
        default => 'border-l-pusaka-navy',
    };
@endphp
<div class="doc-card border-l-4 {{ $collectionBorder }} p-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div class="min-w-0">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="doc-card-icon"><i data-lucide="{{ $collectionIcon }}"></i></span>
                <span class="badge text-bg-secondary">{{ $document->type?->name }}</span>
                @include('layouts.partials.status-badge', ['document' => $document])
                @include('layouts.partials.access-badge', ['document' => $document])
            </div>
            <h3 class="h5 mb-1">
                <a class="text-dark text-decoration-none" href="{{ route('documents.show', $document) }}">{{ $document->title }}</a>
            </h3>
            <div class="doc-card-meta small text-muted d-flex flex-wrap gap-3">
                <span class="d-inline-flex align-items-center gap-1"><i data-lucide="hash"></i> {{ $document->document_number ?? '-' }}</span>
                <span class="d-inline-flex align-items-center gap-1"><i data-lucide="calendar"></i> {{ $document->year ?? '-' }}</span>
                <span class="d-inline-flex align-items-center gap-1"><i data-lucide="tags"></i> {{ $document->category?->name ?? '-' }}</span>
            </div>
            @if($document->summary)
                <p class="mb-0 mt-2 text-truncate-3">{{ str($document->summary)->limit(160) }}</p>
            @endif
        </div>
        <div class="d-flex flex-lg-column gap-2 justify-content-lg-center">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('documents.show', $document) }}"><i data-lucide="eye"></i> Detail</a>
            <a class="btn btn-pusaka btn-sm" href="{{ route('documents.download', $document) }}"><i data-lucide="download"></i> Unduh</a>
        </div>
    </div>
</div>
