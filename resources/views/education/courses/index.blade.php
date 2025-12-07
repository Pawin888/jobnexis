@extends('layouts.app')

@section('title', 'คอร์สอบรม')

@section('content')

    <!-- Container ตาราง กำหนด max-width และอยู่ตรงกลาง -->
    <div class="w-full p-6 shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-end pb-4 mb-6 border-b">
            <a href="{{ route('courses.create') }}" 
               class="btn px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-plus mr-2 text-base-100"></i> สร้างคอร์ส
            </a>
        </div>
        <table class="table w-full table-fixed bg-base-200">
            <thead class="bg-base-200">
                <tr>
                    <th style="width:45%">ชื่อคอร์สอบรม</th>
                    <th style="width:20%">ผู้เข้าร่วม</th>
                    <th style="width:20%">สถานะ</th>
                    <th style="width:15%">การทำงาน</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pagedData as $course)
                    <tr class="hover:bg-gray-50 transition">
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ $course['image'] ? asset('storage/'.$course['image']) : asset('image/web-image/ai-robot.jpg') }}" 
                                     alt="Image" class="w-16 h-16 object-cover rounded-lg">
                                <span>{{ $course['title'] }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span>{{ $course['participants'] }}</span>
                                <i class="fa-solid fa-user"></i>
                            </div>
                        </td>
                        <td>
                            @if ($course['status'] === 'เผยแพร่')
                                <span class="px-3 py-1 rounded-full bg-green-200 text-green-600 text-sm">{{ $course['status'] }}</span>
                            @elseif ($course['status'] === 'รออนุมัติ')
                                <span class="px-3 py-1 rounded-full bg-yellow-200 text-yellow-600 text-sm">{{ $course['status'] }}</span>
                            @elseif ($course['status'] === 'ฉบับร่าง')
                                <span class="px-3 py-1 rounded-full bg-gray-300 text-gray-600 text-sm">{{ $course['status'] }}</span>
                            @elseif ($course['status'] === 'ไม่เผยแพร่')
                                <span class="px-3 py-1 rounded-full bg-red-200 text-red-600 text-sm">{{ $course['status'] }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-2">
                                {{-- ปุ่มดูรายละเอียด --}}
                                <a href="{{ route('courses.show', $course['c_id']) }}" 
                                   class="flex items-center justify-center w-10 h-10 border border-gray-400 rounded-2xl bg-base-100 text-gray-700 hover:bg-blue-600 hover:text-white transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                {{-- ปุ่มลบเปิด Modal --}}
                                <label for="delete-modal-{{ $loop->index }}" 
                                    class="flex items-center justify-center w-10 h-10 border border-gray-400 rounded-2xl bg-base-100 text-gray-700 hover:bg-red-600 hover:text-white cursor-pointer transition">
                                    <i class="fa-solid fa-trash"></i>
                                </label>

                                {{-- Modal --}}
                                <input type="checkbox" id="delete-modal-{{ $loop->index }}" class="modal-toggle">
                                <div class="modal">
                                    <div class="modal-box text-center max-w-xs w-full rounded-2xl">
                                        <h3 class="font-bold text-lg">ยืนยันการลบคอร์สอบรมนี้หรือไม่?</h3>
                                        <div class="modal-action justify-center gap-4">

                                            <form action="{{ route('courses.destroy', $course['c_id']) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-3">
                                                    ยืนยัน
                                                </button>
                                            </form>

                                            <label for="delete-modal-{{ $loop->index }}" 
                                                class="hover:bg-gray-200 text-base-content font-normal rounded-lg px-6 py-3">
                                                ยกเลิก
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

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
                @if($start > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}"
                       class="join-item btn btn-sm {{ $current == 1 ? 'btn-active' : '' }}">1</a>
                    @if($start > 2)
                        <span class="join-item btn btn-sm btn-disabled">...</span>
                    @endif
                @endif

                {{-- ปุ่มช่วงกลาง --}}
                @for($i = $start; $i <= $end; $i++)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                       class="join-item btn btn-sm {{ $i == $current ? 'btn-active' : '' }}">
                        {{ $i }}
                    </a>
                @endfor

                {{-- ปุ่มหน้าสุดท้าย --}}
                @if($end < $last)
                    @if($end < $last - 1)
                        <span class="join-item btn btn-sm btn-disabled">...</span>
                    @endif
                    <a href="{{ request()->fullUrlWithQuery(['page' => $last]) }}"
                       class="join-item btn btn-sm {{ $current == $last ? 'btn-active' : '' }}">
                        {{ $last }}
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
