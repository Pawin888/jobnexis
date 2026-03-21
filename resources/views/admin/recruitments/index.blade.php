@extends('layouts.app')

@section('title', 'ประกาศงาน')

@section('content')
    <div class="flex flex-col gap-4 p-4 shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">
                    รายการประกาศงาน
                </h1>
                <p class="text-sm opacity-70">
                    ทั้งหมด {{ number_format($recs->total()) }} รายการ
                </p>
            </div>
            <div class="flex gap-2">
                @if ($isAdmin)
                    <a href="{{ route('admin.providers.recruitments.create', $ownerId) }}"
                        class="p-2 text-blue-600 border-2 border-blue-600 border-dashed rounded-lg btn btn-sm">
                        <i class="mr-2 fa-solid fa-plus"></i> เพิ่มประกาศงาน
                    </a>
                @else
                    <a href="{{ route('provider.recruitments.create') }}"
                        class="p-2 text-blue-600 border-2 border-blue-600 border-dashed rounded-lg btn btn-sm">
                        <i class="mr-2 fa-solid fa-plus"></i> เพิ่มประกาศงาน
                    </a>
                @endif
            </div>
        </div>

        {{-- ฟิลเตอร์ --}}
        <form method="GET" class="grid items-end grid-cols-1 gap-4 md:grid-cols-4">
            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">ค้นหา</legend>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                    class="pl-2 w-full border border-gray-300 input input-bordered" placeholder="ชื่องาน/รายละเอียด/คุณสมบัติ">
            </fieldset>
            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">สถานะ</legend>
                <select name="status" class="w-full border border-gray-300 select select-bordered pl-2">
                    <option value="">— ทั้งหมด —</option>
                    @foreach (['open' => 'เปิดรับ', 'inactive' => 'ปิดรับ/หมดอายุ'] as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">ประเภท</legend>
                <select name="type" class="w-full border border-gray-300 select select-bordered pl-2">
                    <option value="">— ทั้งหมด —</option>
                    @foreach (['full-time' => 'เต็มเวลา (Full-time)', 'part-time' => 'พาร์ทไทม์ (Part-time)', 'intern' => 'ฝึกงาน (Internship)', 'freelance' => 'ฟรีแลนซ์ (Freelance)'] as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">โหมดการทำงาน</legend>
                <div class="flex gap-2">
                    <select name="work_mode" class="w-full border border-gray-300 select select-bordered pl-2">
                        <option value="">— ทั้งหมด —</option>
                        @foreach (['onsite' => 'เข้าออฟฟิศ (Work on Site)', 'remote' => 'ทำที่บ้าน (Work from Home)', 'hybrid' => 'ผสมผสาน (Hybrid Work)', 'distributed' => 'ทำที่ไหนก็ได้ (Distributed Work)'] as $k => $v)
                            <option value="{{ $k }}" @selected(($filters['work_mode'] ?? '') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ค้นหา</button>
                    <a href="{{ url()->current() }}" class="btn">ล้าง</a>
                </div>
            </fieldset>
        </form>

        <div class="overflow-x-auto rounded-2xl border shadow bg-base-200">
            <table class="table table-fixed w-full min-w-[820px]">
                <thead class="bg-base-300 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="w-[26%] py-3 px-4">ชื่องาน / สถานที่</th>
                        <th class="w-[12%] py-3 px-3">ประเภทงาน</th>
                        <th class="w-[13%] py-3 px-3">โหมดการทำงาน</th>
                        <th class="w-[11%] py-3 px-3">เปิดรับเมื่อ</th>
                        <th class="w-[13%] py-3 px-3">ปิดรับ/หมดอายุ</th>
                        <th class="w-[11%] py-3 px-3">สถานะ</th>
                        <th class="w-[14%] py-3 px-3">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-300">
                    @forelse ($recs as $r)
                        @php
                            $isExpired = $r->rc_expire_at && \Illuminate\Support\Carbon::parse($r->rc_expire_at)->endOfDay()->isPast();
                            $isExpireToday = $r->rc_expire_at && \Illuminate\Support\Carbon::parse($r->rc_expire_at)->isToday();
                            $isOpenActive = $r->rc_status === 'open' && !$isExpired;
                            $toVal = $isOpenActive ? 'closed' : 'open';

                            // Type badge color
                            $typeBadgeColor = match($r->rc_type) {
                                'full-time'  => 'bg-blue-100 text-blue-700',
                                'part-time'  => 'bg-purple-100 text-purple-700',
                                'intern'     => 'bg-green-100 text-green-700',
                                'freelance'  => 'bg-orange-100 text-orange-700',
                                default      => 'bg-gray-100 text-gray-500',
                            };
                            $typeShort = match($r->rc_type) {
                                'full-time'  => 'เต็มเวลา',
                                'part-time'  => 'พาร์ทไทม์',
                                'intern'     => 'ฝึกงาน',
                                'freelance'  => 'ฟรีแลนซ์',
                                default      => '—',
                            };

                            // Work mode badge color
                            $modeBadgeColor = match($r->rc_work_mode) {
                                'onsite'      => 'bg-sky-100 text-sky-700',
                                'remote'      => 'bg-teal-100 text-teal-700',
                                'hybrid'      => 'bg-violet-100 text-violet-700',
                                'distributed' => 'bg-amber-100 text-amber-700',
                                default       => 'bg-gray-100 text-gray-500',
                            };
                            $modeShort = match($r->rc_work_mode) {
                                'onsite'      => 'เข้าออฟฟิศ',
                                'remote'      => 'Work from Home',
                                'hybrid'      => 'Hybrid',
                                'distributed' => 'Distributed',
                                default       => '—',
                            };
                        @endphp
                        <tr class="hover:bg-base-100 transition-colors">
                            <td class="py-3 px-4 max-w-0">
                                <div class="font-medium truncate text-sm" title="{{ $r->rc_title }}">{{ $r->rc_title }}</div>
                                @if($r->rc_location_text)
                                    <div class="text-xs opacity-60 truncate mt-0.5">
                                        <i class="fa-solid fa-location-dot mr-1"></i>{{ $r->rc_location_text }}
                                    </div>
                                @else
                                    <div class="text-xs opacity-40 mt-0.5">ไม่ระบุสถานที่</div>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadgeColor }}">
                                    {{ $typeShort }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $modeBadgeColor }}">
                                    {{ $modeShort }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-xs">
                                @if(!$r->rc_posted_at)
                                    <span class="text-gray-500 opacity-70">ยังไม่เปิดรับ</span>
                                @else
                                    <span class="text-gray-600">{{ optional($r->rc_posted_at)->format('d/m/Y') ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-xs">
                                @if($isExpired)
                                    <span class="text-red-600 font-medium">{{ \Illuminate\Support\Carbon::parse($r->rc_expire_at)->format('d/m/Y') }}</span>
                                    <div class="text-xs text-red-400 mt-0.5">หมดอายุแล้ว</div>
                                @elseif($r->rc_status === 'closed')
                                    @if ($r->rc_expire_at)
                                        <span class="rounded-full text-xs text-gray-700">ปิดรับแล้ว</span>
                                    @else
                                        <span class="rounded-full text-xs text-gray-700">ปิดรับแล้ว</span>
                                    @endif
                                @elseif ($r->rc_expire_at)
                                    <span class="text-gray-700">{{ \Illuminate\Support\Carbon::parse($r->rc_expire_at)->format('d/m/Y') }}</span>
                                    <div class="text-xs mt-0.5 {{ $isExpireToday ? 'text-amber-500' : 'text-gray-400' }}">
                                        {{ $isExpireToday ? 'หมดอายุวันนี้' : 'ยังไม่หมดอายุ' }}
                                    </div>
                                @else
                                    <span class="rounded-full text-xs text-emerald-500">เปิดรับตลอด</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($isOpenActive)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> เปิดรับ
                                    </span>
                                @elseif($isExpired)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span> หมดอายุ
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span> ปิดรับ
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex gap-1.5">
                                    @if ($isAdmin)
                                        @if(!$isExpired)
                                            <!-- Not expired: allow status toggle, edit, QR -->
                                            @if(!$r->rc_expire_at)
                                                <form method="POST" action="{{ route('admin.recruitments.status', $r->rc_id) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="to" value="{{ $toVal }}">
                                                    <button type="submit"
                                                        class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-blue-50 hover:border-blue-400 transition"
                                                        title="{{ $isOpenActive ? 'ปิดรับ' : 'เปิดรับ' }}">
                                                        <i class="fa-solid {{ $isOpenActive ? 'fa-eye' : 'fa-eye-slash' }} text-sm {{ $isOpenActive ? 'text-blue-500' : 'text-gray-400' }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.recruitments.edit', $r->rc_id) }}"
                                               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-blue-50 hover:border-blue-400 transition text-gray-600 hover:text-blue-600"
                                               title="แก้ไข">
                                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                                            </a>
                                            @if($isOpenActive)
                                                <button type="button"
                                                    class="qr-link-btn flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-emerald-50 hover:border-emerald-400 transition text-gray-600 hover:text-emerald-600"
                                                    data-job-url="{{ route('jobs.show', $r->rc_id) }}"
                                                    data-job-title="{{ $r->rc_title }}"
                                                    title="ลิ้งก์และ QR Code">
                                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                                </button>
                                            @endif
                                        @else
                                            <!-- Expired: only show view details -->
                                            <a href="{{ route('admin.recruitments.show', $r->rc_id) }}"
                                               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-blue-50 hover:border-blue-400 transition text-gray-600 hover:text-blue-600"
                                               title="ดูรายละเอียด">
                                                <i class="fa-solid fa-file-lines text-sm"></i>
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('admin.recruitments.destroy', $r->rc_id) }}" class="delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                class="delete-btn flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-red-50 hover:border-red-400 transition text-gray-600 hover:text-red-600"
                                                data-title="{{ addslashes($r->rc_title) }}"
                                                title="ลบ">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    @else
                                        @if(!$isExpired)
                                            <!-- Not expired: allow status toggle, edit, QR -->
                                            @if(!$r->rc_expire_at)
                                                <form method="POST" action="{{ route('provider.recruitments.status', $r->rc_id) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="to" value="{{ $toVal }}">
                                                    <button type="submit"
                                                        class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-blue-50 hover:border-blue-400 transition"
                                                        title="{{ $isOpenActive ? 'ปิดรับ' : 'เปิดรับ' }}">
                                                        <i class="fa-solid {{ $isOpenActive ? 'fa-eye' : 'fa-eye-slash' }} text-sm {{ $isOpenActive ? 'text-blue-500' : 'text-gray-400' }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('provider.recruitments.edit', $r->rc_id) }}"
                                               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-blue-50 hover:border-blue-400 transition text-gray-600 hover:text-blue-600"
                                               title="แก้ไข">
                                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                                            </a>
                                            @if($isOpenActive)
                                                <button type="button"
                                                    class="qr-link-btn flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-emerald-50 hover:border-emerald-400 transition text-gray-600 hover:text-emerald-600"
                                                    data-job-url="{{ route('jobs.show', $r->rc_id) }}"
                                                    data-job-title="{{ $r->rc_title }}"
                                                    title="ลิ้งก์และ QR Code">
                                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                                </button>
                                            @endif
                                        @else
                                            <!-- Expired: only show view details -->
                                            <a href="{{ route('provider.recruitments.show', $r->rc_id) }}"
                                               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-blue-50 hover:border-blue-400 transition text-gray-600 hover:text-blue-600"
                                               title="ดูรายละเอียด">
                                                <i class="fa-solid fa-file-lines text-sm"></i>
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('provider.recruitments.destroy', $r->rc_id) }}" class="delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                class="delete-btn flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-red-50 hover:border-red-400 transition text-gray-600 hover:text-red-600"
                                                data-title="{{ addslashes($r->rc_title) }}"
                                                title="ลบ">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-gray-400">
                                <i class="fa-solid fa-briefcase text-3xl mb-3 block opacity-40"></i>
                                ไม่มีประกาศงาน
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($recs->isNotEmpty())
            <div class="bg-white rounded-xl shadow p-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <label for="perPage" class="text-sm text-gray-700 font-medium">แสดง:</label>
                        <select
                            id="perPage"
                            onchange="window.location.href = '{{ url()->current() }}?perPage=' + this.value + '&q={{ request('q') }}&status={{ request('status') }}&type={{ request('type') }}&work_mode={{ request('work_mode') }}'"
                            class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                        >
                            @foreach ([10, 20, 30, 50] as $n)
                                <option value="{{ $n }}" {{ request('perPage', 20) == $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                        <span class="text-sm text-gray-700">รายการต่อหน้า</span>
                        <span class="text-sm text-gray-600 ml-4">
                            (แสดง
                            <span class="font-semibold text-gray-800">{{ $recs->firstItem() ?? 0 }}</span>
                            -
                            <span class="font-semibold text-gray-800">{{ $recs->lastItem() ?? 0 }}</span>
                            จากทั้งหมด
                            <span class="font-semibold text-gray-800">{{ number_format($recs->total()) }}</span>
                            รายการ)
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($recs->onFirstPage())
                            <button disabled class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm">ก่อนหน้า</button>
                        @else
                            <a href="{{ $recs->appends(request()->except('page'))->previousPageUrl() }}"
                               class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">ก่อนหน้า</a>
                        @endif
                        <div class="flex items-center gap-1">
                            @php
                                $current = $recs->currentPage();
                                $last = $recs->lastPage();
                                $start = max(1, $current - 2);
                                $end = min($last, $current + 2);
                            @endphp
                            @if ($start > 1)
                                <a href="{{ $recs->appends(request()->except('page'))->url(1) }}"
                                   class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">1</a>
                                @if ($start > 2)
                                    <span class="px-2 text-gray-500">...</span>
                                @endif
                            @endif
                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $current)
                                    <span class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-lg text-sm font-medium">{{ $i }}</span>
                                @else
                                    <a href="{{ $recs->appends(request()->except('page'))->url($i) }}"
                                       class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">{{ $i }}</a>
                                @endif
                            @endfor
                            @if ($end < $last)
                                @if ($end < $last - 1)
                                    <span class="px-2 text-gray-500">...</span>
                                @endif
                                <a href="{{ $recs->appends(request()->except('page'))->url($last) }}"
                                   class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">{{ $last }}</a>
                            @endif
                        </div>
                        @if ($recs->hasMorePages())
                            <a href="{{ $recs->appends(request()->except('page'))->nextPageUrl() }}"
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
    // ===== Delete confirmation =====
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = this.closest('.delete-form');
            const title = this.dataset.title;
            Swal.fire({
                title: 'ยืนยันการลบ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fa-solid fa-trash" style="margin-right:6px"></i> ลบ',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    // ===== Shared QR / Link modal =====
    function openQrModal(jobUrl, jobTitle, isNew) {
        isNew = isNew || false;
        Swal.fire({
            title: isNew
                ? '<span style="font-size:1.1rem;font-weight:700">บันทึกสำเร็จ! 🎉</span>'
                : '<span style="font-size:1.1rem;font-weight:700"><i class="fa-solid fa-qrcode" style="margin-right:6px;color:#059669"></i>ลิ้งก์และ QR Code</span>',
            html: `
                <p style="color:#6b7280;font-size:0.875rem;margin-bottom:8px">แชร์ให้ผู้สมัครเข้าดูประกาศงาน</p>
                <p style="font-weight:600;margin-bottom:12px;font-size:0.9rem">${jobTitle}</p>
                <div style="display:flex;gap:6px;margin-bottom:16px;align-items:stretch">
                    <input id="swal-job-url" value="${jobUrl}" readonly
                        onclick="this.select()"
                        style="flex:1;padding:7px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:0.78rem;outline:none;background:#f9fafb;color:#374151;min-width:0;cursor:pointer">
                    <button id="swal-copy-btn"
                        style="padding:7px 14px;background:#2563eb;color:white;border:none;border-radius:8px;cursor:pointer;font-size:0.82rem;white-space:nowrap;flex-shrink:0">
                        <i class="fa-solid fa-copy"></i> คัดลอก
                    </button>
                </div>
                <div style="display:flex;justify-content:center;margin-bottom:12px">
                    <div id="swal-qr-div" style="background:#fff;padding:8px;border-radius:10px;border:1px solid #e5e7eb;display:inline-block"></div>
                </div>
                <button id="swal-dl-btn"
                    style="padding:7px 18px;background:#059669;color:white;border:none;border-radius:8px;cursor:pointer;font-size:0.82rem">
                    <i class="fa-solid fa-download"></i> ดาวน์โหลด QR Code
                </button>
                <p style="font-size:0.75rem;color:#9ca3af;margin-top:8px">สแกน QR Code เพื่อเปิดประกาศงาน</p>
            `,
            showConfirmButton: isNew,
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#2563eb',
            showCloseButton: !isNew,
            focusConfirm: false,
            width: 480,
            didOpen: () => {
                const qrDiv = document.getElementById('swal-qr-div');

                // Render QR Code
                new QRCode(qrDiv, {
                    text: jobUrl,
                    width: 200,
                    height: 200,
                    colorDark: '#1e3a5f',
                    colorLight: '#ffffff',
                });

                // Copy button — proper event listener (works on HTTP & HTTPS)
                document.getElementById('swal-copy-btn').addEventListener('click', function () {
                    const btn = this;
                    const restore = () => setTimeout(() => {
                        btn.innerHTML = '<i class="fa-solid fa-copy"></i> คัดลอก';
                        btn.style.background = '#2563eb';
                    }, 2000);
                    const onSuccess = () => {
                        btn.innerHTML = '<i class="fa-solid fa-check"></i> คัดลอกแล้ว';
                        btn.style.background = '#16a34a';
                        restore();
                    };
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(jobUrl).then(onSuccess).catch(() => {
                            const inp = document.getElementById('swal-job-url');
                            inp.removeAttribute('readonly');
                            inp.select();
                            document.execCommand('copy');
                            inp.setAttribute('readonly', '');
                            onSuccess();
                        });
                    } else {
                        const inp = document.getElementById('swal-job-url');
                        inp.removeAttribute('readonly');
                        inp.select();
                        document.execCommand('copy');
                        inp.setAttribute('readonly', '');
                        onSuccess();
                    }
                });

                // Download QR button
                document.getElementById('swal-dl-btn').addEventListener('click', function () {
                    setTimeout(() => {
                        const canvas = qrDiv.querySelector('canvas');
                        const img    = qrDiv.querySelector('img');
                        let dataUrl;
                        if (canvas) {
                            dataUrl = canvas.toDataURL('image/png');
                        } else if (img) {
                            const c = document.createElement('canvas');
                            c.width  = img.naturalWidth  || img.width  || 200;
                            c.height = img.naturalHeight || img.height || 200;
                            c.getContext('2d').drawImage(img, 0, 0);
                            dataUrl = c.toDataURL('image/png');
                        }
                        if (!dataUrl) return;
                        const a = document.createElement('a');
                        a.href = dataUrl;
                        a.download = 'qr-job.png';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    }, 150);
                });
            },
        });
    }

    // ===== Per-row QR buttons =====
    document.querySelectorAll('.qr-link-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            openQrModal(this.dataset.jobUrl, this.dataset.jobTitle);
        });
    });

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

    @if (session('swal_qr'))
        @php $qrData = session('swal_qr'); @endphp
        openQrModal('{{ route('jobs.show', $qrData['rc_id']) }}', @json($qrData['rc_title']), true);
    @endif
</script>
@endpush
