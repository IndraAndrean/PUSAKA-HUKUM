@if ($paginator->hasPages())
    <nav class="pusaka-pagination" role="navigation" aria-label="Navigasi halaman">
        <p class="pusaka-pagination-summary">
            Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>

        <ul class="pusaka-pagination-list">
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

            @if ($paginator->hasMorePages())
                <li class="pusaka-page-item"><a class="pusaka-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i data-lucide="arrow-right"></i></a></li>
            @else
                <li class="pusaka-page-item disabled"><span class="pusaka-page-link"><i data-lucide="arrow-right"></i></span></li>
            @endif
        </ul>
    </nav>
@endif
