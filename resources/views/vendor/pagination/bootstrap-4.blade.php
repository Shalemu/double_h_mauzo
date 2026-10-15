{{--
    Overrides Laravel's bootstrap-4 pagination app-wide: instead of every
    page number, show  ‹  1  …  current  …  last  ›
--}}
@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $pages = collect([1, $current, $paginator->lastPage()])->unique()->sort()->values();
    @endphp
    <nav aria-label="Pagination">
        <ul class="pagination mb-0">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">&lsaquo;</a></li>
            @endif

            @foreach ($pages as $i => $page)
                {{-- Gap between shown pages --}}
                @if ($i > 0 && $page - $pages[$i - 1] > 1)
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">&hellip;</span></li>
                @endif

                @if ($page === $current)
                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">&rsaquo;</a></li>
            @else
                <li class="page-item disabled" aria-disabled="true"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
