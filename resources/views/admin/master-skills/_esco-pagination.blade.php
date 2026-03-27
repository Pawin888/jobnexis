@php
    $paginator = $paginator ?? null;
    $perPageName = $perPageName ?? 'page';
@endphp
@if($paginator && $paginator->total() > 0)
    <div class="mb-3 bg-white rounded-xl shadow p-3">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-700">แสดง:</span>
                <span class="font-semibold text-gray-800">{{ $paginator->firstItem() ?? 0 }}</span>
                -
                <span class="font-semibold text-gray-800">{{ $paginator->lastItem() ?? 0 }}</span>
                จากทั้งหมด
                <span class="font-semibold text-gray-800">{{ number_format($paginator->total()) }}</span>
                รายการ
            </div>
            <div class="flex items-center gap-2">
                {{-- Previous Button --}}
                @if($paginator->onFirstPage())
                    <button disabled class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm">ก่อนหน้า</button>
                @else
                    <a href="{{ $paginator->appends(request()->except($perPageName))->previousPageUrl() }}" class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">ก่อนหน้า</a>
                @endif
                {{-- Page Numbers --}}
                <div class="flex items-center gap-1">
                    @php
                        $currentPage = $paginator->currentPage();
                        $lastPage    = $paginator->lastPage();
                        $start       = max(1, $currentPage - 2);
                        $end         = min($lastPage, $currentPage + 2);
                    @endphp
                    @if($start > 1)
                        <a href="{{ $paginator->appends(request()->except($perPageName))->url(1) }}" class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">1</a>
                        @if($start > 2)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                    @endif
                    @for($i = $start; $i <= $end; $i++)
                        @if($i == $currentPage)
                            <span class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-lg text-sm font-medium">{{ $i }}</span>
                        @else
                            <a href="{{ $paginator->appends(request()->except($perPageName))->url($i) }}" class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">{{ $i }}</a>
                        @endif
                    @endfor
                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                        <a href="{{ $paginator->appends(request()->except($perPageName))->url($lastPage) }}" class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">{{ $lastPage }}</a>
                    @endif
                </div>
                {{-- Next Button --}}
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->appends(request()->except($perPageName))->nextPageUrl() }}" class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">ถัดไป</a>
                @else
                    <button disabled class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm">ถัดไป</button>
                @endif
            </div>
        </div>
    </div>
@endif
