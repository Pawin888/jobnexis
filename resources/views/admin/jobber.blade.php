@extends('layouts.app')

@section('title', 'ผู้สมัครงาน')

@section('content')

    <div class="flex items-center justify-between p-4 overflow-x-auto border shadow bg-base-200 rounded-2xl ">
        <table class="table">
            <thead>
                <tr>
                    <th>ไอดี</th>
                    <th>ชื่อ-สกุล</th>
                    <th>อีเมล</th>
                    <th>สถานะ</th>
                    <th>การทำงาน</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pagedData as $row)
                    <tr>
                        <td>{{ $row['id'] }}</td>
                        <td>{{ $row->profile ? $row->profile->up_prefix . ' ' . $row->profile->up_name : '-' }}</td>
                        <td>{{ $row['email'] }}</td>
                        <td>
                            @php
                                $status = $row->is_banned
                                    ? 'Banned'
                                    : (is_null($row->email_verified_at)
                                        ? 'Pending'
                                        : 'Active');
                            @endphp
                            @if ($status === 'Active')
                                <span class="px-3 py-1 text-sm text-green-600 bg-green-200 rounded-full">ออนไลน์</span>
                            @elseif ($status === 'Pending')
                                <span class="px-3 py-1 text-sm text-gray-600 bg-gray-200 rounded-full">รอยืนยัน</span>
                            @else
                                <span class="px-3 py-1 text-sm text-red-600 bg-red-200 rounded-full">ถูกแบน</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $status = $row->is_banned
                                    ? 'Banned'
                                    : (is_null($row->email_verified_at)
                                        ? 'Pending'
                                        : 'Active');
                            @endphp
                            <div class="flex gap-2">
                                <a href="{{ route('profile-details.edit', $row->id) }}"
                                    class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-blue-600 hover:text-white"
                                    title="แก้ไข">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @if ($status === 'Active')
                                    {{-- แบน --}}
                                    <form method="POST" action="{{ route('admin.jobber.toggleBan', $row->id) }}"
                                        class="inline-flex">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-red-600 hover:text-white"
                                            title="แบน">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    </form>
                                @elseif ($status === 'Pending')
                                    {{-- ลบ (ยังไม่ยืนยันอีเมล) --}}
                                    <form method="POST" action="{{ route('admin.jobber.destroy', $row->id) }}"
                                        class="inline-flex"
                                        onsubmit="return confirm('ยืนยันลบผู้ใช้ #{{ $row->id }} ?');">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-red-600 hover:text-white"
                                            title="ลบ">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    {{-- ปลดแบน --}}
                                    <form method="POST" action="{{ route('admin.jobber.toggleBan', $row->id) }}"
                                        class="inline-flex">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-green-600 hover:text-white"
                                            title="ปลดแบน">
                                            <i class="fa-solid fa-user-check"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        {{-- ปรับจำนวน colspan ให้เท่ากับจำนวนคอลัมน์จริงของคุณ
             เช่น มี: ไอดี, ชื่อ, อีเมล, โทร, (คอร์ส), สถานะ, การทำงาน => 6 หรือ 7 --}}
                        <td colspan="6" class="py-10 text-center text-gray-500">
                            @if (request()->filled('q'))
                                ไม่พบผู้ใช้ที่เป็น <b>ผู้หางาน</b> ที่ตรงกับ
                                “<span class="font-semibold">{{ e(request('q')) }}</span>”
                                <a href="{{ url()->current() }}" class="ml-2 link">ล้างการค้นหา</a>
                            @else
                                ไม่มีผู้ใช้ที่เป็น <b>ผู้หางาน</b> ในระบบ
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Smart Pagination --}}
    <div class="flex justify-center mt-4">
        <div class="join">
            @php
                $current = $pagedData->currentPage();
                $last = $pagedData->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
            @endphp

            {{-- ปุ่มหน้าแรก --}}
            @if ($start > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}"
                    class="join-item btn btn-sm {{ $current == 1 ? 'btn-active' : '' }}">1</a>
                @if ($start > 2)
                    <span class="join-item btn btn-sm btn-disabled">...</span>
                @endif
            @endif

            {{-- ปุ่มช่วงกลาง --}}
            @for ($i = $start; $i <= $end; $i++)
                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                    class="join-item btn btn-sm {{ $i == $current ? 'btn-active' : '' }}">
                    {{ $i }}
                </a>
            @endfor

            {{-- ปุ่มหน้าสุดท้าย --}}
            @if ($end < $last)
                @if ($end < $last - 1)
                    <span class="join-item btn btn-sm btn-disabled">...</span>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['page' => $last]) }}"
                    class="join-item btn btn-sm {{ $current == $last ? 'btn-active' : '' }}">
                    {{ $last }}
                </a>
            @endif
        </div>
    </div>
@endsection
