@extends('layouts.app')

@section('title', 'ใบสมัครงาน')

@section('content')
<div class="flex flex-col gap-4 p-4 shadow bg-base-200 rounded-2xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">รายการใบสมัครงาน</h1>
            <p class="text-sm opacity-70">ทั้งหมด {{ number_format($applications->total()) }} รายการ</p>
        </div>
    </div>

    {{-- ฟิลเตอร์ --}}
    <div class="grid items-end grid-cols-1 gap-4 md:grid-cols-4">
        <fieldset class="fieldset">
            <legend class="mb-1 fieldset-legend">ชื่อ-นามสกุล</legend>
            <input type="text" id="filterName"
                class="w-full border border-gray-300 input input-bordered"
                placeholder="ค้นหาชื่อผู้สมัคร">
        </fieldset>
        <fieldset class="fieldset">
            <legend class="mb-1 fieldset-legend">ชื่องาน</legend>
            <input type="text" id="filterTitle"
                class="w-full border border-gray-300 input input-bordered"
                placeholder="ค้นหาตำแหน่งงาน">
        </fieldset>
        <fieldset class="fieldset">
            <legend class="mb-1 fieldset-legend">สถานะ</legend>
            <select id="filterStatus" class="w-full border border-gray-300 select select-bordered">
                <option value="">— ทั้งหมด —</option>
                <option value="รอประเมิน">รอประเมิน</option>
                <option value="ยอมรับ">ยอมรับ</option>
                <option value="ปฏิเสธ">ปฏิเสธ</option>
            </select>
        </fieldset>
        <fieldset class="fieldset">
            <legend class="mb-1 fieldset-legend">วันที่สมัคร</legend>
            <div class="flex gap-2">
                <input type="date" id="filterDate"
                    class="w-full border border-gray-300 input input-bordered">
                <button onclick="filterTable()" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ค้นหา</button>
                <a href="{{ url()->current() }}" class="btn">ล้าง</a>
            </div>
        </fieldset>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto border shadow bg-base-200 rounded-2xl p-4">
        <table class="table table-fixed w-full">
            <thead>
                <tr>
                    <th class="w-[25%]">ตำแหน่งงาน</th>
                    <th class="w-[20%]">ผู้สมัคร</th>
                    <th class="w-[15%]">รีซูเม</th>
                    <th class="w-[17%]">วันที่สมัคร</th>
                    <th class="w-[13%]">สถานะ</th>
                    <th class="w-[10%]">การทำงาน</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($applications as $app)
                    <tr class="table-row">
                        <td class="max-w-0">
                            <div class="truncate col-title">{{ Str::limit($app->recruitment->rc_title, 30, '...') }}</div>
                        </td>
                        <td class="col-name truncate">{{ $app->jobber->profile->up_name ?? $app->jobber->email }}</td>
                        <td class="truncate">{{ $app->resume->first_name }} {{ $app->resume->last_name }}</td>
                        <td class="col-date" data-date="{{ \Carbon\Carbon::parse($app->applied_at)->format('Y-m-d') }}">
                            {{ \Carbon\Carbon::parse($app->applied_at)->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @php
                                $statusColor = [
                                    'reviewing' => 'text-yellow-700 bg-yellow-200',
                                    'accepted'  => 'text-green-600 bg-green-200',
                                    'rejected'  => 'text-red-600 bg-red-200',
                                ][$app->status] ?? 'bg-gray-200';
                                $statusLabel = [
                                    'reviewing' => 'รอประเมิน',
                                    'accepted'  => 'ยอมรับ',
                                    'rejected'  => 'ปฏิเสธ',
                                ][$app->status] ?? $app->status;
                            @endphp
                            <span class="px-3 py-1 text-sm rounded-full col-status {{ $statusColor }}">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            @if($app->status === 'reviewing')
                                <a href="{{ route('provider.applications.show', $app->id) }}"
                                   class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-blue-600 hover:text-white"
                                   title="ประเมิน">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            @else
                                <a href="{{ route('provider.applications.show', $app->id) }}"
                                   class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-blue-600 hover:text-white"
                                   title="ดูรายละเอียด">
                                    <i class="fa-solid fa-circle-info"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-gray-500">ไม่มีใบสมัคร</td>
                    </tr>
                @endforelse
                <tr id="emptyRow" style="display: none;">
                    <td colspan="6" class="py-10 text-center text-gray-500">ไม่พบใบสมัครงาน</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($applications->isNotEmpty())
        <div class="bg-white rounded-xl shadow p-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

                {{-- Left: Items per page + Info --}}
                <div class="flex items-center gap-2">
                    <label for="perPage" class="text-sm text-gray-700 font-medium">แสดง:</label>
                    <select
                        id="perPage"
                        onchange="window.location.href = '{{ url()->current() }}?perPage=' + this.value"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                    >
                        @foreach ([10, 20, 30, 50] as $n)
                            <option value="{{ $n }}" {{ request('perPage', 20) == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                    <span class="text-sm text-gray-700">รายการต่อหน้า</span>

                    <span class="text-sm text-gray-600 ml-4">
                        (แสดง
                        <span class="font-semibold text-gray-800">{{ $applications->firstItem() ?? 0 }}</span>
                        -
                        <span class="font-semibold text-gray-800">{{ $applications->lastItem() ?? 0 }}</span>
                        จากทั้งหมด
                        <span class="font-semibold text-gray-800">{{ number_format($applications->total()) }}</span>
                        รายการ)
                    </span>
                </div>

                {{-- Right: Pagination Controls --}}
                <div class="flex items-center gap-2">
                    {{-- Previous --}}
                    @if ($applications->onFirstPage())
                        <button disabled class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm">ก่อนหน้า</button>
                    @else
                        <a href="{{ $applications->appends(request()->except('page'))->previousPageUrl() }}"
                           class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">ก่อนหน้า</a>
                    @endif

                    {{-- Page Numbers --}}
                    <div class="flex items-center gap-1">
                        @php
                            $current = $applications->currentPage();
                            $last    = $applications->lastPage();
                            $start   = max(1, $current - 2);
                            $end     = min($last, $current + 2);
                        @endphp

                        @if ($start > 1)
                            <a href="{{ $applications->appends(request()->except('page'))->url(1) }}"
                               class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">1</a>
                            @if ($start > 2)
                                <span class="px-2 text-gray-500">...</span>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $current)
                                <span class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-lg text-sm font-medium">{{ $i }}</span>
                            @else
                                <a href="{{ $applications->appends(request()->except('page'))->url($i) }}"
                                   class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">{{ $i }}</a>
                            @endif
                        @endfor

                        @if ($end < $last)
                            @if ($end < $last - 1)
                                <span class="px-2 text-gray-500">...</span>
                            @endif
                            <a href="{{ $applications->appends(request()->except('page'))->url($last) }}"
                               class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">{{ $last }}</a>
                        @endif
                    </div>

                    {{-- Next --}}
                    @if ($applications->hasMorePages())
                        <a href="{{ $applications->appends(request()->except('page'))->nextPageUrl() }}"
                           class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">ถัดไป</a>
                    @else
                        <button disabled class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm">ถัดไป</button>
                    @endif
                </div>

            </div>
        </div>
    @endif
</div>

<script>
    function filterTable() {
        const name   = document.getElementById('filterName').value.toLowerCase();
        const title  = document.getElementById('filterTitle').value.toLowerCase();
        const status = document.getElementById('filterStatus').value;
        const date   = document.getElementById('filterDate').value;

        let visibleCount = 0;

        document.querySelectorAll('#tableBody .table-row').forEach(row => {
            const colName   = row.querySelector('.col-name')?.textContent.toLowerCase() ?? '';
            const colTitle  = row.querySelector('.col-title')?.textContent.toLowerCase() ?? '';
            const colStatus = row.querySelector('.col-status')?.textContent.trim() ?? '';
            const colDate   = row.querySelector('.col-date')?.dataset.date ?? '';

            const match =
                colName.includes(name) &&
                colTitle.includes(title) &&
                (status === '' || colStatus === status) &&
                (date === '' || colDate === date);

            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        const emptyRow = document.getElementById('emptyRow');
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }

    function clearFilter() {
        document.getElementById('filterName').value   = '';
        document.getElementById('filterTitle').value  = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterDate').value   = '';
        filterTable();
    }
</script>

@endsection
