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
                    placeholder="ค้นหาด้วย ชื่อทักษะ / หมวดหมู่"
                    class="w-full sm:w-96 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
                {{-- Preserve sort + group state when searching --}}
                <input type="hidden" name="sort"  value="{{ request('sort', 'name') }}">
                <input type="hidden" name="dir"   value="{{ request('dir', 'asc') }}">
                <input type="hidden" name="group" value="{{ request('group') }}">
                <input type="hidden" name="skill" value="{{ request('skill') }}">
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

        {{-- Active filters badge --}}
        @if($activeGroup || $activeSkill)
            <div class="mb-4 flex items-center gap-2">
                <span class="text-sm text-gray-600">กำลังกรอง:</span>

                @if($activeGroup)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                        กลุ่ม: {{ $activeGroup->name }}
                        <a href="{{ request()->fullUrlWithQuery(['group' => null, 'page' => 1]) }}"
                           class="hover:text-blue-900 leading-none"
                           title="ล้างตัวกรองกลุ่ม">✕</a>
                    </span>
                @endif

                @if($activeSkill)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium">
                        ทักษะ: {{ $activeSkill->name }}
                        <a href="{{ request()->fullUrlWithQuery(['skill' => null, 'page' => 1]) }}"
                           class="hover:text-emerald-900 leading-none"
                           title="ล้างตัวกรองทักษะ">✕</a>
                    </span>
                @endif
            </div>
        @endif

        {{-- Skills Table --}}
        @if($skills->isNotEmpty())
            @php
                $sortBy  = request('sort', 'name');
                $sortDir = request('dir', 'asc');

                $nameDir  = ($sortBy === 'name'  && $sortDir === 'asc') ? 'desc' : 'asc';
                $groupDir = ($sortBy === 'group' && $sortDir === 'asc') ? 'desc' : 'asc';
                $levelDir = ($sortBy === 'level' && $sortDir === 'desc') ? 'asc' : 'desc';

                $nameUrl  = request()->fullUrlWithQuery(['sort' => 'name',  'dir' => $nameDir,  'page' => 1]);
                $groupUrl = request()->fullUrlWithQuery(['sort' => 'group', 'dir' => $groupDir, 'page' => 1]);
                $levelUrl = request()->fullUrlWithQuery(['sort' => 'level', 'dir' => $levelDir, 'page' => 1]);
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full table-fixed text-sm border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr class="border-b">

                            {{-- ทักษะ --}}
                            <th class="text-left py-2 px-3 w-1/2">
                                <a href="{{ $nameUrl }}"
                                   class="inline-flex items-center gap-1 hover:text-blue-600 transition-colors select-none">
                                    ทักษะ
                                    <span class="inline-block w-3 text-xs text-center {{ $sortBy === 'name' ? 'text-blue-600' : 'text-gray-400' }}">
                                        {{ $sortBy === 'name' ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}
                                    </span>
                                </a>
                            </th>

                            {{-- หมวดหมู่ --}}
                            <th class="text-left py-2 px-3 w-4/12">
                                <a href="{{ $groupUrl }}"
                                   class="inline-flex items-center gap-1 hover:text-blue-600 transition-colors select-none">
                                    กลุ่มทักษะ
                                    <span class="inline-block w-3 text-xs text-center {{ $sortBy === 'group' ? 'text-blue-600' : 'text-gray-400' }}">
                                        {{ $sortBy === 'group' ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}
                                    </span>
                                </a>
                            </th>

                            <th class="text-left py-2 px-3 w-2/12">
                                <a href="{{ $levelUrl }}"
                                   class="inline-flex items-center gap-1 hover:text-blue-600 transition-colors select-none">
                                    Level
                                    <span class="inline-block w-3 text-xs text-center {{ $sortBy === 'level' ? 'text-blue-600' : 'text-gray-400' }}">
                                        {{ $sortBy === 'level' ? ($sortDir === 'desc' ? '↓' : '↑') : '↕' }}
                                    </span>
                                </a>
                            </th>

                            <th class="text-center py-2 px-3 w-1/12">ESCO URI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($skills as $skill)
                            @php
                                $groups = $skill->skillGroups->sortBy('name')->values();
                                // ถ้าเลือก filter กลุ่ม ให้แสดงเฉพาะกลุ่มที่เลือกเท่านั้น
                                if ($activeGroup) {
                                    $groups = $groups->filter(function ($g) use ($activeGroup) {
                                        return (int) $g->id === (int) $activeGroup->id;
                                    })->values();
                                }
                                // ตอนเรียงตามกลุ่ม ให้แสดงเฉพาะกลุ่มหลักที่ใช้จัดลำดับ
                                elseif (($sortBy ?? request('sort', 'name')) === 'group') {
                                    $groups = $groups->take(1)->values();
                                }
                            @endphp

                            @php
                                $skillGroupCount = $skill->skillGroups->count();
                                $maxGroups = max(1, (int) ($maxSkillGroupCount ?? 0));
                                $filledStars = (int) round(($skillGroupCount / $maxGroups) * 5);
                                $filledStars = max(0, min(5, $filledStars));
                            @endphp

                            @if($groups->isNotEmpty())
                                @foreach($groups as $group)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-2 px-3 font-medium text-gray-900">
                                            @if($skill->name)
                                                <a href="{{ request()->fullUrlWithQuery(['skill' => $skill->id, 'group' => null, 'page' => 1]) }}"
                                                   class="hover:text-blue-600 hover:underline transition-colors"
                                                   title="แสดงกลุ่มทั้งหมดของทักษะ {{ $skill->name }}">
                                                    {{ $skill->name }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td class="py-2 px-3 text-gray-600">
                                            <a href="{{ request()->fullUrlWithQuery(['group' => $group->id, 'skill' => null, 'page' => 1]) }}"
                                               class="hover:text-blue-600 hover:underline transition-colors"
                                               title="กรองเฉพาะหมวดหมู่ {{ $group->name }}">
                                                {{ $group->name }}
                                            </a>
                                        </td>

                                        <td class="py-2 px-3 text-amber-500 whitespace-nowrap" title="{{ $skillGroupCount }}/{{ $maxGroups }} กลุ่ม">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="{{ $i <= $filledStars ? 'text-amber-500' : 'text-gray-300' }}">★</span>
                                            @endfor
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
                            @else
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 font-medium text-gray-900">
                                        @if($skill->name)
                                            <a href="{{ request()->fullUrlWithQuery(['skill' => $skill->id, 'group' => null, 'page' => 1]) }}"
                                               class="hover:text-blue-600 hover:underline transition-colors"
                                               title="แสดงกลุ่มทั้งหมดของทักษะ {{ $skill->name }}">
                                                {{ $skill->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-gray-600">-</td>
                                    <td class="py-2 px-3 text-amber-500 whitespace-nowrap" title="{{ $skillGroupCount }}/{{ $maxGroups }} กลุ่ม">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= $filledStars ? 'text-amber-500' : 'text-gray-300' }}">★</span>
                                        @endfor
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
                            @endif
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
                        onchange="window.location.href = '{{ url()->current() }}?perPage=' + this.value + '&search={{ request('search') }}&sort={{ request('sort', 'name') }}&dir={{ request('dir', 'asc') }}&group={{ request('group') }}&skill={{ request('skill') }}'"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                    >
                        <option value="20" {{ request('perPage', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="25" {{ request('perPage', 20) == 25 ? 'selected' : '' }}>25</option>
                        <option value="30" {{ request('perPage', 20) == 30 ? 'selected' : '' }}>30</option>
                        <option value="40" {{ request('perPage', 20) == 40 ? 'selected' : '' }}>40</option>
                    </select>
                    <span class="text-sm text-gray-700">รายการต่อหน้า</span>

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
                            $lastPage    = $skills->lastPage();
                            $start       = max(1, $currentPage - 2);
                            $end         = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $skills->appends(request()->except('page'))->url(1) }}"
                               class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">
                                1
                            </a>
                            @if($start > 2)
                                <span class="px-2 text-gray-500">...</span>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            @if($i == $currentPage)
                                <span class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-lg text-sm font-medium">
                                    {{ $i }}
                                </span>
                            @else
                                <a href="{{ $skills->appends(request()->except('page'))->url($i) }}"
                                   class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">
                                    {{ $i }}
                                </a>
                            @endif
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="px-2 text-gray-500">...</span>
                            @endif
                            <a href="{{ $skills->appends(request()->except('page'))->url($lastPage) }}"
                               class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">
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
