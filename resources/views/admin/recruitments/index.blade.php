@extends('layouts.app')

@section('title', 'ประกาศงาน')

@section('content')
    <div class="flex flex-col gap-4 p-4 shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">
                    รายการประกาศงาน
                    <span class="opacity-70">
                        ของ {{ $company->co_name ?? ($provider->email ?? '-') }}
                    </span>
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
        <form method="GET" class="grid items-end grid-cols-1 gap-4 md:grid-cols-5">
                <fieldset class="fieldset">
                    <legend class="mb-1 fieldset-legend">ค้นหา</legend>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                        class="w-full border border-gray-300 input input-bordered" placeholder="ชื่องาน/รายละเอียด/คุณสมบัติ">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="mb-1 fieldset-legend">สถานะ</legend>
                    <select name="status" class="w-full border border-gray-300 select select-bordered">
                        <option value="">— ทั้งหมด —</option>
                        @foreach (['open' => 'เปิดรับ', 'closed' => 'ปิดรับ', 'draft' => 'ฉบับร่าง'] as $k => $v)
                            <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="mb-1 fieldset-legend">ประเภท</legend>
                    <select name="type" class="w-full border border-gray-300 select select-bordered">
                        <option value="">— ทั้งหมด —</option>
                        @foreach (['full-time' => 'Full-time', 'part-time' => 'Part-time', 'intern' => 'Intern', 'freelance' => 'Freelance'] as $k => $v)
                            <option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="mb-1 fieldset-legend">โหมดทำงาน</legend>
                    <select name="work_mode" class="w-full border border-gray-300 select select-bordered">
                        <option value="">— ทั้งหมด —</option>
                        @foreach (['onsite' => 'Onsite', 'remote' => 'Remote', 'hybrid' => 'Hybrid'] as $k => $v)
                            <option value="{{ $k }}" @selected(($filters['work_mode'] ?? '') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </fieldset>
            <div class="flex gap-2 md:col-span-5">
                <button class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ค้นหา</button>
                <a href="{{ url()->current() }}" class="btn">ล้าง</a>

            </div>
        </form>

        <div class="flex flex-col items-center justify-center p-4 overflow-x-auto border shadow bg-base-200 rounded-2xl">
            <table class="table">
                <thead>
                    <tr>
                        <th>ชื่องาน</th>
                        <th>ประเภท</th>
                        <th>โพสต์เมื่อ</th>
                        <th>หมดอายุ</th>
                        <th>สถานะ</th>
                        <th>การทำงาน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recs as $r)
                        <tr>
                            <td class="max-w-[320px]">
                                <div class="line-clamp-1" title="$r->rc_title">{{ Str::limit($r->rc_title, 30, '...') }}</div>
                                <div class="text-xs opacity-70 line-clamp-1">
                                    {{ $r->rc_location_text ?? 'ไม่ใส่ที่อยู่' }}
                                </div>
                            </td>
                            <td>
                                <div class="mr-1 badge">{{ ucfirst($r->rc_type) }}</div>
                            </td>
                            <td>{{ optional($r->rc_posted_at)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>
                                @if ($r->rc_expire_at)
                                    {{ \Illuminate\Support\Carbon::parse($r->rc_expire_at)->format('Y-m-d') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColor =
                                        [
                                            'open' => 'text-green-600 bg-green-200',
                                            'closed' => 'text-gray-600 bg-gray-200',
                                            'draft' => 'text-yellow-700 bg-yellow-200',
                                        ][$r->rc_status] ?? 'bg-gray-200';
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 text-sm rounded-full {{ $statusColor }}">
                                        {{ $r->rc_status === 'open' ? 'เปิดรับ' : ($r->rc_status === 'closed' ? 'ปิดรับ' : 'ฉบับร่าง') }}
                                    </span>
                                    {{-- Toggle publish/draft --}}

                                </div>
                            </td>
                            <td>
                                <div class="gap-2 join">
                                    {{-- Toggle eye open/closed --}}
                                    @php
                                        $toVal = $r->rc_status === 'open' ? 'draft' : 'open';
                                        $isOpen = $r->rc_status === 'open';
                                    @endphp
                                    @if ($isAdmin)
                                        <form method="POST" action="{{ route('admin.recruitments.status', $r->rc_id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="to" value="{{ $toVal }}">
                                            <button type="submit"
                                                class="flex items-center justify-center w-10 h-10 transition border border-gray-400 rounded-2xl bg-base-100 {{ $isOpen ? 'hover:bg-blue-600' : 'hover:bg-blue-600' }}"
                                                title="{{ $isOpen ? 'ตั้งเป็นฉบับร่าง' : 'เผยแพร่' }}">
                                                <i class="fa-solid {{ $isOpen ? 'fa-eye' : 'fa-eye-slash' }} {{ $isOpen ? 'text-gray-600' : 'text-gray-600' }}"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.recruitments.edit', $r->rc_id) }}"
                                           class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-blue-600 hover:text-white"
                                           title="แก้ไข">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.recruitments.destroy', $r->rc_id) }}" onsubmit="return confirm('ยืนยันลบประกาศงาน “{{ addslashes($r->rc_title) }}” ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-red-600 hover:text-white"
                                                title="ลบ">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('provider.recruitments.status', $r->rc_id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="to" value="{{ $toVal }}">
                                            <button type="submit"
                                                class="flex items-center justify-center w-10 h-10 transition border border-gray-400 rounded-2xl bg-base-100 {{ $isOpen ? 'hover:bg-yellow-200' : 'hover:bg-green-200' }}"
                                                title="{{ $isOpen ? 'ตั้งเป็นฉบับร่าง' : 'เผยแพร่' }}">
                                                <i class="fa-solid {{ $isOpen ? 'fa-eye' : 'fa-eye-slash' }} {{ $isOpen ? 'text-blue-600' : 'text-gray-600' }}"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('provider.recruitments.edit', $r->rc_id) }}"
                                           class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-blue-600 hover:text-white"
                                           title="แก้ไข">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form method="POST" action="{{ route('provider.recruitments.destroy', $r->rc_id) }}" onsubmit="return confirm('ยืนยันลบประกาศงาน “{{ addslashes($r->rc_title) }}” ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-red-600 hover:text-white"
                                                title="ลบ">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-gray-500">
                                ไม่มีประกาศงาน
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination แบบ join เดิม --}}
        <div class="flex justify-center mt-4">
            @if ($recs->lastPage() > 1)
                <div class="join">
                    @php
                        $current = $recs->currentPage();
                        $last = $recs->lastPage();
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
@endsection
