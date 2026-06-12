@if ($paginator->hasPages())
<nav class="qong-pagination" role="navigation" aria-label="Pagination">
    <div class="qong-pagination__info">
        @if ($paginator->firstItem())
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        @else
            {{ $paginator->count() }} results
        @endif
    </div>

    <div class="qong-pagination__pages">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="qong-pagination__btn qong-pagination__btn--disabled" aria-disabled="true">&lsaquo; Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="qong-pagination__btn">&lsaquo; Prev</a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="qong-pagination__btn qong-pagination__btn--dots">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="qong-pagination__btn qong-pagination__btn--active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="qong-pagination__btn" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="qong-pagination__btn">Next &rsaquo;</a>
        @else
            <span class="qong-pagination__btn qong-pagination__btn--disabled" aria-disabled="true">Next &rsaquo;</span>
        @endif
    </div>
</nav>

<style>
.qong-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    flex-wrap: wrap;
    gap: 8px;
}
.qong-pagination__info {
    font-size: 13px;
    color: #9b7fd4;
}
.qong-pagination__pages {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}
.qong-pagination__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    padding: 0 10px;
    border-radius: 6px;
    border: 1px solid #3a1f6e;
    background: #1a0a3a;
    color: #c4a8f0;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
    white-space: nowrap;
    line-height: 1;
}
.qong-pagination__btn:hover {
    background: #2d1060;
    border-color: #7c3aed;
    color: #fff;
    text-decoration: none;
}
.qong-pagination__btn--active {
    background: #7c3aed;
    border-color: #7c3aed;
    color: #fff;
    cursor: default;
}
.qong-pagination__btn--disabled {
    opacity: 0.4;
    cursor: not-allowed;
    pointer-events: none;
}
.qong-pagination__btn--dots {
    background: transparent;
    border-color: transparent;
    cursor: default;
    color: #9b7fd4;
}
</style>
@endif
