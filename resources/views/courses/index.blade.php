@extends('layouts.app')

@section('title', 'คอร์สทั้งหมด')

@section('content')
    <div class="w-full p-3 shadow bg-base-200 rounded-2xl" x-data="{
        showEnroll: false,
        selectedCourseId: null,
        selectedCourseName: '',
        routeTpl: '{{ route('courses.enroll', ['id' => 'ID_PLACEHOLDER']) }}',
        openEnroll(id, name) {
            this.selectedCourseId = id;
            this.selectedCourseName = name;
            this.showEnroll = true;
        }
    }">
        <div class="flex items-center justify-between px-4 mb-0">
            <div>
                <h1 class="text-2xl font-bold text-base-content">คอร์สทั้งหมด</h1>
                <form method="GET" class="flex items-center gap-2 mt-2">
                    <input type="text" name="q" value="{{ $q }}" placeholder=" ค้นหาคอร์ส..."
                        class="border-2 border-gray-300 w-96 input">
                    <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
                    <button class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">ค้นหา</button>
                </form>
                <div class="tabs tabs-border" role="tablist">
                    @auth
                        <a href="{{ request()->fullUrlWithQuery(['tab' => 'all', 'page' => 1]) }}"
                            role="tab" class="tab {{ ($tab ?? 'all') === 'all' ? 'tab-active' : '' }}">ทั้งหมด</a>
                        <a href="{{ request()->fullUrlWithQuery(['tab' => 'my', 'page' => 1]) }}"
                            role="tab" class="tab {{ ($tab ?? 'all') === 'my' ? 'tab-active' : '' }}">ที่สมัครแล้ว</a>
                    @endauth
                </div>
            </div>
        </div>


        @if (session('error'))
            <div class="p-3 mb-4 text-red-700 bg-red-100 border border-red-300 rounded">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="p-3 mb-4 text-green-700 bg-green-100 border border-green-300 rounded">{{ session('success') }}</div>
        @endif

        @if ($courses->count() === 0)
            <div class="p-3 text-center bg-white rounded-xl">ไม่พบคอร์ส</div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $course)
                    <div class="flex flex-col p-4 transition transform bg-white shadow rounded-xl ">
                        <a href="{{ route('courses.view', ['id' => $course->c_id]) }}">
                            <img src="{{ $course->c_image ? asset('storage/' . $course->c_image) : asset('image/web-image/ai-robot.jpg') }}"
                                alt="{{ $course->c_name }}" class="object-cover w-full h-40 mb-3 rounded-lg">
                        </a>
                        <h3 class="mb-1 text-lg font-bold text-base-content">
                            <a href="{{ route('courses.view', ['id' => $course->c_id]) }}" class="hover:underline">
                                {{ $course->c_name }}
                            </a>
                        </h3>
                        <div class="flex gap-2 mb-3 text-sm text-gray-600 line-clamp-2">
                            <p>{{ $course->c_name }}</p>
                        </div>

                        <div class="flex items-center justify-between gap-2 mt-auto">
                            @auth
                                @if (auth()->user()->role === 'jobber')
                                    @if (in_array($course->c_id, $enrolledIds))
                                        <span class="badge badge-success">สมัครแล้ว</span>
                                        <a href="{{ route('courses.view', ['id' => $course->c_id]) }}"
                                            class="btn btn-sm">เข้าคอร์ส</a>
                                    @else
                                        <button type="button"
                                            class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                            @click="openEnroll({{ $course->c_id }}, '{{ addslashes($course->c_name) }}')">
                                            สมัครคอร์ส
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ route('courses.view', ['id' => $course->c_id]) }}"
                                        class="btn btn-sm">ดูรายละเอียด</a>
                                @endif
                            @else
                                <button type="button" class="btn btn-sm btn-primary"
                                    @click="$dispatch('open-auth-modal', 'login')">
                                    สมัครคอร์ส
                                </button>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $courses->links() }}</div>

            <!-- Enroll Modal -->
            <template x-if="showEnroll">
                <div class="modal modal-open">
                    <div class="modal-box">
                        <h3 class="mb-2 text-lg font-bold">สมัครเข้าเรียน</h3>
                        <p class="mb-2">คอร์ส: <span class="font-semibold" x-text="selectedCourseName"></span></p>
                        <form x-ref="enrollForm" method="POST"
                            @submit.prevent="$refs.enrollForm.action = routeTpl.replace('ID_PLACEHOLDER', selectedCourseId); $refs.enrollForm.submit();">
                            @csrf
                            <label class="w-full mb-4 form-control">
                                <div class="label"><span class="label-text">กรอกรหัสคอร์ส</span></div>
                                <input type="text" name="code" class="w-full border-2 border-gray-300 input" required
                                    autocomplete="off" placeholder="กรุณากรอกรหัสคอร์ส" />
                            </label>
                            <div class="modal-action">
                                <button type="submit"
                                    class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">ยืนยัน</button>
                                <button type="button" class="btn" @click="showEnroll=false">ยกเลิก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        @endif
    </div>
@endsection
