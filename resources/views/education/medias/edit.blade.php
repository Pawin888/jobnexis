@extends('layouts.app')

@section('title', 'แก้ไขสื่อการสอน')

@section('content')
<div class="bg-base-200 p-6 rounded-lg shadow max-w-2xl mx-auto">

    <h2 class="text-2xl font-semibold mb-6 text-center">แก้ไขสื่อการสอน</h2>

    {{-- แสดงข้อความสำเร็จ --}}
    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 mb-6 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('medias.update', $media->m_id) }}"
          method="POST"
          enctype="multipart/form-data"
          class="flex flex-col gap-6"
          x-data="mediaForm()"
          @submit.prevent="validateForm()">

        @csrf
        @method('PUT')
        <input type="hidden" name="course_id" value="{{ $courseId }}">

        {{-- ======================== --}}
        {{-- เลือกบทเรียน --}}
        {{-- ======================== --}}
        <div class="flex flex-col gap-1">
            <label class="block font-medium">บทเรียน</label>

            {{-- รายการบทเรียน realtime --}}
            <div class="lessons-list mb-2">
                @if($media->lesson)
                    <div class="lesson-item flex items-center justify-between p-2 border rounded mb-1"
                         data-id="{{ $media->lesson->l_id }}">
                        <div class="flex items-center gap-2">
                            <span class="lesson-name">{{ $media->lesson->l_name }}</span>
                            <button type="button"
                                    class="text-red-500 hover:text-red-700 font-bold remove-from-list">&times;</button>
                        </div>
                        <div class="flex gap-2">
                            <i class="fa-solid fa-pen text-blue-600 cursor-pointer edit-btn"
                               data-value="{{ $media->lesson->l_id }}"></i>
                            <i class="fa-solid fa-trash text-red-600 cursor-pointer delete-btn"
                               data-value="{{ $media->lesson->l_id }}"></i>
                        </div>
                    </div>
                @else
                    <div class="lesson-placeholder text-sm text-gray-400">
                        ยังไม่ได้เลือกบทเรียน
                    </div>
                @endif
            </div>

            {{-- Dropdown เลือกบทเรียน --}}
            <select id="lesson_select" name="m_l_id">
                <option value="">-- เพิ่ม/เลือกบทเรียน --</option>
                @foreach($lessons as $lesson)
                    <option value="{{ $lesson->l_id }}"
                            {{ $media->m_l_id == $lesson->l_id ? 'selected' : '' }}>
                        {{ $lesson->l_name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ======================== --}}
        {{-- ชื่อสื่อการสอน --}}
        {{-- ======================== --}}
        <div class="flex flex-col gap-1 relative">
            <label class="block text-base-content mb-1">ชื่อสื่อการสอน</label>
            <div class="relative">
                <input type="text"
                       name="m_name"
                       x-model="mediaName"
                       maxlength="50"
                       value="{{ $media->m_name }}"
                       required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10
                              focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100">
                <span class="absolute bottom-2 right-3 text-sm text-gray-500 pointer-events-none"
                      x-text="`${mediaName.length} / 50`"></span>
            </div>
            <span class="text-red-600 text-sm mt-1"
                  x-show="mediaNameError"
                  x-cloak>
                กรุณากรอกชื่อสื่อการสอน
            </span>
        </div>

        {{-- ======================== --}}
        {{-- คำอธิบาย --}}
        {{-- ======================== --}}
        <div class="flex flex-col gap-1 relative">
            <label class="block text-base-content mb-1">คำอธิบายสื่อการสอน</label>
            <textarea name="m_desc"
                      x-model="mediaDesc"
                      rows="4"
                      maxlength="200"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 pr-14
                             focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100 resize-none">{{ $media->m_desc }}</textarea>
            <span class="absolute bottom-2 right-3 text-sm text-gray-500"
                  x-text="`${mediaDesc.length} / 200`"></span>
        </div>

        {{-- ======================== --}}
        {{-- ไฟล์แนบ --}}
        {{-- ======================== --}}
        <div class="flex flex-col gap-1">
            <label class="block text-base-content mb-1">ไฟล์แนบ</label>

            {{-- กล่องอัปโหลด --}}
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500"
                 @click="$refs.fileInput.click()"
                 @dragover.prevent
                 @drop.prevent="handleDrop($event)">
                <p class="text-gray-600">
                    ลากไฟล์มาวางที่นี่ หรือ
                    <span class="text-blue-600 underline">เลือกไฟล์</span>
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    รองรับหลายไฟล์ (PDF, DOCX, PNG, JPG, MP4, MOV, AVI และอื่นๆ)
                </p>
            </div>

            {{-- input file จริง --}}
            <input type="file"
                   name="files[]"
                   multiple
                   class="hidden"
                   x-ref="fileInput"
                   @change="handleFiles($event)">

            <!-- แสดงไฟล์ทั้งหมด (เก่า + ใหม่) -->
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- ไฟล์เดิม -->
                @foreach($media->files as $file)
                    {{-- ลบ x-data และ x-show เพื่อหลีกเลี่ยง scope conflict --}}
                    <div class="bg-white border border-gray-300 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200"
                        id="existing-file-{{ $file->mf_id }}">

                        @php
                            $ext = strtolower(pathinfo($file->mf_original_name, PATHINFO_EXTENSION));
                            $icon = 'fas fa-file text-gray-500';
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
                            $isVideo = in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv', 'flv', '3gp', 'm4v']);
                            
                            switch($ext) {
                                case 'pdf': $icon = 'fas fa-file-pdf text-red-500'; break;
                                case 'doc':
                                case 'docx': $icon = 'fas fa-file-word text-blue-500'; break;
                                case 'xls':
                                case 'xlsx': $icon = 'fas fa-file-excel text-green-600'; break;
                                case 'ppt':
                                case 'pptx': $icon = 'fas fa-file-powerpoint text-orange-500'; break;
                                case 'jpg':
                                case 'jpeg':
                                case 'png':
                                case 'gif':
                                case 'svg': $icon = 'fas fa-file-image text-purple-500'; break;
                                case 'mp4':
                                case 'mov':
                                case 'avi':
                                case 'mkv':
                                case 'webm':
                                case 'wmv':
                                case 'flv':
                                case '3gp':
                                case 'm4v': $icon = 'fas fa-file-video text-pink-500'; break;
                            }
                        @endphp
                        @php $fileUrl = Storage::disk(config('media.disk','public'))->url($file->mf_path); @endphp
                        
                        <!-- Header ไฟล์ -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 border-b border-gray-100">
                            <div class="flex items-center gap-2 flex-1 min-w-0 cursor-pointer" 
                                onclick="window.open('{{ $fileUrl }}', '_blank')">
                                <i class="{{ $icon }} text-lg flex-shrink-0"></i>
                                <span class="truncate text-sm font-medium text-gray-700 hover:text-blue-600">{{ $file->mf_original_name }}</span>
                            </div>
                            {{-- แก้ไขปุ่มลบให้เรียกฟังก์ชันโดยตรงและซ่อนไฟล์ --}}
                            <button type="button"
                                    @click.stop="markFileForDeletion({{ $file->mf_id }}); document.getElementById('existing-file-{{ $file->mf_id }}').style.display = 'none'"
                                    class="ml-2 w-6 h-6 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center text-sm font-bold transition-colors">
                                ×
                            </button>
                        </div>

                        <!-- Preview Area -->
                        <div class="p-3">
                            @if($isImage)
                                <div class="cursor-pointer group" onclick="openImageModal('{{ $fileUrl }}', '{{ $file->mf_original_name }}')">
                                    <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="{{ $fileUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Preview">
                                    </div>
                                </div>
                            @elseif($isVideo)
                                <div class="cursor-pointer group">
                                    <div class="aspect-video bg-gray-900 rounded-lg overflow-hidden">
                                        <video class="w-full h-full object-cover" controls preload="metadata">
                                            <source src="{{ $fileUrl }}" type="video/{{ $ext }}">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                </div>
                            @else
                                <div class="py-4 text-center cursor-pointer group" onclick="window.open('{{ $fileUrl }}', '_blank')">
                                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-2 group-hover:bg-gray-200 transition-colors">
                                        <i class="{{ $icon }} text-2xl"></i>
                                    </div>
                                    <p class="text-xs text-gray-500 group-hover:text-blue-600">คลิกเพื่อเปิดไฟล์</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <!-- ไฟล์ใหม่ -->
                <template x-for="(file, index) in files" :key="index">
                    <div class="bg-white border border-gray-300 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                        
                        <!-- Header ไฟล์ -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 border-b border-gray-100">
                            <div class="flex items-center gap-2 flex-1 min-w-0 cursor-pointer" 
                                @click="openFile(file)">
                                <i :class="getFileIcon(file.name) + ' text-lg flex-shrink-0'"></i>
                                <span x-text="file.name" class="truncate text-sm font-medium text-gray-700 hover:text-blue-600"></span>
                            </div>
                            <button type="button"
                                    @click.stop="removeFile(index)"
                                    class="ml-2 w-6 h-6 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center text-sm font-bold transition-colors">
                                ×
                            </button>
                        </div>

                        <!-- Preview Area -->
                        <div class="p-3">
                            <!-- Image preview -->
                            <div x-show="isImage(file.name)" @click="openFile(file)" class="cursor-pointer group">
                                <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                    <img :src="URL.createObjectURL(file)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Preview">
                                </div>
                            </div>
                            
                            <!-- Video preview -->
                            <div x-show="isVideo(file.name)" class="cursor-pointer group">
                                <div class="aspect-video bg-gray-900 rounded-lg overflow-hidden">
                                    <video class="w-full h-full object-cover" controls preload="metadata">
                                        <source :src="URL.createObjectURL(file)" :type="file.type">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>

                            <!-- Document files -->
                            <div x-show="!isImage(file.name) && !isVideo(file.name)" @click="openFile(file)" class="py-4 text-center cursor-pointer group">
                                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-2 group-hover:bg-gray-200 transition-colors">
                                    <i :class="getFileIcon(file.name) + ' text-2xl'"></i>
                                </div>
                                <p class="text-xs text-gray-500 group-hover:text-blue-600">คลิกเพื่อเปิดไฟล์</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ======================== --}}
        {{-- ปุ่ม Action --}}
        {{-- ======================== --}}
        <div class="col-span-1 md:col-span-2 flex justify-center gap-4 mt-10">
            <button type="submit"
                    class="btn bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-3">
                บันทึก
            </button>
            <a href="{{ route('courses.show', ['id' => $courseId]) }}"
               class="btn btn-outline text-base-content rounded-lg px-6 py-3">
                ยกเลิก
            </a>
        </div>

        {{-- Hidden container สำหรับไฟล์ที่ถูกลบ - อยู่ในฟอร์ม --}}
        <div id="deleted-files-container" style="display: none;"></div>
    </form>
</div>

<script>
let lessonPending = false

function mediaForm() {
    return {
        mediaName: '{{ $media->m_name }}',
        mediaDesc: '{{ $media->m_desc }}',
        mediaNameError: false,
        files: [],
        deletedFileIds: [], // เก็บไฟล์ที่ถูกลบ

        init() {
            this.mediaName = '{{ $media->m_name }}';
            this.mediaDesc = '{{ $media->m_desc }}';
            console.log('🔧 MediaForm initialized');
            console.log('🔧 Initial deletedFileIds:', this.deletedFileIds);
        },

        markFileForDeletion(fileId) {
            console.log('🚀 markFileForDeletion called with fileId:', fileId);
            console.log('🚀 deletedFileIds before:', JSON.stringify(this.deletedFileIds));
            
            if (!this.deletedFileIds.includes(fileId)) {
                this.deletedFileIds.push(fileId);
                console.log('🚀 File added to deletion list');
            } else {
                console.log('🚀 File already in deletion list');
            }
            
            console.log('🚀 deletedFileIds after:', JSON.stringify(this.deletedFileIds));
            console.log('🚀 Total files marked for deletion:', this.deletedFileIds.length);
        },

        validateForm() {
            console.log('🔥 === FORM VALIDATION START ===');
            console.log('🔥 Deleted file IDs:', JSON.stringify(this.deletedFileIds));
            console.log('🔥 Number of files to delete:', this.deletedFileIds.length);

            // ค้นหา container
            let container = document.getElementById('deleted-files-container');
            console.log('🔥 Container found:', !!container);
            console.log('🔥 Container is in form:', !!container?.closest('form'));
            
            if (!container) {
                console.log('🔥 Creating new container...');
                container = document.createElement('div');
                container.id = 'deleted-files-container';
                container.style.display = 'none';
                this.$el.appendChild(container);
                console.log('🔥 New container created');
            }
            
            // เคลียร์และสร้าง hidden inputs
            container.innerHTML = '';
            console.log('🔥 Container cleared');
            
            if (this.deletedFileIds.length > 0) {
                console.log('🔥 Creating hidden inputs...');
                this.deletedFileIds.forEach((fileId, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_files[]';
                    input.value = fileId;
                    input.id = `delete-file-${fileId}`;
                    container.appendChild(input);
                    console.log(`🔥 Hidden input ${index + 1}:`, {
                        name: input.name,
                        value: input.value,
                        id: input.id
                    });
                });
            } else {
                console.log('🔥 No files to delete');
            }
            
            // ตรวจสอบ hidden inputs ที่สร้างแล้ว
            const hiddenInputs = this.$el.querySelectorAll('input[name="delete_files[]"]');
            console.log('🔥 Total hidden inputs in form:', hiddenInputs.length);
            console.log('🔥 Hidden input values:', Array.from(hiddenInputs).map(inp => inp.value));
            
            // ตรวจสอบ FormData ที่จะส่ง
            const formData = new FormData(this.$el);
            const deleteFilesFromForm = formData.getAll('delete_files[]');
            console.log('🔥 delete_files[] in FormData:', deleteFilesFromForm);
            
            if (lessonPending) {
                alert("กรุณารอสักครู่ กำลังสร้างบทเรียนใหม่...");
                return;
            }

            this.mediaNameError = this.mediaName.trim() === '';
            if (this.mediaNameError) {
                console.log('🔥 Media name is empty');
                return;
            }

            const select = document.querySelector("#lesson_select");
            const value = select.value;
            if (value && isNaN(parseInt(value))) {
                alert("กรุณารอระบบสร้างบทเรียนใหม่ให้เสร็จ");
                return;
            }

            console.log('🔥 === SUBMITTING FORM ===');
            console.log('🔥 Final check - files to delete:', this.deletedFileIds);
            
            this.$el.submit();
        },

        openImageModal(src, title) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="max-w-4xl max-h-full p-4 relative">
                    <img src="${src}" alt="${title}" class="max-w-full max-h-full object-contain rounded">
                    <button onclick="this.closest('.fixed').remove()" 
                            class="absolute top-2 right-2 bg-white text-black rounded-full w-8 h-8 flex items-center justify-center hover:bg-gray-200 z-10">
                        ×
                    </button>
                    <p class="text-white text-center mt-2 absolute bottom-2 left-1/2 transform -translate-x-1/2">${title}</p>
                </div>
            `;
            modal.onclick = (e) => { if(e.target === modal) modal.remove(); };
            document.body.appendChild(modal);
        },

        openFile(file) {
            if (this.isImage(file.name)) {
                this.openImageModal(URL.createObjectURL(file), file.name);
            } else {
                const fileUrl = URL.createObjectURL(file);
                window.open(fileUrl, '_blank');
            }
        },

        validateFileType(file) {
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
                'image/webp',
                'video/mp4',
                'video/quicktime',
                'video/x-msvideo',
                'video/x-ms-wmv',
                'video/webm',
                'video/x-flv',
                'video/3gpp',
                'video/mp4v-es'
            ];
            
            return allowedTypes.includes(file.type);
        },

        getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            switch(ext) {
                case 'pdf': return 'fas fa-file-pdf text-red-500';
                case 'doc':
                case 'docx': return 'fas fa-file-word text-blue-500';
                case 'xls':
                case 'xlsx': return 'fas fa-file-excel text-green-600';
                case 'ppt':
                case 'pptx': return 'fas fa-file-powerpoint text-orange-500';
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                case 'svg': return 'fas fa-file-image text-purple-500';
                case 'mp4':
                case 'mov':
                case 'mkv':
                case 'webm':
                case 'wmv':
                case 'flv':
                case '3gp':
                case 'm4v':
                case 'avi': return 'fas fa-file-video text-pink-500';
                default: return 'fas fa-file text-gray-500';
            }
        },

        isVideo(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            return ['mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv', 'flv', '3gp', 'm4v'].includes(ext);
        },

        isImage(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            return ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext);
        },

        handleFiles(event) {
            const MAX_MB = {{ (int) config('media.max_upload_mb') }};
            const MAX_BYTES = MAX_MB * 1024 * 1024;
            const newFiles = Array.from(event.target.files).filter(file => {
                if (!this.validateFileType(file)) {
                    alert(`ไฟล์ ${file.name} ไม่รองรับ กรุณาเลือกไฟล์ประเภท PDF, DOCX, รูปภาพ หรือวีดีโอ`);
                    return false;
                }
                if (file.size > MAX_BYTES) {
                    alert(`ไฟล์ ${file.name} มีขนาดใหญ่เกินไป (สูงสุด ${MAX_MB}MB)`);
                    return false;
                }
                return true;
            });
            
            this.files = [...this.files, ...newFiles];
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
        },

        handleDrop(event) {
            const MAX_MB = {{ (int) config('media.max_upload_mb') }};
            const MAX_BYTES = MAX_MB * 1024 * 1024;
            const droppedFiles = Array.from(event.dataTransfer.files).filter(file => {
                if (!this.validateFileType(file)) {
                    alert(`ไฟล์ ${file.name} ไม่รองรับ กรุณาเลือกไฟล์ประเภท PDF, DOCX, รูปภาพ หรือวีดีโอ`);
                    return false;
                }
                if (file.size > MAX_BYTES) {
                    alert(`ไฟล์ ${file.name} มีขนาดใหญ่เกินไป (สูงสุด ${MAX_MB}MB)`);
                    return false;
                }
                return true;
            });
            
            this.files = [...this.files, ...droppedFiles];
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
        },

        removeFile(index) {
            this.files.splice(index, 1);
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
        }
    }
}

function openImageModal(src, title) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="max-w-4xl max-h-full p-4 relative">
            <img src="${src}" alt="${title}" class="max-w-full max-h-full object-contain rounded">
            <button onclick="this.closest('.fixed').remove()" 
                    class="absolute top-2 right-2 bg-white text-black rounded-full w-8 h-8 flex items-center justify-center hover:bg-gray-200 z-10">
                ×
            </button>
            <p class="text-white text-center mt-2 absolute bottom-2 left-1/2 transform -translate-x-1/2">${title}</p>
        </div>
    `;
    modal.onclick = (e) => { if(e.target === modal) modal.remove(); };
    document.body.appendChild(modal);
}

document.addEventListener("DOMContentLoaded", function () {
    const lessonSelect = document.querySelector("#lesson_select");
    const lessonsList = document.querySelector(".lessons-list");
    const lessonIdsInput = document.querySelector("#lesson_ids");
    let lessonPending = false;
    let ts;

    // ================= TomSelect =================
    ts = new TomSelect(lessonSelect, {
        placeholder: "--เพิ่ม/เลือกบทเรียน--",
        create: true,
        persist: false,
        sortField: { field: "text", direction: "asc" },
        plugins: ['remove_button'],
        onOptionAdd: function (value, data) {
            lessonPending = true;

            fetch("{{ route('medias.lesson.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ l_name: value, l_c_id: {{ $courseId }} })
            })
            .then(res => res.json())
            .then(data => {
                ts.removeOption(value);
                ts.addOption({ value: data.l_id, text: data.l_name });
                ts.setValue(data.l_id, true);

                const existing = lessonsList.querySelector(".lesson-item");
                if (existing) {
                    existing.setAttribute("data-id", data.l_id);
                    existing.querySelector(".lesson-name").textContent = data.l_name;
                    existing.querySelector(".edit-btn").setAttribute("data-value", data.l_id);
                    existing.querySelector(".delete-btn").setAttribute("data-value", data.l_id);
                } else {
                    addLessonToList(data.l_id, data.l_name);
                }

                lessonPending = false;
                updateLessonIds();
            })
            .catch(err => {
                console.error("Error creating lesson:", err);
                lessonPending = false;
                alert("เกิดข้อผิดพลาดในการสร้างบทเรียน");
            });
        }
    });

    ts.on('change', function (value) {
        if (value && !lessonPending) {
            const option = ts.options[value];
            if (option) {
                const existing = lessonsList.querySelector(".lesson-item");
                if (existing) {
                    existing.setAttribute("data-id", value);
                    existing.querySelector(".lesson-name").textContent = option.text;
                    existing.querySelector(".edit-btn").setAttribute("data-value", value);
                    existing.querySelector(".delete-btn").setAttribute("data-value", value);
                } else {
                    addLessonToList(value, option.text);
                }
                updateLessonIds();
            }
        }
    });

    // ================= ฟังก์ชันช่วย =================
    function addLessonToList(id, name) {
        const placeholder = lessonsList.querySelector(".lesson-placeholder");
        if (placeholder) placeholder.remove();

        const div = document.createElement("div");
        div.className = "lesson-item flex items-center justify-between p-2 border rounded mb-1";
        div.setAttribute("data-id", id);
        div.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="lesson-name">${name}</span>
                <button type="button" class="text-red-500 hover:text-red-700 font-bold remove-from-list">&times;</button>
            </div>
            <div class="flex gap-2">
                <i class="fa-solid fa-pen text-blue-600 cursor-pointer edit-btn" data-value="${id}"></i>
                <i class="fa-solid fa-trash text-red-600 cursor-pointer delete-btn" data-value="${id}"></i>
            </div>`;
        lessonsList.appendChild(div);
    }

    function updateLessonIds() {
        const item = lessonsList.querySelector(".lesson-item");
        if (lessonIdsInput) {
            lessonIdsInput.value = item ? item.getAttribute("data-id") : "";
        }
    }

    function updatePlaceholder() {
        const hasLesson = lessonsList.querySelector(".lesson-item");
        const placeholder = lessonsList.querySelector(".lesson-placeholder");
        if (!hasLesson && !placeholder) {
            const ph = document.createElement("div");
            ph.className = "lesson-placeholder text-sm text-gray-400";
            ph.textContent = "ยังไม่ได้เลือกบทเรียน";
            lessonsList.appendChild(ph);
        } else if (hasLesson && placeholder) {
            placeholder.remove();
        }
    }

    // ================= Event listener ปุ่ม =================
    lessonsList.addEventListener("click", function (e) {
        const removeBtn = e.target.closest(".remove-from-list");
        const editBtn = e.target.closest(".edit-btn");
        const deleteBtn = e.target.closest(".delete-btn");

        if (removeBtn) {
            const lessonDiv = lessonsList.querySelector(".lesson-item");
            if (lessonDiv) {
                lessonDiv.remove();
                ts.clear(true);
                updateLessonIds();
                updatePlaceholder();
            }
        }

        if (editBtn) {
            const value = editBtn.getAttribute("data-value");
            const lessonSpan = lessonsList.querySelector(`.lesson-item[data-id="${value}"] .lesson-name`);
            
            if (!lessonSpan) {
                console.error("ไม่พบ lesson span สำหรับ ID:", value);
                return;
            }

            const currentName = lessonSpan.textContent.trim();
            const newName = prompt("แก้ไขชื่อบทเรียน:", currentName);
            
            if (newName && newName.trim() !== "" && newName.trim() !== currentName) {
                lessonSpan.textContent = "กำลังอัพเดท...";
                
                const updateUrl = "{{ route('lesson.update', ':id') }}".replace(':id', value);
                
                fetch(updateUrl, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": '{{ csrf_token() }}',
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ l_name: newName.trim() })
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && data.success === true) {
                        lessonSpan.textContent = data.lesson.l_name;
                        
                        if (typeof ts !== 'undefined' && ts.updateOption) {
                            ts.updateOption(value, { value: value, text: data.lesson.l_name });
                            
                            if (ts.getValue() == value) {
                                ts.setValue(value, true);
                            }
                        }
                    } else {
                        throw new Error("Response success is false");
                    }
                })
                .catch(err => {
                    console.error("Error updating lesson:", err);
                    lessonSpan.textContent = currentName;
                    alert("เกิดข้อผิดพลาดในการอัพเดทบทเรียน: " + err.message);
                });
            }
        }

        if (deleteBtn) {
            const value = deleteBtn.getAttribute("data-value");
            const lessonDiv = lessonsList.querySelector(`.lesson-item[data-id="${value}"]`);
            const lessonName = lessonDiv ? lessonDiv.querySelector(".lesson-name").textContent : "";
            
            if (!confirm(`คุณต้องการลบบทเรียน "${lessonName}" หรือไม่?`)) {
                return;
            }

            if (lessonDiv) {
                lessonDiv.style.opacity = "0.5";
                lessonDiv.style.pointerEvents = "none";
            }

            const deleteUrl = "{{ route('lesson.destroyLesson', ':id') }}".replace(':id', value);

            fetch(deleteUrl, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": '{{ csrf_token() }}',
                    "Accept": "application/json"
                }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data && data.success === true) {
                    if (lessonDiv) {
                        lessonDiv.remove();
                    }
                    
                    if (typeof ts !== 'undefined' && ts.removeOption) {
                        ts.removeOption(value);
                        ts.clear(true);
                    }
                    
                    updateLessonIds();
                    updatePlaceholder();
                } else {
                    throw new Error("Delete failed - success is false");
                }
            })
            .catch(err => {
                console.error("Error deleting lesson:", err);
                if (lessonDiv) {
                    lessonDiv.style.opacity = "1";
                    lessonDiv.style.pointerEvents = "auto";
                }
                alert("เกิดข้อผิดพลาดในการลบบทเรียน: " + err.message);
            });
        }
    });

    updatePlaceholder();
});
</script>

@endsection
