@extends('layouts.app')

@section('title', 'คลังทักษะ ESCO')

@section('content')
<div class="mx-auto">

    {{-- Skills Card --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="font-semibold text-lg mb-4">
            ค้นหา
        </h2>

        {{-- Search Bar --}}
        <div class="mb-6">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-col sm:flex-row gap-3">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="ค้นหาด้วย ชื่อทักษะ / คีย์เวิร์ด"
                    class="w-full sm:w-96 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        ค้นหา
                    </button>
                    <a
                        href="{{ url()->current() }}"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                    >
                        ล้าง
                    </a>
                </div>
            </form>
        </div>

        {{-- Skills Table --}}
        @if($skills->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr class="border-b">
                            <th class="text-left py-2 px-3">ทักษะ</th>
                            <th class="text-left py-2 px-3">หมวดหมู่</th>
                            <th class="text-center py-2 px-3">ESCO URI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($skills as $skill)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3 font-medium text-gray-900">
                                    {{ $skill->name ?? '-' }}
                                </td>

                                <td class="py-2 px-3 text-gray-600">
                                    {{ $skill->skillGroups->first()?->name ?? '-' }}
                                </td>

                                <td class="py-2 px-3 text-center">
                                    @if($skill->esco_uri)
                                        <a
                                            href="{{ $skill->esco_uri }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-full transition-colors"
                                            title="{{ $skill->esco_uri }}"
                                        >
                                           <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none"
                                           viewBox="0 0 24 24" stroke="black">
                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                                        </svg>
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-sm">ยังไม่มี Skills</p>
        @endif
    </div>

    {{-- Pagination --}}
@if($skills->isNotEmpty())
    <div class="mt-6 bg-white rounded-xl shadow p-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

            {{-- Left: Items per page --}}
            <div class="flex items-center gap-2">
                <label for="perPage" class="text-sm text-gray-700 font-medium">แสดง:</label>
                <select
                    id="perPage"
                    name="perPage"
                    onchange="window.location.href = '{{ url()->current() }}?perPage=' + this.value + '&search={{ request('search') }}'"
                    class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                >
                    <option value="20" {{ request('perPage', 20) == 20 ? 'selected' : '' }}>20</option>
                    <option value="25" {{ request('perPage', 20) == 25 ? 'selected' : '' }}>25</option>
                    <option value="30" {{ request('perPage', 20) == 30 ? 'selected' : '' }}>30</option>
                    <option value="40" {{ request('perPage', 20) == 40 ? 'selected' : '' }}>40</option>
                </select>
                <span class="text-sm text-gray-700">รายการต่อหน้า</span>

                {{-- Info moved here --}}
                <span class="text-sm text-gray-600 ml-4">
                    (แสดง
                    <span class="font-semibold text-gray-800">{{ $skills->firstItem() ?? 0 }}</span>
                    -
                    <span class="font-semibold text-gray-800">{{ $skills->lastItem() ?? 0 }}</span>
                    จากทั้งหมด
                    <span class="font-semibold text-gray-800">{{ number_format($skills->total()) }}</span>
                    รายการ)
                </span>
            </div>

            {{-- Right: Pagination Controls --}}
            <div class="flex items-center gap-2">
                {{-- Previous Button --}}
                @if($skills->onFirstPage())
                    <button
                        disabled
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm"
                    >
                        ก่อนหน้า
                    </button>
                @else
                    <a
                        href="{{ $skills->appends(request()->except('page'))->previousPageUrl() }}"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm"
                    >
                        ก่อนหน้า
                    </a>
                @endif

                {{-- Page Numbers --}}
                <div class="flex items-center gap-1">
                    @php
                        $currentPage = $skills->currentPage();
                        $lastPage = $skills->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    {{-- First Page --}}
                    @if($start > 1)
                        <a
                            href="{{ $skills->appends(request()->except('page'))->url(1) }}"
                            class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm"
                        >
                            1
                        </a>
                        @if($start > 2)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                    @endif

                    {{-- Page Range --}}
                    @for($i = $start; $i <= $end; $i++)
                        @if($i == $currentPage)
                            <span class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-lg text-sm font-medium">
                                {{ $i }}
                            </span>
                        @else
                            <a
                                href="{{ $skills->appends(request()->except('page'))->url($i) }}"
                                class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm"
                            >
                                {{ $i }}
                            </a>
                        @endif
                    @endfor

                    {{-- Last Page --}}
                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                        <a
                            href="{{ $skills->appends(request()->except('page'))->url($lastPage) }}"
                            class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm"
                        >
                            {{ $lastPage }}
                        </a>
                    @endif
                </div>

                {{-- Next Button --}}
                @if($skills->hasMorePages())
                    <a
                        href="{{ $skills->appends(request()->except('page'))->nextPageUrl() }}"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm"
                    >
                        ถัดไป
                    </a>
                @else
                    <button
                        disabled
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm"
                    >
                        ถัดไป
                    </button>
                @endif
            </div>

        </div>
    </div>
    @endif

</div>
@endsection
