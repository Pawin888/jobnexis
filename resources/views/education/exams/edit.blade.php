@extends('layouts.app')

@section('title', 'แก้ไขแบบทดสอบ')

@section('content')
<div class="bg-base-200 p-6 rounded-lg shadow max-w-2xl mx-auto">
    <h2 class="text-2xl font-semibold mb-6 text-center">แก้ไขแบบทดสอบ</h2>

    {{-- แสดงข้อความสำเร็จ --}}
    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 mb-6 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- แสดง Error --}}
    @if($errors->any())
        <div class="bg-red-200 text-red-800 p-3 mb-6 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('exams.update', $exam->e_id) }}" method="POST"
          class="flex flex-col gap-6"
          x-data="examForm()"
          @submit.prevent="validateForm()">
        <input type="hidden" name="course_id" value="{{ $courseId }}">
        @csrf
        @method('PUT')

        {{-- ส่วนบทเรียน --}}
        <div class="flex flex-col gap-1">
            {{-- Label บทเรียน --}}
            <label class="block font-medium">บทเรียน</label>

            {{-- แสดงรายการบทเรียน (สำหรับแก้ไข/ลบ realtime) --}}
            <div class="lessons-list mb-2">
                @if($exam->e_l_id && $exam->lesson)
                    <div class="lesson-item flex items-center justify-between p-2 border rounded mb-1" data-id="{{ $exam->e_l_id }}">
                        <div class="flex items-center gap-2">
                            <span class="lesson-name">{{ $exam->lesson->l_name }}</span>
                            <button type="button" class="text-red-500 hover:text-red-700 font-bold remove-from-list">&times;</button>
                        </div>
                        <div class="flex gap-2">
                            <i class="fa-solid fa-pen text-blue-600 cursor-pointer edit-btn" data-value="{{ $exam->e_l_id }}"></i>
                            <i class="fa-solid fa-trash text-red-600 cursor-pointer delete-btn" data-value="{{ $exam->e_l_id }}"></i>
                        </div>
                    </div>
                @else
                    <div class="lesson-placeholder text-sm text-gray-400">ยังไม่ได้เลือกบทเรียน</div>
                @endif
            </div>

            {{-- เลือกบทเรียน --}}
            <div class="flex flex-col gap-1">
                <select id="lesson_select" name="e_l_id">
                    <option value="">-- เพิ่ม/เลือกบทเรียน --</option>
                    @foreach($lessons as $lesson)
                        <option value="{{ $lesson->l_id }}" {{ $exam->e_l_id == $lesson->l_id ? 'selected' : '' }}>
                            {{ $lesson->l_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ชื่อแบบทดสอบ --}}
        <div class="flex flex-col gap-1 relative">
            <label class="block text-base-content mb-1">ชื่อแบบทดสอบ</label>
            <div class="relative">
                <input type="text" name="e_name"
                    x-model="examName"
                    maxlength="50"
                    value="{{ old('e_name', $exam->e_name) }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100"
                    required>
                <!-- ตัวนับตัวอักษร ลอยมุมขวาล่าง -->
                <span class="absolute bottom-2 right-3 text-sm text-gray-500 pointer-events-none"
                    x-text="`${examName.length} / 50`"></span>
            </div>
            <!-- ข้อความแจ้งเตือน แยกออกมา -->
            <span class="text-red-600 text-sm mt-1"
                x-show="examNameError"
                x-cloak
                style="display: none;">
                กรุณากรอกชื่อแบบทดสอบ
            </span>
        </div>

        {{-- คำอธิบายแบบทดสอบ --}}
        <div class="flex flex-col gap-1 relative">
            <label class="block text-base-content mb-1">คำอธิบายแบบทดสอบ</label>
            <textarea name="e_description" x-model="examDesc" rows="4" maxlength="200"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 pr-14 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100 resize-none">{{ old('e_description', $exam->e_description) }}</textarea>
            <span class="absolute bottom-2 right-3 text-sm text-gray-500"
                  x-text="`${examDesc.length} / 200`"></span>
        </div>

        {{-- ทักษะที่ประเมิน (จัดหมวดหมู่) --}}
        <div class="flex flex-col gap-2" x-data="skillSelectEdit()">
            <label class="block text-base-content mb-1">ทักษะที่แบบทดสอบนี้วัดผล</label>

            <div class="flex flex-wrap gap-2 mb-2">
                <template x-for="(skill,index) in selectedSkills" :key="index">
                    <span class="px-3 py-1 rounded-full border border-gray-300 bg-base-100 text-base-content">
                        <span x-text="skill"></span>
                        <button type="button" class="ml-2 text-red-500" @click="removeSkill(index)">&times;</button>
                    </span>
                </template>
                <template x-if="selectedSkills.length === 0">
                    <span class="text-sm text-gray-400">ยังไม่ได้เลือกทักษะ</span>
                </template>
            </div>

            <div class="flex items-center gap-2">
                <select x-model="currentSkill" class="select select-bordered border border-gray-300">
                    <option value="">เลือกทักษะ</option>
                    @foreach($skillsByCategory as $category => $items)
                        <optgroup label="{{ $category }}">
                            @foreach($items as $skill)
                                <option value="{{ $skill->name }}">{{ $skill->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <button type="button" class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm" @click="addSkill()">เพิ่ม</button>
            </div>

            <input type="hidden" name="skills" :value="selectedSkills.join(',')">
            <p class="text-sm text-red-600" x-show="skillError">กรุณาเลือกอย่างน้อย 1 ทักษะ</p>
        </div>

        {{-- เกณฑ์ผ่าน (จำนวนข้อที่ต้องถูกอย่างน้อย) --}}
        <div class="flex flex-col gap-1">
            <label class="block text-base-content mb-1">เกณฑ์ผ่าน (จำนวนข้อ)</label>
            <input type="number" name="pass_threshold" min="1" class="input input-bordered w-40 border border-gray-300" value="{{ old('pass_threshold', $exam->pass_threshold) }}" required>
            <p class="text-xs text-gray-500">ระบบจะตรวจว่าคุณทำถูกอย่างน้อยจำนวนข้อตามเกณฑ์นี้</p>
        </div>

        {{-- แสดงคำถามที่มีอยู่ --}}
        @if($exam->questions && $exam->questions->count() > 0)
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <label class="block text-base-content font-medium">คำถามที่มีอยู่ ({{ $exam->questions->count() }} ข้อ)</label>
                    <a href="{{ route('questions.manage', $exam->e_id) }}"
                       class="px-3 py-1 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">
                        <i class="fa-solid fa-list-check mr-1"></i>
                        จัดการคำถาม
                    </a>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($exam->questions as $question)
                        <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                            <div class="mb-2">
                                <span class="font-medium text-gray-700">{{ $loop->iteration }}. {{ $question->q_question }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full {{ $question->q_correct_answer == 1 ? 'bg-blue-500 text-white' : 'bg-gray-300' }} flex items-center justify-center text-xs font-bold">1</span>
                                    <span class="{{ $question->q_correct_answer == 1 ? 'text-blue-700 font-medium' : 'text-gray-600' }}">{{ $question->q_answer1 }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full {{ $question->q_correct_answer == 2 ? 'bg-blue-500 text-white' : 'bg-gray-300' }} flex items-center justify-center text-xs font-bold">2</span>
                                    <span class="{{ $question->q_correct_answer == 2 ? 'text-blue-700 font-medium' : 'text-gray-600' }}">{{ $question->q_answer2 }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full {{ $question->q_correct_answer == 3 ? 'bg-blue-500 text-white' : 'bg-gray-300' }} flex items-center justify-center text-xs font-bold">3</span>
                                    <span class="{{ $question->q_correct_answer == 3 ? 'text-blue-700 font-medium' : 'text-gray-600' }}">{{ $question->q_answer3 }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full {{ $question->q_correct_answer == 4 ? 'bg-blue-500 text-white' : 'bg-gray-300' }} flex items-center justify-center text-xs font-bold">4</span>
                                    <span class="{{ $question->q_correct_answer == 4 ? 'text-blue-700 font-medium' : 'text-gray-600' }}">{{ $question->q_answer4 }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-6 text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <i class="fa-solid fa-question-circle text-3xl mb-2 text-gray-400"></i>
                <p class="mb-3">ยังไม่มีคำถามในแบบทดสอบนี้</p>
                <a href="{{ route('questions.manage', $exam->e_id) }}"
                   class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">
                    <i class="fa-solid fa-plus mr-2"></i>
                    เพิ่มคำถาม
                </a>
            </div>
        @endif

        {{-- ปุ่ม Action --}}
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
    </form>
</div>

<script>
let lessonPending = false;

function examForm() {
    return {
        examName: '{{ old('e_name', $exam->e_name) }}',
        examDesc: '{{ old('e_description', $exam->e_description) }}',
        examNameError: false,

        init() {
            console.log('Alpine.js examForm initialized for edit');
        },

        validateForm() {
            console.log('validateForm called');

            if (lessonPending) {
                alert("กรุณารอสักครู่ กำลังสร้างบทเรียนใหม่...");
                return;
            }

            this.examNameError = this.examName.trim() === '';

            if (this.examNameError) {
                console.log('Form validation failed - empty name');
                return;
            }

            // ตรวจสอบว่า e_l_id เป็น number
            const select = document.querySelector("#lesson_select");
            const value = select.value;
            if (value && isNaN(parseInt(value))) {
                alert("กรุณารอระบบสร้างบทเรียนใหม่ให้เสร็จ");
                return;
            }

            console.log('Form validation passed, submitting...');
            this.$el.submit();
        }
    }
}

function skillSelectEdit() {
    return {
        selectedSkills: @json(collect($exam->skills ?? [])->pluck('name')),
        currentSkill: '',
        skillError: false,
        addSkill() {
            if (this.currentSkill && !this.selectedSkills.includes(this.currentSkill)) {
                this.selectedSkills.push(this.currentSkill);
                this.skillError = false;
            }
            this.currentSkill = '';
        },
        removeSkill(index) {
            this.selectedSkills.splice(index, 1);
            if (this.selectedSkills.length === 0) this.skillError = true;
        }
    }
}

// ใช้ TomSelect และ lesson management code เหมือนเดิม...
document.addEventListener("DOMContentLoaded", function () {
    const lessonSelect = document.querySelector("#lesson_select");
    const lessonsList = document.querySelector(".lessons-list");
    let lessonPending = false;
    let ts;

    ts = new TomSelect(lessonSelect, {
        placeholder: "--เพิ่ม/เลือกบทเรียน--",
        create: true,
        persist: false,
        sortField: { field: "text", direction: "asc" },
        plugins: ['remove_button'],
        onOptionAdd: function (value, data) {
            lessonPending = true;

            fetch("{{ route('exams.lesson.store') }}", {
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
            })
            .catch(err => {
                console.error("Error creating lesson:", err);
                lessonPending = false;
                alert("เกิดข้อผิดพลาดในการสร้างบทเรียน");
            });
        }
    });

    // เพิ่มฟังก์ชันช่วยเหลือเหมือนเดิม...
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

    // Event listeners เหมือนเดิม...
    lessonsList.addEventListener("click", function (e) {
        const removeBtn = e.target.closest(".remove-from-list");
        const editBtn = e.target.closest(".edit-btn");
        const deleteBtn = e.target.closest(".delete-btn");

        if (removeBtn) {
            const lessonDiv = lessonsList.querySelector(".lesson-item");
            if (lessonDiv) {
                lessonDiv.remove();
                ts.clear(true);
                updatePlaceholder();
            }
        }

        if (editBtn) {
            const value = editBtn.getAttribute("data-value");
            const lessonSpan = lessonsList.querySelector(`.lesson-item[data-id="${value}"] .lesson-name`);

            if (!lessonSpan) return;

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
                .then(res => res.json())
                .then(data => {
                    if (data && data.success === true) {
                        lessonSpan.textContent = data.lesson.l_name;
                        if (typeof ts !== 'undefined' && ts.updateOption) {
                            ts.updateOption(value, { value: value, text: data.lesson.l_name });
                            if (ts.getValue() == value) {
                                ts.setValue(value, true);
                            }
                        }
                    }
                })
                .catch(err => {
                    console.error("Error updating lesson:", err);
                    lessonSpan.textContent = currentName;
                    alert("เกิดข้อผิดพลาดในการอัพเดทบทเรียน");
                });
            }
        }

        if (deleteBtn) {
            const value = deleteBtn.getAttribute("data-value");
            const lessonDiv = lessonsList.querySelector(`.lesson-item[data-id="${value}"]`);
            const lessonName = lessonDiv ? lessonDiv.querySelector(".lesson-name").textContent : "";

            if (!confirm(`คุณต้องการลบบทเรียน "${lessonName}" หรือไม่?`)) return;

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
            .then(res => res.json())
            .then(data => {
                if (data && data.success === true) {
                    if (lessonDiv) lessonDiv.remove();
                    if (typeof ts !== 'undefined' && ts.removeOption) {
                        ts.removeOption(value);
                        ts.clear(true);
                    }
                    updatePlaceholder();
                }
            })
            .catch(err => {
                console.error("Error deleting lesson:", err);
                if (lessonDiv) {
                    lessonDiv.style.opacity = "1";
                    lessonDiv.style.pointerEvents = "auto";
                }
                alert("เกิดข้อผิดพลาดในการลบบทเรียน");
            });
        }
    });

    updatePlaceholder();
});
</script>
@endsection
