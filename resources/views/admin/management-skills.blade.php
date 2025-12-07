@extends('layouts.app')

@section('title', 'จัดการทักษะ')

@section('content')
<div class="overflow-x-auto border shadow bg-base-200 py-8 rounded-2xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">จัดการทักษะ</h1>
                    <p class="mt-2 text-gray-600">จัดการทักษะที่ใช้ในระบบคอร์สอบรม</p>
                </div>
                <!-- ปุ่มเพิ่มทักษะใหม่ เปิด Modal -->
                <label for="create-modal" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer">
                    <i class="fa-solid fa-plus mr-2"></i>
                    เพิ่มทักษะใหม่
                </label>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fa-solid fa-check-circle text-green-600 mr-3"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fa-solid fa-exclamation-circle text-red-600 mr-3"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Skills Table Card - รวมทุกอย่างเป็นการ์ดเดียว -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Header: Title + Search + Sort ในบล็อคเดียว -->
            <div class="bg-gray-50 p-6">
                <!-- Title Section -->
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">รายการทักษะทั้งหมด</h3>
                    <p class="text-sm text-gray-600 mt-1">จำนวน {{ $skills->total() }} รายการ</p>
                </div>

                <!-- Search and Sort Form -->
                <form method="GET" action="{{ route('management.skills.index') }}" class="space-y-4">
                    <!-- Search + Controls ในบรรทัดเดียว -->
                    <div class="flex flex-col lg:flex-row gap-4 items-end">
                        <!-- Search Box - 40% -->
                        <div class="w-full lg:w-2/5">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fa-solid fa-search mr-2"></i>ค้นหาทักษะ
                            </label>
                            <input type="text" 
                                id="search" 
                                name="search" 
                                value="{{ request('search') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500"
                                placeholder="พิมพ์ชื่อทักษะ">
                        </div>

                        <!-- Sort By - 25% -->
                        <div class="w-full lg:w-1/4">
                            <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fa-solid fa-sort mr-2"></i>เรียงตาม
                            </label>
                            <select id="sort" 
                                    name="sort" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                                <option value="id" {{ request('sort') == 'id' ? 'selected' : '' }}>ID</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>ชื่อทักษะ</option>
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>วันที่สร้าง</option>
                                <option value="courses_count" {{ request('sort') == 'courses_count' ? 'selected' : '' }}>จำนวนคอร์ส</option>
                            </select>
                        </div>
                        
                        <!-- Sort Direction - 20% -->
                        <div class="w-full lg:w-1/5">
                            <label for="direction" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fa-solid fa-arrow-up-down mr-2"></i>ลำดับ
                            </label>
                            <select id="direction" 
                                    name="direction" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>น้อย → มาก</option>
                                <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>มาก → น้อย</option>
                            </select>
                        </div>

                        <!-- Buttons - 15% -->
                        <div class="w-full lg:w-[15%]">
                            <label class="block text-sm font-medium text-gray-700 mb-2 invisible">ปุ่ม</label>
                            <div class="flex gap-2">
                                <button type="submit" 
                                        class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm">
                                    <i class="fa-solid fa-search"></i>
                                </button>
                                
                                <a href="{{ route('management.skills.index') }}" 
                                class="px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors text-sm">
                                    <i class="fa-solid fa-refresh"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- แสดงผลการค้นหา (แยกแถว) -->
                    @if(request()->hasAny(['search', 'sort', 'direction']))
                        <div class="flex items-center text-sm text-gray-600 pt-2 border-t border-gray-200">
                            <i class="fa-solid fa-filter mr-2"></i>
                            @if(request('search'))
                                ค้นหา: "<strong>{{ request('search') }}</strong>"
                            @endif
                            
                            @if(request('sort') && request('sort') != 'id')
                                @if(request('search')) | @endif
                                เรียงตาม: <strong>
                                @switch(request('sort'))
                                    @case('name') ชื่อทักษะ @break
                                    @case('created_at') วันที่สร้าง @break
                                    @case('courses_count') จำนวนคอร์ส @break
                                @endswitch
                                </strong>
                            @endif
                            
                            @if(request('direction') == 'desc')
                                (มาก → น้อย)
                            @endif
                        </div>
                    @endif
                </form>
            </div>

            @if($skills->count() > 0)
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-100 border-t border-gray-200">
                            <tr>
                                <th scope="col" class="w-2/5 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fa-solid fa-tag text-gray-400 mr-2"></i>
                                        ชื่อทักษะ
                                    </div>
                                </th>
                                <th scope="col" class="w-1/5 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fa-solid fa-graduation-cap text-gray-400 mr-2"></i>
                                        จำนวนคอร์สที่ใช้
                                    </div>
                                </th>
                                <th scope="col" class="w-1/5 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fa-solid fa-calendar text-gray-400 mr-2"></i>
                                        วันที่สร้าง
                                    </div>
                                </th>
                                <th scope="col" class="w-1/5 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    การทำงาน
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($skills as $skill)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <!-- Skill Name -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <i class="fa-solid fa-code text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $skill->name }}</div>
                                                <div class="text-xs text-gray-500">ID: {{ $skill->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Course Count -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @php $courseCount = $skill->courses()->count(); @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $courseCount > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $courseCount }} คอร์ส
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Created Date -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ $skill->created_at ? $skill->created_at->format('d/m/Y') : 'ไม่ระบุ' }}
                                        </div>
                                        @if($skill->created_at)
                                            <div class="text-xs text-gray-500">
                                                {{ $skill->created_at->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            {{-- ปุ่มแก้ไข เปิด Modal --}}
                                            <label for="edit-modal-{{ $loop->index }}" 
                                               class="flex items-center justify-center w-10 h-10 border border-gray-400 rounded-2xl bg-base-100 text-gray-700 hover:bg-blue-600 hover:text-white transition cursor-pointer">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </label>

                                            {{-- ปุ่มลบเปิด Modal --}}
                                            <label for="delete-modal-{{ $loop->index }}" 
                                                class="flex items-center justify-center w-10 h-10 border border-gray-400 rounded-2xl bg-base-100 text-gray-700 hover:bg-red-600 hover:text-white cursor-pointer transition">
                                                <i class="fa-solid fa-trash"></i>
                                            </label>

                                            {{-- Edit Modal --}}
                                            <input type="checkbox" id="edit-modal-{{ $loop->index }}" class="modal-toggle">
                                            <div class="modal">
                                                <div class="modal-box max-w-md w-full rounded-2xl">
                                                    <h3 class="font-bold text-lg mb-4">แก้ไขทักษะ</h3>
                                                    <form action="{{ route('management.skills.update', $skill) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        
                                                        <div class="mb-4">
                                                            <label for="edit-name-{{ $loop->index }}" class="block text-sm font-medium text-gray-700 mb-2">
                                                                ชื่อทักษะ
                                                            </label>
                                                            <input type="text" 
                                                                   id="edit-name-{{ $loop->index }}" 
                                                                   name="name" 
                                                                   value="{{ $skill->name }}"
                                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500"
                                                                   required>
                                                        </div>

                                                        <div class="modal-action justify-center gap-4">
                                                            <button type="submit"
                                                                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-3">
                                                                บันทึกการแก้ไข
                                                            </button>
                                                            <label for="edit-modal-{{ $loop->index }}" 
                                                                class="hover:bg-gray-200 text-base-content font-normal rounded-lg px-6 py-3 cursor-pointer">
                                                                ยกเลิก
                                                            </label>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            {{-- Delete Modal --}}
                                            <input type="checkbox" id="delete-modal-{{ $loop->index }}" class="modal-toggle">
                                            <div class="modal">
                                                <div class="modal-box text-center max-w-xs w-full rounded-2xl">
                                                    <h3 class="font-bold text-lg">ยืนยันการลบทักษะ "{{ $skill->name }}" หรือไม่?</h3>
                                                    @if($skill->courses()->count() > 0)
                                                        <p class="text-red-600 text-sm mt-2">
                                                            <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                                                            ทักษะนี้มีการใช้งานใน {{ $skill->courses()->count() }} คอร์ส
                                                        </p>
                                                    @endif
                                                    <div class="modal-action justify-center gap-4">
                                                        <form action="{{ route('management.skills.destroy', $skill) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="bg-red-600 hover:bg-red-700 text-white rounded-lg px-6 py-3">
                                                                ยืนยันลบ
                                                            </button>
                                                        </form>

                                                        <label for="delete-modal-{{ $loop->index }}" 
                                                            class="hover:bg-gray-200 text-base-content font-normal rounded-lg px-6 py-3 cursor-pointer">
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
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12 bg-white">
                    <div class="mx-auto h-24 w-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-tags text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">ยังไม่มีทักษะ</h3>
                    <p class="text-gray-500 mb-6">เริ่มต้นโดยการเพิ่มทักษะแรกของคุณ</p>
                    <label for="create-modal" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 cursor-pointer">
                        <i class="fa-solid fa-plus mr-2"></i>
                        เพิ่มทักษะใหม่
                    </label>
                </div>
            @endif

            <!-- Pagination ใส่ไว้ล่างสุดของการ์ด -->
            @if($skills->hasPages())
                <div class="bg-gray-50 p-6 border-t border-gray-200">
                    {{-- Smart Pagination --}}
                    <div class="flex justify-center">
                        <div class="join">
                            @php
                                $current = $skills->currentPage();
                                $last = $skills->lastPage();
                                $start = max(1, $current - 2);
                                $end = min($last, $current + 2);
                            @endphp

                            {{-- ปุ่มก่อนหน้า --}}
                            @if($skills->onFirstPage())
                                <span class="join-item btn btn-sm btn-disabled">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </span>
                            @else
                                <a href="{{ $skills->previousPageUrl() }}"
                                class="join-item btn btn-sm hover:bg-blue-600 hover:text-white">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </a>
                            @endif

                            {{-- ปุ่มหน้าแรก --}}
                            @if($start > 1)
                                <a href="{{ $skills->url(1) }}"
                                class="join-item btn btn-sm {{ $current == 1 ? 'btn-active bg-blue-600 text-white' : 'hover:bg-blue-600 hover:text-white' }}">
                                    1
                                </a>
                                @if($start > 2)
                                    <span class="join-item btn btn-sm btn-disabled">...</span>
                                @endif
                            @endif

                            {{-- ปุ่มช่วงกลาง --}}
                            @for($i = $start; $i <= $end; $i++)
                                <a href="{{ $skills->url($i) }}"
                                class="join-item btn btn-sm {{ $i == $current ? 'btn-active bg-blue-600 text-white' : 'hover:bg-blue-600 hover:text-white' }}">
                                    {{ $i }}
                                </a>
                            @endfor

                            {{-- ปุ่มหน้าสุดท้าย --}}
                            @if($end < $last)
                                @if($end < $last - 1)
                                    <span class="join-item btn btn-sm btn-disabled">...</span>
                                @endif
                                <a href="{{ $skills->url($last) }}"
                                class="join-item btn btn-sm {{ $current == $last ? 'btn-active bg-blue-600 text-white' : 'hover:bg-blue-600 hover:text-white' }}">
                                    {{ $last }}
                                </a>
                            @endif

                            {{-- ปุ่มถัดไป --}}
                            @if($skills->hasMorePages())
                                <a href="{{ $skills->nextPageUrl() }}"
                                class="join-item btn btn-sm hover:bg-blue-600 hover:text-white">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            @else
                                <span class="join-item btn btn-sm btn-disabled">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- แสดงข้อมูลสถิติ --}}
                    <div class="text-center text-sm text-gray-600 mt-3">
                        แสดง {{ $skills->firstItem() }} - {{ $skills->lastItem() }} จาก {{ $skills->total() }} รายการ
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Modal -->
<input type="checkbox" id="create-modal" class="modal-toggle">
<div class="modal">
    <div class="modal-box max-w-md w-full rounded-2xl">
        <h3 class="font-bold text-lg mb-4">เพิ่มทักษะใหม่</h3>
        <form action="{{ route('management.skills.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="create-name" class="block text-sm font-medium text-gray-700 mb-2">
                    ชื่อทักษะ
                </label>
                <input type="text" 
                       id="create-name" 
                       name="name" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500"
                       placeholder="เช่น PHP, JavaScript, การออกแบบ"
                       required>
            </div>

            <div class="modal-action justify-center gap-4">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-3">
                    บันทึก
                </button>
                <label for="create-modal" 
                    class="hover:bg-gray-200 text-base-content font-normal rounded-lg px-6 py-3 cursor-pointer">
                    ยกเลิก
                </label>
            </div>
        </form>
    </div>
</div>

<script>
// Auto submit เมื่อเปลี่ยน dropdown
document.getElementById('sort').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('direction').addEventListener('change', function() {
    this.form.submit();
});

// Enter key ใน search box
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});
</script>
@endsection