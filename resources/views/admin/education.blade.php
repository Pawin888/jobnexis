@extends('layouts.app')


@section('title', 'สถาบันการศึกษา')


@section('content')
    <div class="p-4 overflow-x-auto border shadow bg-base-200 rounded-2xl">
        <table class="table">
            <thead>
                <tr>
                    <th>ชื่อสถาบัน</th>
                    <th>อีเมล</th>
                    <th>หมายเลขสถาบัน</th>
                    <th>คอร์ส</th>
                    <th>สถานะ</th>
                    <th>การทำงาน</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pagedData as $row)
                    <tr>
                        <td>{{ $row->e_name ?: '-' }}</td>
                        <td>{{ $row->institute_email ?: $row->user_email }}</td>
                        <td>{{ $row->e_number ?: '-' }}</td>
                        <td>{{ $row->courses_count ?? '-' }}</td>
                        <td>
                            @if ($row->is_banned === false && $row->email_verified_at !== null)
                                <span class="px-3 py-1 text-sm text-green-600 bg-green-200 rounded-full">ออนไลน์</span>
                            @elseif ($row->email_verified_at === null)
                                <span class="px-3 py-1 text-sm text-gray-600 bg-gray-200 rounded-full">รอยืนยัน</span>
                            @else
                                <span class="px-3 py-1 text-sm text-red-600 bg-red-200 rounded-full">ถูกแบน</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $status =
                                    $row->email_verified_at === null
                                        ? 'Pending'
                                        : ($row->is_banned
                                            ? 'Banned'
                                            : 'Active');
                            @endphp
                            <div class="flex items-center gap-2">
                                {{-- ดูคอร์สของสถาบันนี้ --}}
                                <a href="{{ route('courses.index', ['owner' => $row->id]) }}"
                                   class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-indigo-600 hover:text-white"
                                   title="ดูคอร์สทั้งหมดของสถาบันนี้">
                                    <i class="fa-solid fa-book"></i>
                                </a>
                                {{-- ลิงก์แก้ไข --}}
                                <a href="{{ route('admin.profile-education.edit', $row->id) }}"
                                    class="flex items-center justify-center w-10 h-10 text-gray-700 transition border border-gray-400 rounded-2xl bg-base-100 hover:bg-blue-600 hover:text-white"
                                    title="แก้ไข">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                @if ($status === 'Active')
                                    {{-- แบน --}}
                                    <form method="POST" action="{{ route('admin.educations.toggleBan', $row->id) }}"
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
                                    <form method="POST" action="{{ route('admin.educations.destroy', $row->id) }}"
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
                                    <form method="POST" action="{{ route('admin.educations.toggleBan', $row->id) }}"
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
                                ไม่พบผู้ใช้ที่เป็น <b>สถาบันการศึกษา</b> ที่ตรงกับ
                                “<span class="font-semibold">{{ e(request('q')) }}</span>”
                                <a href="{{ url()->current() }}" class="ml-2 link">ล้างการค้นหา</a>
                            @else
                                ไม่มีผู้ใช้ที่เป็น <b>สถาบันการศึกษา</b> ในระบบ
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
