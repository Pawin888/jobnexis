@extends('layouts.app')

@section('title', 'รายละเอียดคอร์สอบรม')

@section('content')
<!-- Header + Tabs -->
<div class="w-full p-6 shadow bg-base-200 rounded-2xl">
    <div class="flex items-center justify-between pb-3 mb-4 border-b">
        <!-- Left: Tabs -->
        <div class="flex items-baseline gap-3">
            <a href="{{ (auth()->check() && in_array(auth()->user()->role, ['education','admin'])) ? route('courses.show', ['id' => $course->c_id]) : route('courses.view', ['id' => $course->c_id]) }}"
               class="text-3xl font-bold text-blue-600 underline cursor-pointer hover:text-blue-600">
                รายละเอียด
            </a>
            @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
                <a href="{{ route('courses.person.show', ['id' => $course->c_id]) }}"
                   class="text-xl cursor-pointer text-base-content hover:text-blue-600">
                    ผู้เรียน
                </a>
            @endif
        </div>

        <!-- Right: Actions by role -->
        <div>
            @if (auth()->check() && auth()->user()->role === 'jobber')
                <div class="flex justify-end" x-data="{ showEnroll: false }">
                    @if ($course->c_status !== 'open')
                        <span class="px-4 py-2 text-sm text-gray-600 bg-gray-200 rounded-lg">คอร์สนี้ยังไม่เปิดรับสมัคร</span>
                    @else
                        @if (!empty($isEnrolled) && $isEnrolled)
                            <form action="{{ route('courses.unenroll', ['id' => $course->c_id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 text-red-600 bg-red-100 rounded-lg hover:bg-red-200">
                                    ยกเลิกการสมัคร
                                </button>
                            </form>
                        @else
                            <button @click="showEnroll = true" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                สมัครเข้าเรียน
                            </button>
                            <template x-if="showEnroll">
                                <div class="modal modal-open">
                                    <div class="modal-box">
                                        <h3 class="mb-2 text-lg font-bold">สมัครเข้าเรียน</h3>
                                        <p class="mb-4">คอร์ส: <span class="font-semibold">{{ $course->c_name }}</span></p>
                                        <form method="POST" action="{{ route('courses.enroll', ['id' => $course->c_id]) }}">
                                            @csrf
                                            <label class="w-full mb-4 form-control">
                                                <div class="label"><span class="label-text">กรอกรหัสคอร์ส</span></div>
                                                <input type="text" name="code" class="w-full input input-bordered" required autocomplete="off" />
                                            </label>
                                            <div class="modal-action">
                                                <button type="button" class="btn" @click="showEnroll=false">ยกเลิก</button>
                                                <button type="submit" class="btn btn-primary">ยืนยัน</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </template>
                        @endif
                    @endif
                </div>
            @endif

            @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
                <a href="{{ route('courses.edit', ['id' => $course->c_id]) }}">
                    <i class="text-xl cursor-pointer fa-solid fa-gear text-base-content hover:text-blue-600"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Course Summary -->
    <div class="w-full rounded-[20px] shadow pt-20 pb-20 px-6 md:px-16 text-white"
         style="background: linear-gradient(to bottom, rgb(125 211 252) 0%, rgb(37 99 235) 100%);">
        <div class="flex flex-col gap-6 p-6 shadow-md bg-base-200 text-base-content rounded-xl md:flex-row">
            <!-- Left: Details -->
            <div class="space-y-4 md:w-1/2">
                <div class="text-4xl font-bold">
                    <span>{{ $course->c_name ?? 'ยังไม่มีข้อมูล' }}</span>
                </div>
                <div>
                    <span>{{ $course->c_description ?? '"เพิ่มคำอธิบายคอร์สอบรมของคุณ"' }}</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @forelse($course->skills->sortBy('name') as $skill)
                        <span class="px-3 py-1 rounded-full text-base-content border border-gray-400 {{ $loop->index % 2 == 0 ? 'bg-blue-200' : 'bg-base-200' }}">
                            {{ $skill->name }}
                        </span>
                    @empty
                        <span class="px-3 py-1 border border-gray-400 rounded-full bg-base-200 text-base-content">
                            ยังไม่มีข้อมูล
                        </span>
                    @endforelse
                </div>
                @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
                    <div class="flex items-center gap-2">
                        <h3>รหัสคอร์สอบรม:</h3>
                        <span>{{ $course->c_code ?? 'ยังไม่มีข้อมูล' }}</span>
                    </div>
                    <p>
                        สถานะคอร์ส:
                        @if ($course->c_status === 'open')
                            <span class="px-2 py-1 text-white bg-green-600 rounded-lg">เปิดรับสมัคร</span>
                        @elseif ($course->c_status === 'closed')
                            <span class="px-2 py-1 text-white bg-red-600 rounded-lg">ปิดรับสมัคร</span>
                        @else
                            <span class="px-2 py-1 text-white bg-gray-600 rounded-lg">ไม่ระบุสถานะ</span>
                        @endif
                    </p>
                @endif
            </div>
            <!-- Right: Image -->
            <div class="flex items-center justify-center md:w-1/2">
                <img src="{{ $course->c_image ? asset('storage/'.$course->c_image) : asset('image/web-image/ai-robot.jpg') }}"
                     alt="Image" class="w-full h-80 rounded-[20px] object-cover">
            </div>
        </div>
    </div>
</div>

@if (session('error'))
    <div class="p-3 mt-4 text-red-700 bg-red-100 border border-red-300 rounded">{{ session('error') }}</div>
@endif
@if (session('success'))
    <div class="p-3 mt-4 text-green-700 bg-green-100 border border-green-300 rounded">{{ session('success') }}</div>
@endif

<!-- Actions + Content List -->
<div class="w-full p-6 mt-6 shadow bg-base-200 rounded-2xl">
    @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
        <div class="flex justify-end gap-4 mb-6">
            <a href="{{ route('medias.create', ['courseId' => $course->c_id]) }}"
               class="flex items-center gap-2 px-4 py-2 text-blue-600 transition border-2 border-blue-600 border-dashed rounded-full hover:bg-blue-100">
                <span class="flex items-center justify-center p-1 text-white bg-blue-600 rounded-full w-7 h-7">
                    <i class="fa-solid fa-file"></i>
                </span>
                <span>สร้างสื่อการสอน</span>
            </a>
            <a href="{{ route('exams.create', ['courseId' => $course->c_id]) }}"
               class="flex items-center gap-2 px-4 py-2 text-blue-600 transition border-2 border-blue-600 border-dashed rounded-full hover:bg-blue-100">
                <span class="flex items-center justify-center p-1 text-white bg-blue-600 rounded-full w-7 h-7">
                    <i class="fa-solid fa-clipboard-question"></i>
                </span>
                <span>สร้างแบบทดสอบ</span>
            </a>
        </div>
    @endif

    <div class="max-w-4xl mx-auto space-y-4">
        <!-- Lessons -->
        @foreach ($lessons as $lesson)
            <div x-data="{ open: false, menuOpen: false }" class="p-4 bg-white border-b-2 border-gray-300 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-semibold">{{ $lesson->l_name }}</span>

                    <div class="flex items-center gap-2">
                        <button @click="open = !open" class="p-2 rounded-full hover:bg-gray-100">
                            <i :class="open ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="transition-transform"></i>
                        </button>

                        @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
                            <div class="relative">
                                <button @click="menuOpen = !menuOpen" class="p-2 rounded-full hover:bg-gray-100">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <div x-show="menuOpen" @click.outside="menuOpen = false" class="absolute right-0 z-50 w-40 mt-2 bg-white border border-gray-300 rounded-lg shadow-lg">
                                    <form action="{{ route('lesson.update', $lesson->l_id) }}" method="POST" class="px-3 py-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="l_name" value="{{ $lesson->l_name }}"
                                               class="w-full px-2 py-1 mb-2 text-sm border border-gray-300 rounded focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100">
                                        <button type="submit" class="w-full py-1 text-sm text-blue-600 rounded hover:bg-blue-200">เปลี่ยนชื่อ</button>
                                    </form>

                                    <label for="delete-lesson-modal-{{ $loop->index }}" class="block w-full py-1 text-sm text-center text-red-600 rounded cursor-pointer hover:bg-red-200">ลบ</label>
                                </div>
                            </div>

                            <!-- Delete Lesson Modal -->
                            <input type="checkbox" id="delete-lesson-modal-{{ $loop->index }}" class="modal-toggle">
                            <div class="modal">
                                <div class="w-full max-w-xs text-center modal-box rounded-2xl">
                                    <h3 class="text-lg font-bold">ยืนยันการลบบทเรียนนี้หรือไม่?</h3>
                                    <div class="justify-center gap-4 modal-action">
                                        <form action="{{ route('lesson.destroyLesson', $lesson->l_id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ยืนยัน</button>
                                        </form>
                                        <label for="delete-lesson-modal-{{ $loop->index }}" class="px-6 py-3 font-normal rounded-lg hover:bg-gray-200 text-base-content">ยกเลิก</label>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Medias under lesson -->
                <div x-show="open" class="pl-6 mt-3 space-y-2">
                    @if ($lesson->medias && $lesson->medias->count() > 0)
                        <span class="font-semibold text-base-content">สื่อการสอน</span>
                        @foreach ($lesson->medias as $media)
                            <div x-data="{ subOpen: false, menuOpen: false }" class="relative p-3 border-l-4 border-blue-500 rounded-lg bg-gray-50">

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="flex items-center justify-center p-1 text-white bg-blue-600 rounded-full w-7 h-7">
                                            <i class="fa-solid fa-file"></i>
                                        </span>
                                        <h4 class="text-base-content">{{ $media->m_name }}</h4>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button @click="subOpen = !subOpen" class="p-2 rounded-full hover:bg-gray-100">
                                            <i :class="subOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="transition-transform"></i>
                                        </button>

                                        @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
                                            <div class="relative">
                                                <button @click="menuOpen = !menuOpen" class="p-2 rounded-full hover:bg-gray-100">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <div x-show="menuOpen" @click.outside="menuOpen = false" class="absolute right-0 z-50 w-32 mt-2 bg-white border border-gray-300 rounded shadow">
                                                    <button type="button" onclick="window.location='{{ route('medias.edit', $media->m_id) }}'" class="w-full py-1 text-sm text-blue-600 rounded hover:bg-blue-200">แก้ไข</button>
                                                    <label for="delete-media-lesson-{{ $lesson->l_id }}-{{ $media->m_id }}" class="block w-full py-1 text-sm text-center text-red-600 rounded cursor-pointer hover:bg-red-200">ลบ</label>
                                                </div>
                                            </div>

                                            <!-- Delete media modal -->
                                            <input type="checkbox" id="delete-media-lesson-{{ $lesson->l_id }}-{{ $media->m_id }}" class="modal-toggle">
                                            <div class="modal">
                                                <div class="w-full max-w-xs text-center modal-box rounded-2xl">
                                                    <h3 class="text-lg font-bold">ยืนยันการลบสื่อการสอนนี้หรือไม่?</h3>
                                                    <div class="justify-center gap-4 modal-action">
                                                        <form action="{{ route('medias.destroy', $media->m_id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ยืนยัน</button>
                                                        </form>
                                                        <label for="delete-media-lesson-{{ $lesson->l_id }}-{{ $media->m_id }}" class="px-6 py-3 font-normal rounded-lg hover:bg-gray-200 text-base-content">ยกเลิก</label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div x-show="subOpen" class="mt-2 space-y-2">
                                    <div class="p-3 rounded-lg bg-gray-50">
                                        @if (!empty($media->m_desc))
                                            <p class="text-sm text-base-content">{{ $media->m_desc }}</p>
                                        @else
                                            <p class="text-sm italic text-gray-400">ยังไม่มีคำอธิบาย</p>
                                        @endif

                                        @if ($media->files && $media->files->count() > 0)
                                            <div class="grid grid-cols-1 gap-3 mt-3 sm:grid-cols-2">
                                                @foreach ($media->files as $file)
                                                    @php
                                                        $ext = strtolower(pathinfo($file->mf_original_name, PATHINFO_EXTENSION));
                                                        $icon = 'fas fa-file text-gray-500';
                                                        $color = 'text-gray-500';
                                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
                                                        $isVideo = in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv', 'flv', '3gp', 'm4v']);

                                                        switch ($ext) {
                                                            case 'pdf': $icon = 'fas fa-file-pdf'; $color = 'text-red-500'; break;
                                                            case 'doc': case 'docx': $icon = 'fas fa-file-word'; $color = 'text-blue-500'; break;
                                                            case 'xls': case 'xlsx': $icon = 'fas fa-file-excel'; $color = 'text-green-600'; break;
                                                            case 'ppt': case 'pptx': $icon = 'fas fa-file-powerpoint'; $color = 'text-orange-500'; break;
                                                            case 'jpg': case 'jpeg': case 'png': case 'gif': case 'svg': $icon = 'fas fa-file-image'; $color = 'text-purple-500'; break;
                                                            case 'mp4': case 'mov': case 'avi': case 'mkv': case 'webm': case 'wmv': case 'flv': case '3gp': case 'm4v': $icon = 'fas fa-file-video'; $color = 'text-pink-500'; break;
                                                        }
                                                        $fileUrl = Storage::disk(config('media.disk','public'))->url($file->mf_path);
                                                    @endphp

                                                    <div class="overflow-hidden transition-shadow duration-200 bg-white border border-gray-300 shadow-sm rounded-xl hover:shadow-md">

                                                        <!-- Header ไฟล์ -->
                                                        <div class="flex items-center justify-between p-3 border-b border-gray-100 bg-gray-50">
                                                            <div class="flex items-center flex-1 min-w-0 gap-2 cursor-pointer"
                                                                onclick="window.open('{{ $fileUrl }}', '_blank')">
                                                                <i class="{{ $icon }} text-lg flex-shrink-0 {{ $color }}"></i>
                                                                <span class="text-sm font-medium text-gray-700 truncate hover:text-blue-600">{{ $file->mf_original_name }}</span>
                                                            </div>
                                                        </div>

                                                        <!-- Preview Area -->
                                                        <div class="p-3">
                                                            @if($isImage)
                                                                <div class="cursor-pointer group" onclick="openImageModal('{{ $fileUrl }}', '{{ $file->mf_original_name }}')">
                                                                    <div class="overflow-hidden bg-gray-100 rounded-lg aspect-video">
                                                                        <img src="{{ $fileUrl }}" class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105" alt="Preview">
                                                                    </div>
                                                                </div>
                                                            @elseif($isVideo)
                                                                <div class="cursor-pointer group">
                                                                    <div class="overflow-hidden bg-gray-900 rounded-lg aspect-video">
                                                                        <video class="object-cover w-full h-full" controls preload="metadata">
                                                                            <source src="{{ $fileUrl }}" type="video/{{ $ext }}">
                                                                            Your browser does not support the video tag.
                                                                        </video>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="py-4 text-center cursor-pointer group" onclick="window.open('{{ $fileUrl }}', '_blank')">
                                                                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-2 transition-colors bg-gray-100 rounded-full group-hover:bg-gray-200">
                                                                        <i class="{{ $icon }} text-2xl {{ $color }}"></i>
                                                                    </div>
                                                                    <p class="text-xs text-gray-500 group-hover:text-blue-600">คลิกเพื่อเปิดไฟล์</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    {{-- แบบทดสอบในบทเรียน - แก้ไขชื่อฟิลด์ --}}
                    @if ($lesson->exams && $lesson->exams->count() > 0)
                        <span class="font-semibold text-base-content">แบบทดสอบ</span>
                        @foreach ($lesson->exams as $exam)
                            <div x-data="{ subOpen: false, menuOpen: false }" class="relative p-3 border-l-4 border-blue-500 rounded-lg bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="flex items-center justify-center p-1 text-white bg-blue-600 rounded-full w-7 h-7">
                                            <i class="fa-solid fa-clipboard-question"></i>
                                        </span>
                                        <h4 class="text-base-content">{{ $exam->e_name }}</h4>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button @click="subOpen = !subOpen" class="p-2 rounded-full hover:bg-gray-100">
                                            <i :class="subOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="transition-transform"></i>
                                        </button>

                                        @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
                                            <div class="relative">
                                                <button @click="menuOpen = !menuOpen" class="p-2 rounded-full hover:bg-gray-100">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <div x-show="menuOpen" @click.outside="menuOpen = false" class="absolute right-0 z-50 w-32 mt-2 bg-white border border-gray-300 rounded shadow">
                                                    <button type="button" onclick="window.location='{{ route('exams.edit', $exam->e_id) }}'" class="w-full py-1 text-sm text-blue-600 rounded hover:bg-blue-200">แก้ไข</button>
                                                    <label for="delete-exam-lesson-{{ $lesson->l_id }}-{{ $exam->e_id }}" class="block w-full py-1 text-sm text-center text-red-600 rounded cursor-pointer hover:bg-red-200">ลบ</label>
                                                </div>
                                            </div>

                                            <!-- Delete exam modal -->
                                            <input type="checkbox" id="delete-exam-lesson-{{ $lesson->l_id }}-{{ $exam->e_id }}" class="modal-toggle">
                                            <div class="modal">
                                                <div class="w-full max-w-xs text-center modal-box rounded-2xl">
                                                    <h3 class="text-lg font-bold">ยืนยันการลบแบบทดสอบนี้หรือไม่?</h3>
                                                    <div class="justify-center gap-4 modal-action">
                                                        <form action="{{ route('exams.destroy', $exam->e_id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ยืนยัน</button>
                                                        </form>
                                                        <label for="delete-exam-lesson-{{ $lesson->l_id }}-{{ $exam->e_id }}" class="px-6 py-3 font-normal rounded-lg hover:bg-gray-200 text-base-content">ยกเลิก</label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div x-show="subOpen" class="mt-2 space-y-2">
                                    <div class="p-3 rounded-lg bg-gray-50">
                                        @if (!empty($exam->e_description))
                                            <p class="mb-3 text-sm text-base-content">{{ $exam->e_description }}</p>
                                        @else
                                            <p class="mb-3 text-sm italic text-gray-400">ยังไม่มีคำอธิบาย</p>
                                        @endif

                                        <!-- สรุปข้อมูลแบบทดสอบ -->
                                        <div class="flex flex-wrap gap-2 mb-4 text-xs text-gray-600">
                                            <span class="px-3 py-1 bg-green-100 rounded-full">
                                                <i class="mr-1 fa-solid fa-question-circle"></i>
                                                {{ $exam->questions->count() }} ข้อ
                                            </span>
                                        </div>

                                        <!-- ปุ่มดำเนินการ -->
                                        <div class="flex flex-wrap justify-center gap-3">
                                            @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
                                                <!-- ปุ่มจัดการคำถาม -->
                                                <a href="{{ route('questions.manage', $exam->e_id) }}"
                                                class="px-4 py-2 text-sm text-white transition-colors bg-purple-600 rounded-lg hover:bg-purple-700">
                                                    <i class="mr-2 fa-solid fa-list-check"></i>
                                                    จัดการคำถาม
                                                </a>
                                            @endif

                                            @if (auth()->check() && auth()->user()->role === 'jobber')
                                                <a href="{{ route('exams.take', $exam->e_id) }}"
                                                class="px-4 py-2 text-sm text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                                                    <i class="mr-2 fa-solid fa-play"></i>
                                                    เริ่มทำแบบทดสอบ
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Solo medias (no lesson) -->
        @foreach ($soloMedias as $media)
            <div x-data="{ subOpen: false, menuOpen: false }" class="relative p-4 bg-white border-b-2 border-gray-300 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center p-1 text-white bg-blue-600 rounded-full w-7 h-7">
                            <i class="fa-solid fa-file"></i>
                        </span>
                        <h4 class="text-base-content">{{ $media->m_name }}</h4>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="subOpen = !subOpen" class="p-2 rounded-full hover:bg-gray-100">
                            <i :class="subOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="transition-transform"></i>
                        </button>

                        @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
                            <div class="relative">
                                <button @click="menuOpen = !menuOpen" class="p-2 rounded-full hover:bg-gray-100">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <div x-show="menuOpen" @click.outside="menuOpen = false" class="absolute right-0 z-50 w-32 mt-2 bg-white border border-gray-300 rounded shadow">
                                    <button type="button" onclick="window.location='{{ route('medias.edit', $media->m_id) }}'" class="w-full py-1 text-sm text-blue-600 rounded hover:bg-blue-200">แก้ไข</button>
                                    <label for="delete-mediaOut-modal-{{ $media->m_id }}" class="block w-full py-1 text-sm text-center text-red-600 rounded cursor-pointer hover:bg-red-200">ลบ</label>
                                </div>
                            </div>

                            <!-- Delete solo media modal -->
                            <input type="checkbox" id="delete-mediaOut-modal-{{ $media->m_id }}" class="modal-toggle">
                            <div class="modal">
                                <div class="w-full max-w-xs text-center modal-box rounded-2xl">
                                    <h3 class="text-lg font-bold">ยืนยันการลบสื่อนี้หรือไม่?</h3>
                                    <div class="justify-center gap-4 modal-action">
                                        <form action="{{ route('medias.destroy', $media->m_id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ยืนยัน</button>
                                        </form>
                                        <label for="delete-mediaOut-modal-{{ $media->m_id }}" class="px-6 py-3 font-normal rounded-lg hover:bg-gray-200 text-base-content">ยกเลิก</label>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div x-show="subOpen" class="mt-2 space-y-2">
                    <div class="p-3 border-l-4 border-blue-500 rounded-lg bg-gray-50">
                        @if (!empty($media->m_desc))
                            <p class="text-sm text-base-content">{{ $media->m_desc }}</p>
                        @else
                            <p class="text-sm italic text-gray-400">ยังไม่มีคำอธิบาย</p>
                        @endif

                        @if ($media->files && $media->files->count() > 0)
                            <div class="grid grid-cols-1 gap-3 mt-3 sm:grid-cols-2">
                                @foreach ($media->files as $file)
                                    @php
                                        $ext = strtolower(pathinfo($file->mf_original_name, PATHINFO_EXTENSION));
                                        $icon = 'fas fa-file text-gray-500';
                                        $color = 'text-gray-500';
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
                                        $isVideo = in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv', 'flv', '3gp', 'm4v']);

                                        switch ($ext) {
                                            case 'pdf': $icon = 'fas fa-file-pdf'; $color = 'text-red-500'; break;
                                            case 'doc': case 'docx': $icon = 'fas fa-file-word'; $color = 'text-blue-500'; break;
                                            case 'xls': case 'xlsx': $icon = 'fas fa-file-excel'; $color = 'text-green-600'; break;
                                            case 'ppt': case 'pptx': $icon = 'fas fa-file-powerpoint'; $color = 'text-orange-500'; break;
                                            case 'jpg': case 'jpeg': case 'png': case 'gif': case 'svg': $icon = 'fas fa-file-image'; $color = 'text-purple-500'; break;
                                            case 'mp4': case 'mov': case 'avi': case 'mkv': case 'webm': case 'wmv': case 'flv': case '3gp': case 'm4v': $icon = 'fas fa-file-video'; $color = 'text-pink-500'; break;
                                        }
                                        $fileUrl = Storage::disk(config('media.disk','public'))->url($file->mf_path);
                                    @endphp

                                    <div class="overflow-hidden transition-shadow duration-200 bg-white border border-gray-300 shadow-sm rounded-xl hover:shadow-md">

                                        <!-- Header ไฟล์ -->
                                        <div class="flex items-center justify-between p-3 border-b border-gray-100 bg-gray-50">
                                            <div class="flex items-center flex-1 min-w-0 gap-2 cursor-pointer"
                                                onclick="window.open('{{ $fileUrl }}', '_blank')">
                                                <i class="{{ $icon }} text-lg flex-shrink-0 {{ $color }}"></i>
                                                <span class="text-sm font-medium text-gray-700 truncate hover:text-blue-600">{{ $file->mf_original_name }}</span>
                                            </div>
                                        </div>

                                        <!-- Preview Area -->
                                        <div class="p-3">
                                            @if($isImage)
                                                <div class="cursor-pointer group" onclick="openImageModal('{{ $fileUrl }}', '{{ $file->mf_original_name }}')">
                                                    <div class="overflow-hidden bg-gray-100 rounded-lg aspect-video">
                                                        <img src="{{ $fileUrl }}" class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105" alt="Preview">
                                                    </div>
                                                </div>
                                            @elseif($isVideo)
                                                <div class="cursor-pointer group">
                                                    <div class="overflow-hidden bg-gray-900 rounded-lg aspect-video">
                                                        <video class="object-cover w-full h-full" controls preload="metadata">
                                                            <source src="{{ $fileUrl }}" type="video/{{ $ext }}">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="py-4 text-center cursor-pointer group" onclick="window.open('{{ $fileUrl }}', '_blank')">
                                                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-2 transition-colors bg-gray-100 rounded-full group-hover:bg-gray-200">
                                                        <i class="{{ $icon }} text-2xl {{ $color }}"></i>
                                                    </div>
                                                    <p class="text-xs text-gray-500 group-hover:text-blue-600">คลิกเพื่อเปิดไฟล์</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Solo exams (no lesson) -->
        @foreach ($soloExams as $exam)
            <div x-data="{ subOpen: false, menuOpen: false }" class="relative p-4 bg-white border-b-2 border-gray-300 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center p-1 text-white bg-blue-600 rounded-full w-7 h-7">
                            <i class="fa-solid fa-clipboard-question"></i>
                        </span>
                        <h4 class="text-base-content">{{ $exam->e_name }}</h4>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="subOpen = !subOpen" class="p-2 rounded-full hover:bg-gray-100">
                            <i :class="subOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="transition-transform"></i>
                        </button>

                        @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
                            <div class="relative">
                                <button @click="menuOpen = !menuOpen" class="p-2 rounded-full hover:bg-gray-100">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <div x-show="menuOpen" @click.outside="menuOpen = false" class="absolute right-0 z-50 w-32 mt-2 bg-white border border-gray-300 rounded shadow">
                                    <button type="button" onclick="window.location='{{ route('exams.edit', $exam->e_id) }}'" class="w-full py-1 text-sm text-blue-600 rounded hover:bg-blue-200">แก้ไข</button>
                                    <label for="delete-examOut-modal-{{ $exam->e_id }}" class="block w-full py-1 text-sm text-center text-red-600 rounded cursor-pointer hover:bg-red-200">ลบ</label>
                                </div>
                            </div>

                            <!-- Delete solo exam modal -->
                            <input type="checkbox" id="delete-examOut-modal-{{ $exam->e_id }}" class="modal-toggle">
                            <div class="modal">
                                <div class="w-full max-w-xs text-center modal-box rounded-2xl">
                                    <h3 class="text-lg font-bold">ยืนยันการลบแบบทดสอบนี้หรือไม่?</h3>
                                    <div class="justify-center gap-4 modal-action">
                                        <form action="{{ route('exams.destroy', $exam->e_id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ยืนยัน</button>
                                        </form>
                                        <label for="delete-examOut-modal-{{ $exam->e_id }}" class="px-6 py-3 font-normal rounded-lg hover:bg-gray-200 text-base-content">ยกเลิก</label>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div x-show="subOpen" class="mt-2 space-y-2">
                    <div class="p-3 border-l-4 border-blue-500 rounded-lg bg-gray-50">
                        @if (!empty($exam->e_description))
                            <p class="mb-3 text-sm text-base-content">{{ $exam->e_description }}</p>
                        @else
                            <p class="mb-3 text-sm italic text-gray-400">ยังไม่มีคำอธิบาย</p>
                        @endif

                        <!-- สรุปข้อมูลแบบทดสอบ -->
                        <div class="flex flex-wrap gap-2 mb-4 text-xs text-gray-600">
                            <span class="px-3 py-1 bg-green-100 rounded-full">
                                <i class="mr-1 fa-solid fa-question-circle"></i>
                                {{ $exam->questions->count() }} ข้อ
                            </span>
                            @if ($exam->questions->count() > 0)
                                <span class="px-3 py-1 bg-blue-100 rounded-full">
                                    <i class="mr-1 fa-solid fa-clock"></i>
                                    สร้างแล้ว
                                </span>
                            @endif
                        </div>

                        <!-- ปุ่มดำเนินการ -->
                        <div class="flex flex-wrap justify-center gap-3">
                            @if (auth()->check() && in_array(auth()->user()->role, ['education','admin']))
                                <!-- ปุ่มจัดการคำถาม -->
                                <a href="{{ route('questions.manage', $exam->e_id) }}"
                                class="px-4 py-2 text-sm text-white transition-colors bg-purple-600 rounded-lg hover:bg-purple-700">
                                    <i class="mr-2 fa-solid fa-list-check"></i>
                                    จัดการคำถาม
                                </a>
                            @endif

                            @if (auth()->check() && auth()->user()->role === 'jobber')
                                <a href="{{ route('exams.take', $exam->e_id) }}"
                                class="px-4 py-2 text-sm text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                                    <i class="mr-2 fa-solid fa-play"></i>
                                    เริ่มทำแบบทดสอบ
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if (($lessons->count() ?? 0) === 0 && ($soloMedias->count() ?? 0) === 0 && ($soloExams->count() ?? 0) === 0)
            <div class="p-4 text-center text-gray-500 bg-white rounded-xl">ยังไม่มีเนื้อหาในคอร์สนี้</div>
        @endif
    </div>
</div>

<script>
// ฟังก์ชันสำหรับแสดง modal รูปภาพ (ใช้เมื่ออยากแสดงรูปแบบเต็มหน้าจอ)
function openImageModal(src, title) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="relative max-w-4xl max-h-full p-4">
            <img src="${src}" alt="${title}" class="object-contain max-w-full max-h-full rounded">
            <button onclick="this.closest('.fixed').remove()"
                    class="absolute z-10 flex items-center justify-center w-8 h-8 text-black bg-white rounded-full top-2 right-2 hover:bg-gray-200">
                ×
            </button>
            <p class="absolute mt-2 text-center text-white transform -translate-x-1/2 bottom-2 left-1/2">${title}</p>
        </div>
    `;
    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
    document.body.appendChild(modal);
}
</script>


@endsection
