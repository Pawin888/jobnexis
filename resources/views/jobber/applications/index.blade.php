@extends('layouts.app')

@section('title', 'ติดตามการสมัครงาน')

@section('content')
<div class="flex flex-col gap-4 p-4 shadow bg-base-200 rounded-2xl">
    <div>
        <h1 class="text-xl font-semibold">ติดตามการสมัครงาน</h1>
        <p class="text-sm opacity-70">ดูสถานะใบสมัครและรายการเชิญสมัครงานของคุณ</p>
    </div>

    {{-- Invite Section --}}
    <div class="overflow-x-auto rounded-2xl border shadow bg-base-200">
        <div class="bg-base-300 text-xs uppercase tracking-wide py-3 px-4 font-semibold">การเชิญสมัคร (Invite)</div>
        <table class="table table-fixed w-full min-w-[820px]">
            <thead class="bg-base-100 text-xs uppercase tracking-wide">
                <tr>
                    <th class="w-[30%] py-3 px-4">ตำแหน่งงาน</th>
                    <th class="w-[22%] py-3 px-3">ผู้ประกอบการ</th>
                    <th class="w-[18%] py-3 px-3">วันที่เชิญ</th>
                    <th class="w-[15%] py-3 px-3">สถานะ</th>
                    <th class="w-[15%] py-3 px-3 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-base-300">
                @forelse($invites as $invite)
                    <tr class="hover:bg-base-100 transition-colors">
                        <td class="py-3 px-4 max-w-0">
                            <div class="font-medium truncate text-sm">{{ $invite->recruitment->rc_title ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-3 text-sm">
                            {{ $invite->provider->companyProfile->co_name ?? ($invite->provider->profile->up_name ?? $invite->provider->email ?? '-') }}
                        </td>
                        <td class="py-3 px-3 text-xs">
                            {{ optional($invite->invited_at)->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 inline-block"></span> เชิญสมัคร
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($invite->recruitment)
                                    <a href="{{ route('jobber.jobs.show', $invite->recruitment->rc_id) }}"
                                       class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-blue-50 hover:border-blue-400 transition text-gray-600 hover:text-blue-600"
                                       title="ดูประกาศงาน">
                                        <i class="fa-solid fa-search text-sm"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">ยังไม่มีการเชิญสมัครงาน</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Applications Section --}}
    <div class="overflow-x-auto rounded-2xl border shadow bg-base-200">
        <div class="bg-base-300 text-xs uppercase tracking-wide py-3 px-4 font-semibold">รายการงานที่สมัคร</div>
        <table class="table table-fixed w-full min-w-[820px]">
            <thead class="bg-base-100 text-xs uppercase tracking-wide">
                <tr>
                    <th class="w-[32%] py-3 px-4">ตำแหน่งงาน</th>
                    <th class="w-[24%] py-3 px-3">ผู้ประกอบการ</th>
                    <th class="w-[16%] py-3 px-3">วันที่สมัคร</th>
                    <th class="w-[14%] py-3 px-3">สถานะ</th>
                    <th class="w-[14%] py-3 px-3 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-base-300">
                @forelse($applications as $app)
                    @php
                        $statusMeta = match($app->status) {
                            'accepted' => ['ผ่าน', 'bg-green-100 text-green-700', 'bg-green-500'],
                            'rejected' => ['ไม่ผ่าน', 'bg-red-100 text-red-700', 'bg-red-500'],
                            default => ['รอพิจารณา', 'bg-yellow-100 text-yellow-700', 'bg-yellow-500'],
                        };
                    @endphp
                    <tr class="hover:bg-base-100 transition-colors">
                        <td class="py-3 px-4 max-w-0">
                            <div class="font-medium truncate text-sm">{{ $app->recruitment->rc_title ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-3 text-sm">
                            {{ $app->recruitment->owner->companyProfile->co_name ?? ($app->recruitment->owner->profile->up_name ?? $app->recruitment->owner->email ?? '-') }}
                        </td>
                        <td class="py-3 px-3 text-xs">
                            {{ optional($app->applied_at)->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusMeta[1] }}">
                                <span class="w-1.5 h-1.5 rounded-full inline-block {{ $statusMeta[2] }}"></span> {{ $statusMeta[0] }}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($app->recruitment)
                                    <a href="{{ route('jobber.jobs.show', $app->recruitment->rc_id) }}"
                                       class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-blue-50 hover:border-blue-400 transition text-gray-600 hover:text-blue-600"
                                       title="ดูประกาศงาน">
                                        <i class="fa-solid fa-search text-sm"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">ยังไม่มีรายการสมัครงาน</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($applications->isNotEmpty())
        <div class="bg-white rounded-xl shadow p-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
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
                </div>

                <div class="flex items-center gap-2">
                    @if ($applications->onFirstPage())
                        <button disabled class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm">ก่อนหน้า</button>
                    @else
                        <a href="{{ $applications->appends(request()->except('page'))->previousPageUrl() }}"
                           class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">ก่อนหน้า</a>
                    @endif

                    <div class="flex items-center gap-1">
                        @php
                            $current = $applications->currentPage();
                            $last    = $applications->lastPage();
                            $start   = max(1, $current - 2);
                            $end     = min($last, $current + 2);
                        @endphp

                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $current)
                                <span class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-lg text-sm font-medium">{{ $i }}</span>
                            @else
                                <a href="{{ $applications->appends(request()->except('page'))->url($i) }}"
                                   class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">{{ $i }}</a>
                            @endif
                        @endfor
                    </div>

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
@endsection

@push('scripts')
<script>
    @if (session('swal_success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: '{{ session('swal_success') }}',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    @endif

    @if (session('swal_error'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: '{{ session('swal_error') }}',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    @endif
</script>
@endpush
