@extends('layouts.app')

@section('title', 'สร้างสื่อการสอน')

@section('content')
<div class="bg-base-200 p-6 rounded-lg shadow max-w-2xl mx-auto">

    <h2 class="text-2xl font-semibold mb-6 text-center">สร้างสื่อการสอน</h2>

    {{-- แสดงข้อความสำเร็จ --}}
    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 mb-6 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('medias.store') }}" method="POST" enctype="multipart/form-data"
          class="flex flex-col gap-6"
          x-data="mediaForm()"
          @submit.prevent="validateForm()">
        <input type="hidden" name="course_id" value="{{ $courseId }}">
        
        @csrf

    {{-- ครอบทั้งหมด --}}
    <div class="flex flex-col gap-1">

        {{-- Label บทเรียน --}}
        <label class="block font-medium">บทเรียน</label>

        {{-- แสดงรายการบทเรียน (สำหรับแก้ไข/ลบ realtime) --}}
        <div class="lessons-list mb-2">
            <div class="lesson-placeholder text-sm text-gray-400">ยังไม่ได้เลือกบทเรียน</div>
        </div>

        {{-- เลือกบทเรียน --}}
        <div class="flex flex-col gap-1">
            <select id="lesson_select" name="m_l_id">
                <option value="">-- เพิ่ม/เลือกบทเรียน --</option>
                @foreach($lessons as $lesson)
                    <option value="{{ $lesson->l_id }}">{{ $lesson->l_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

        {{-- ชื่อสื่อการสอน --}}
        <div class="flex flex-col gap-1 relative">
            <label class="block text-base-content mb-1">ชื่อสื่อการสอน</label>

            <div class="relative">
                <input type="text" name="m_name"
                    x-model="mediaName"
                    maxlength="50"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100"
                    required>

                <!-- ตัวนับตัวอักษร ลอยมุมขวาล่าง -->
                <span class="absolute bottom-2 right-3 text-sm text-gray-500 pointer-events-none"
                    x-text="`${mediaName.length} / 50`"></span>
            </div>

            <!-- ข้อความแจ้งเตือน แยกออกมา -->
            <span class="text-red-600 text-sm mt-1" x-show="mediaNameError" x-cloak>
                กรุณากรอกชื่อสื่อการสอน
            </span>
        </div>

        {{-- คำอธิบายสื่อการสอน --}}
        <div class="flex flex-col gap-1 relative">
            <label class="block text-base-content mb-1">คำอธิบายสื่อการสอน</label>
            <textarea name="m_desc" x-model="mediaDesc" rows="4" maxlength="200"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 pr-14 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100 resize-none"></textarea>
            <span class="absolute bottom-2 right-3 text-sm text-gray-500"
                  x-text="`${mediaDesc.length} / 200`"></span>
        </div>

        {{-- ไฟล์แนบ --}}
        <div class="flex flex-col gap-1">
            <label class="block text-base-content mb-1">ไฟล์แนบ</label>

            <!-- กล่องอัปโหลด -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500"
                @click="$refs.fileInput.click()"
                @dragover.prevent
                @drop.prevent="handleDrop($event)">
                <p class="text-gray-600">ลากไฟล์มาวางที่นี่ หรือ <span class="text-blue-600 underline">เลือกไฟล์</span></p>
                <p class="text-xs text-gray-400 mt-1">รองรับหลายไฟล์ (PDF, DOCX, PNG, JPG, MP4, MOV, AVI และอื่นๆ) ขนาดสูงสุดไฟล์ละ {{ (int) config('media.max_upload_mb') }}MB</p>
            </div>

            <!-- input file จริง แต่ซ่อน -->
            <input type="file" name="files[]" multiple class="hidden"
                x-ref="fileInput" @change="handleFiles($event)">

            <!-- แสดงรายการไฟล์ -->
            <div x-show="files.length > 0" class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <template x-for="(file, index) in files" :key="index">
                    <div class="bg-white border border-gray-300 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                        
                        <!-- Header ไฟล์ -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 border-b border-gray-100">
                            <div class="flex items-center gap-2 flex-1 min-w-0 cursor-pointer" 
                                @click="openFile(file)">
                                <i :class="getFileIcon(file.name) + ' text-lg flex-shrink-0'"></i>
                                <span x-text="file.name" class="truncate text-sm font-medium text-gray-700 hover:text-blue-600"></span>
                            </div>
                            <button type="button" @click.stop="removeFile(index)" 
                                    class="ml-2 w-6 h-6 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center text-sm font-bold transition-colors">
                                ×
                            </button>
                        </div>
                        
                        <!-- Preview Area -->
                        <div class="p-3">
                            <!-- Image preview - แก้ไขให้ใช้ URL.createObjectURL -->
                            <div x-show="isImage(file.name)" @click="openFile(file)" class="cursor-pointer group">
                                <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                    <img :src="URL.createObjectURL(file)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Preview">
                                </div>
                            </div>
                            
                            <!-- Video preview - แก้ไขให้ใช้ URL.createObjectURL -->
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
                                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center mb-2 group-hover:bg-gray-200 transition-colors">
                                    <i :class="getFileIcon(file.name) + ' text-2xl'"></i>
                                </div>
                                <p class="text-xs text-gray-500 group-hover:text-blue-600">คลิกเพื่อเปิดไฟล์</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ปุ่ม Action --}}
        <div class="col-span-1 md:col-span-2 flex justify-center gap-4 mt-10">
            <button type="submit" 
                    class="btn bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-3">
                สร้าง
            </button>
           <a href="{{ route('courses.show', ['id' => $courseId]) }}" 
            class="btn btn-outline text-base-content rounded-lg px-6 py-3">
                ยกเลิก
            </a>
        </div>
    </form>
</div>

<script>
let lessonPending = false;

function mediaForm() {
    return {
        mediaName: '',
        mediaDesc: '',
        mediaNameError: false,
        files: [],

        openFile(file) {
            if (this.isImage(file.name)) {
                // สำหรับรูปภาพ แสดง modal
                this.openImageModal(URL.createObjectURL(file), file.name);
            } else {
                // สำหรับไฟล์อื่นๆ เปิดใน tab ใหม่
                const fileUrl = URL.createObjectURL(file);
                window.open(fileUrl, '_blank');
            }
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

        validateFileType(file) {
            const allowedTypes = [
                // Documents
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                // Images
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
                'image/webp',
                // Videos
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

        // ฟังก์ชันตรวจสอบไอคอนตามนามสกุล
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

        validateForm() {
            if (lessonPending) {
                alert("กรุณารอสักครู่ กำลังสร้างบทเรียนใหม่...");
                return;
            }

            this.mediaNameError = this.mediaName.trim() === '';
            if (this.mediaNameError) return;

            // ตรวจสอบว่า m_l_id เป็น number
            const select = document.querySelector("#lesson_select");
            const value = select.value;
            if (value && isNaN(parseInt(value))) {
                alert("กรุณารอระบบสร้างบทเรียนใหม่ให้เสร็จ");
                return;
            }

            this.$el.submit();
        },

         handleFiles(event) {
            const MAX_MB = {{ (int) config('media.max_upload_mb') }};
            const MAX_BYTES = MAX_MB * 1024 * 1024;
            const newFiles = Array.from(event.target.files).filter(file => {
                if (!this.validateFileType(file)) {
                    alert(`ไฟล์ ${file.name} ไม่รองรับ กรุณาเลือกไฟล์ประเภท PDF, DOCX, รูปภาพ หรือวีดีโอ`);
                    return false;
                }
                if (file.size > MAX_BYTES) { // dynamic max size
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
                if (file.size > MAX_BYTES) { // dynamic max size
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

document.addEventListener("DOMContentLoaded", function () {
    const lessonSelect = document.querySelector("#lesson_select");
    const lessonsList = document.querySelector(".lessons-list");
    const lessonIdsInput = document.querySelector("#lesson_ids");
    let lessonPending = false;
    let ts; // ประกาศ ts ที่ scope ที่สามารถเข้าถึงได้ทุกที่

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
                // ลบ option ชั่วคราวและเพิ่ม option ที่มี ID จริง
                ts.removeOption(value);
                ts.addOption({ value: data.l_id, text: data.l_name });
                ts.setValue(data.l_id, true);

                // อัพเดท UI
                const existing = lessonsList.querySelector(".lesson-item");
                if (existing) {
                    existing.setAttribute("data-id", data.l_id);
                    existing.querySelector(".lesson-name").textContent = data.l_name;
                    // อัพเดทปุ่ม edit และ delete ด้วย ID ใหม่
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

    // เมื่อเลือกบทเรียนจาก dropdown
    ts.on('change', function (value) {
        if (value && !lessonPending) {
            const option = ts.options[value];
            if (option) {
                const existing = lessonsList.querySelector(".lesson-item");
                if (existing) {
                    existing.setAttribute("data-id", value);
                    existing.querySelector(".lesson-name").textContent = option.text;
                    // อัพเดทปุ่ม edit และ delete
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

        // ----- ลบออกจาก list (ไม่ลบจากฐานข้อมูล) -----
        if (removeBtn) {
            const lessonDiv = lessonsList.querySelector(".lesson-item");
            if (lessonDiv) {
                lessonDiv.remove();
                ts.clear(true);
                updateLessonIds();
                updatePlaceholder();
            }
        }

        // ----- แก้ไขชื่อ -----
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
                // แสดง loading state
                lessonSpan.textContent = "กำลังอัพเดท...";
                
                // ใช้ named route
                const updateUrl = "{{ route('lesson.update', ':id') }}".replace(':id', value);
                console.log("Update URL:", updateUrl); // debug
                
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
                    console.log("Response status:", res.status);
                    console.log("Response headers:", res.headers.get('content-type'));
                    
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log("Update response:", data);
                    if (data && data.success === true) {
                        // อัพเดท UI แบบ real-time
                        lessonSpan.textContent = data.lesson.l_name;
                        
                        // อัพเดท TomSelect option
                        if (typeof ts !== 'undefined' && ts.updateOption) {
                            ts.updateOption(value, { value: value, text: data.lesson.l_name });
                            
                            // ถ้า option นี้ถูกเลือกอยู่ ให้อัพเดท display text
                            if (ts.getValue() == value) {
                                ts.setValue(value, true);
                            }
                        }
                        
                        console.log("อัพเดทบทเรียนสำเร็จ");
                        alert("อัพเดทสำเร็จ!"); // เพิ่ม alert เพื่อทดสอบ
                    } else {
                        throw new Error("Response success is false");
                    }
                })
                .catch(err => {
                    console.error("Error updating lesson:", err);
                    lessonSpan.textContent = currentName; // คืนค่าเดิม
                    alert("เกิดข้อผิดพลาดในการอัพเดทบทเรียน: " + err.message);
                });
            }
        }

        // ----- ลบบทเรียน -----
        if (deleteBtn) {
            const value = deleteBtn.getAttribute("data-value");
            const lessonDiv = lessonsList.querySelector(`.lesson-item[data-id="${value}"]`);
            const lessonName = lessonDiv ? lessonDiv.querySelector(".lesson-name").textContent : "";
            
            if (!confirm(`คุณต้องการลบบทเรียน "${lessonName}" หรือไม่?`)) {
                return;
            }

            // แสดง loading state
            if (lessonDiv) {
                lessonDiv.style.opacity = "0.5";
                lessonDiv.style.pointerEvents = "none";
            }

            // ใช้ named route
            const deleteUrl = "{{ route('lesson.destroyLesson', ':id') }}".replace(':id', value);
            console.log("Delete URL:", deleteUrl); // debug

            fetch(deleteUrl, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": '{{ csrf_token() }}',
                    "Accept": "application/json"
                }
            })
            .then(res => {
                console.log("Delete response status:", res.status);
                console.log("Delete response headers:", res.headers.get('content-type'));
                
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                console.log("Delete response:", data);
                if (data && data.success === true) {
                    // ลบจาก UI แบบ real-time
                    if (lessonDiv) {
                        lessonDiv.remove();
                    }
                    
                    // ลบจาก TomSelect
                    if (typeof ts !== 'undefined' && ts.removeOption) {
                        ts.removeOption(value);
                        ts.clear(true);
                    }
                    
                    updateLessonIds();
                    updatePlaceholder();
                    
                    console.log("ลบบทเรียนสำเร็จ");
                    alert("ลบสำเร็จ!"); // เพิ่ม alert เพื่อทดสอบ
                } else {
                    throw new Error("Delete failed - success is false");
                }
            })
            .catch(err => {
                console.error("Error deleting lesson:", err);
                // คืนค่า UI เดิม
                if (lessonDiv) {
                    lessonDiv.style.opacity = "1";
                    lessonDiv.style.pointerEvents = "auto";
                }
                alert("เกิดข้อผิดพลาดในการลบบทเรียน: " + err.message);
            });
        }
    });

    // เรียกตอนโหลดหน้าเพื่อเช็ค placeholder
    updatePlaceholder();
});
</script>

@endsection
