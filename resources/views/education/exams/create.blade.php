@extends('layouts.app')

@section('title', 'สร้างแบบทดสอบ')

@section('content')
<div class="bg-base-200 p-6 rounded-lg shadow max-w-2xl mx-auto">
    <h2 class="text-2xl font-semibold mb-6 text-center">สร้างแบบทดสอบ</h2>

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

    <form action="{{ route('exams.store') }}" method="POST"
          class="flex flex-col gap-6"
          x-data="examForm()"
          @submit.prevent="validateForm()">
        <input type="hidden" name="course_id" value="{{ $courseId }}">
        @csrf

        {{-- ส่วนบทเรียน --}}
        <div class="flex flex-col gap-1">
            {{-- Label บทเรียน --}}
            <label class="block font-medium">บทเรียน</label>

            {{-- แสดงรายการบทเรียน (สำหรับแก้ไข/ลบ realtime) --}}
            <div class="lessons-list mb-2">
                <div class="lesson-placeholder text-sm text-gray-400">ยังไม่ได้เลือกบทเรียน</div>
            </div>

            {{-- เลือกบทเรียน --}}
            <div class="flex flex-col gap-1">
                <select id="lesson_select" name="e_l_id">
                    <option value="">-- เพิ่ม/เลือกบทเรียน --</option>
                    @foreach($lessons as $lesson)
                        <option value="{{ $lesson->l_id }}">{{ $lesson->l_name }}</option>
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
                      class="w-full border border-gray-300 rounded-md px-3 py-2 pr-14 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100 resize-none"></textarea>
            <span class="absolute bottom-2 right-3 text-sm text-gray-500"
                  x-text="`${examDesc.length} / 200`"></span>
        </div>

        {{-- ทักษะที่ประเมิน (จัดหมวดหมู่) --}}
        <div class="flex flex-col gap-2" x-data="skillSelect()">
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
            <input type="number" name="pass_threshold" min="1" class="input input-bordered w-40 border border-gray-300" required>
            <p class="text-xs text-gray-500">ระบบจะตรวจว่าคุณทำถูกอย่างน้อยจำนวนข้อตามเกณฑ์นี้</p>
        </div>

        {{-- ส่วนคำถาม --}}
        <div class="flex flex-col gap-4" x-data="questionManager()">
            <div class="flex items-center justify-between">
                <label class="block text-base-content mb-1">คำถาม</label>
                <!-- ปุ่มเพิ่มคำถามเมื่อยังไม่มีคำถาม -->
                <button type="button" @click="addQuestion()"
                        x-show="questions.length === 0"
                        class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    <i class="fa-solid fa-plus mr-1"></i>
                    เพิ่มคำถาม
                </button>
            </div>

            <!-- รายการคำถาม -->
            <div class="space-y-4" x-show="questions.length > 0">
                <template x-for="(question, index) in questions" :key="question.id">
                    <div class="border border-gray-300 rounded-lg p-4 bg-white shadow-sm">
                        <!-- Header คำถาม -->
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-base-content" x-text="`คำถามที่ ${index + 1}`"></h4>
                            <button type="button" @click="removeQuestion(index)"
                                    class="text-red-600 hover:text-red-800">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>

                        <!-- คำถาม -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">คำถาม</label>
                            <textarea x-model="question.q_question"
                                    :name="`questions[${index}][q_question]`"
                                    rows="2"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100 resize-none"
                                    placeholder="กรอกคำถาม..."
                                    required></textarea>
                        </div>

                        <!-- ตัวเลือก 4 ข้อ -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <template x-for="(answer, answerIndex) in ['q_answer1', 'q_answer2', 'q_answer3', 'q_answer4']" :key="answerIndex">
                                <div class="flex items-center gap-2">
                                    <!-- Radio button สำหรับเลือกคำตอบที่ถูก -->
                                    <input type="radio"
                                        :name="`questions[${index}][q_correct_answer]`"
                                        :value="answerIndex + 1"
                                        x-model="question.q_correct_answer"
                                        class="text-green-600 focus:ring-green-500"
                                        required>

                                    <!-- Input สำหรับคำตอบ -->
                                    <input type="text"
                                        x-model="question[answer]"
                                        :name="`questions[${index}][${answer}]`"
                                        :placeholder="`ตัวเลือกที่ ${answerIndex + 1}`"
                                        class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100"
                                        required>
                                </div>
                            </template>
                        </div>

                        <!-- แสดงคำตอบที่เลือก -->
                        <div class="text-sm text-gray-600">
                            <i class="fa-solid fa-check-circle text-green-600 mr-1"></i>
                            <span x-show="question.q_correct_answer">
                                คำตอบที่ถูก: ตัวเลือกที่ <span x-text="question.q_correct_answer"></span>
                            </span>
                            <span x-show="!question.q_correct_answer" class="text-red-600">
                                กรุณาเลือกคำตอบที่ถูกต้อง
                            </span>
                        </div>
                    </div>
                </template>

                <!-- ปุ่มเพิ่มคำถาม - อยู่ส่วนล่างของคำถามล่าสุด -->
                <div class="flex justify-center">
                    <button type="button" @click="addQuestion()"
                            class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                        <i class="fa-solid fa-plus mr-2"></i>
                        เพิ่มคำถามใหม่
                    </button>
                </div>
            </div>

            <!-- ข้อความเมื่อไม่มีคำถาม -->
            <div x-show="questions.length === 0"
                class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
                <i class="fa-solid fa-question-circle text-4xl mb-2 text-gray-400"></i>
                <p>ยังไม่มีคำถาม กดปุ่ม "เพิ่มคำถาม" เพื่อเริ่มต้น</p>
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

function examForm() {
    return {
        examName: '',
        examDesc: '',
        examNameError: false,

        init() {
            console.log('Alpine.js examForm initialized');
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

            // ตรวจสอบคำถาม
            const questionTextareas = document.querySelectorAll('textarea[name*="[q_question]"]');
            if (questionTextareas.length === 0) {
                alert("กรุณาเพิ่มคำถามอย่างน้อย 1 ข้อ");
                return;
            }

            // ตรวจสอบว่าคำถามมีเนื้อหา
            let hasValidQuestion = false;
            questionTextareas.forEach(textarea => {
                if (textarea.value.trim() !== '') {
                    hasValidQuestion = true;
                }
            });

            if (!hasValidQuestion) {
                alert("กรุณากรอกคำถามให้ครบถ้วน");
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

function skillSelect() {
    return {
        selectedSkills: [],
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

function questionManager() {
    return {
        questions: [],
        questionIdCounter: 1,

        addQuestion() {
            this.questions.push({
                id: this.questionIdCounter++,
                q_question: '',
                q_answer1: '',
                q_answer2: '',
                q_answer3: '',
                q_answer4: '',
                q_correct_answer: ''
            });
        },

        removeQuestion(index) {
            if (confirm('คุณต้องการลบคำถามนี้หรือไม่?')) {
                this.questions.splice(index, 1);
            }
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const lessonSelect = document.querySelector("#lesson_select");
    const lessonsList = document.querySelector(".lessons-list");
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
                    // ลบจาก UI แบบ real-time
                    if (lessonDiv) {
                        lessonDiv.remove();
                    }

                    // ลบจาก TomSelect
                    if (typeof ts !== 'undefined' && ts.removeOption) {
                        ts.removeOption(value);
                        ts.clear(true);
                    }

                    updatePlaceholder();
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
