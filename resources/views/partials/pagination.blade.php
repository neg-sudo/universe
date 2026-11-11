@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pagination__link is-disabled" aria-disabled="true">
                <x-icon name="chevron-left" :size="16" /> Previous
            </span>
        @else
            <a class="pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                <x-icon name="chevron-left" :size="16" /> Previous
            </a>
        @endif

        <div class="pagination__pages">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination__ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination__page is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination__page" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a class="pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                Next <x-icon name="chevron-right" :size="16" />
            </a>
        @else
            <span class="pagination__link is-disabled" aria-disabled="true">
                Next <x-icon name="chevron-right" :size="16" />
            </span>
        @endif
    </nav>
@endif
