@if ($paginator->hasPages())
    <nav class="pusaka-pagination d-flex flex-wrap align-items-center justify-content-between gap-3" role="navigation" aria-label="Navigasi halaman">
        <p class="small text-muted mb-0">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} dokumen
        </p>
        <ul class="pusaka-pagination-list d-flex flex-wrap align-items-center gap-1 mb-0">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="pusaka-page-item disabled"><span class="pusaka-page-link"><i data-lucide="arrow-left"></i></span></li>
            @else
                <li class="pusaka-page-item"><a class="pusaka-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i data-lucide="arrow-left"></i></a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="pusaka-page-item disabled"><span class="pusaka-page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="pusaka-page-item active"><span class="pusaka-page-link">{{ $page }}</span></li>
                        @else
                            <li class="pusaka-page-item"><a class="pusaka-page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="pusaka-page-item"><a class="pusaka-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i data-lucide="arrow-right"></i></a></li>
            @else
                <li class="pusaka-page-item disabled"><span class="pusaka-page-link"><i data-lucide="arrow-right"></i></span></li>
            @endif
        </ul>
    </nav>
@endif
