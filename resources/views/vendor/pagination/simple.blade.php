@if ($paginator->hasPages())
<div class="pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="disabled"><i class="fas fa-chevron-left"></i></span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
    @endif

    {{-- Page numbers --}}
    @php
        $current  = $paginator->currentPage();
        $last     = $paginator->lastPage();
        $window   = 2;
    @endphp

    @if($current > $window + 1)
        <a href="{{ $paginator->url(1) }}">1</a>
        @if($current > $window + 2)<span class="disabled">…</span>@endif
    @endif

    @for ($p = max(1, $current - $window); $p <= min($last, $current + $window); $p++)
        @if($p == $current)
            <span class="active">{{ $p }}</span>
        @else
            <a href="{{ $paginator->url($p) }}">{{ $p }}</a>
        @endif
    @endfor

    @if($current < $last - $window)
        @if($current < $last - $window - 1)<span class="disabled">…</span>@endif
        <a href="{{ $paginator->url($last) }}">{{ $last }}</a>
    @endif

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
    @else
        <span class="disabled"><i class="fas fa-chevron-right"></i></span>
    @endif
</div>
@endif
