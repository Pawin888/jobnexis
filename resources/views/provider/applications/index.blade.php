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
        <table class="table">
            <thead>
                <tr>
                    <th>ตำแหน่งงาน</th>
                    <th>ผู้สมัคร</th>
                    <th>รีซูเม</th>
                    <th>วันที่สมัคร</th>
                    <th>สถานะ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($applications as $app)
                    <tr class="table-row">
                        <td class="max-w-[280px]">
                            <div class="line-clamp-1 col-title">{{ Str::limit($app->recruitment->rc_title, 30, '...') }}</div>
                        </td>
                        <td class="col-name">{{ $app->jobber->profile->up_name ?? $app->jobber->email }}</td>
                        <td>{{ $app->resume->first_name }} {{ $app->resume->last_name }}</td>
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
    <div class="flex justify-center mt-4">
        @if ($applications->lastPage() > 1)
            <div class="join">
                @php
                    $current = $applications->currentPage();
                    $last = $applications->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                @endphp

                @if ($start > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}"
                        class="join-item btn btn-sm {{ $current == 1 ? 'btn-active' : '' }}">1</a>
                    @if ($start > 2)
                        <span class="join-item btn btn-sm btn-disabled">...</span>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                        class="join-item btn btn-sm {{ $i == $current ? 'btn-active' : '' }}">{{ $i }}</a>
                @endfor

                @if ($end < $last)
                    @if ($end < $last - 1)
                        <span class="join-item btn btn-sm btn-disabled">...</span>
                    @endif
                    <a href="{{ request()->fullUrlWithQuery(['page' => $last]) }}"
                        class="join-item btn btn-sm {{ $current == $last ? 'btn-active' : '' }}">{{ $last }}</a>
                @endif
            </div>
        @endif
    </div>
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
