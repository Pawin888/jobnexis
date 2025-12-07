{{-- resources/views/components/pagination-join.blade.php --}}
@if ($paginator->hasPages())
    <div class="join">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <button class="join-item btn btn-sm btn-disabled">&laquo;</button>
        @else
            <a class="join-item btn btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
        @endif

        {{-- Elements --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <button class="join-item btn btn-sm btn-disabled">{{ $element }}</button>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <button class="join-item btn btn-sm btn-active">{{ $page }}</button>
                    @else
                        <a class="join-item btn btn-sm" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a class="join-item btn btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
        @else
            <button class="join-item btn btn-sm btn-disabled">&raquo;</button>
        @endif
    </div>
@endif
