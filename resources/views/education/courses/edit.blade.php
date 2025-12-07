@extends('layouts.app')

@section('title', 'แก้ไขคอร์สอบรม')

@section('content')
<div class="bg-base-200 p-6 rounded-lg shadow w-full">

    <!-- Header Section -->
    <div class="flex items-center justify-between border-b pb-3 mb-4">
        <div class="flex items-baseline gap-3">
            <a href="{{ route('courses.show', ['id' => $course->c_id]) }}" 
               class="text-xl text-base-content cursor-pointer hover:text-blue-600">
                รายละเอียด
            </a>
            <a href="{{ route('courses.person.show', ['id' => $course->c_id]) }}"  
               class="text-xl text-base-content cursor-pointer hover:text-blue-600">
                บุคคล
            </a>
        </div>

        <div>
            <a href="{{ route('courses.edit', ['id' => $course->c_id]) }}">
                <i class="fa-solid fa-gear text-3xl text-blue-600 cursor-pointer hover:text-blue-600"></i>
            </a>
        </div>
    </div>

    <!-- Form Edit Course -->
    <div class="p-6 max-w-5xl mx-auto">
        <h1 class="text-2xl text-base-content font-bold mb-6 text-center">แก้ไขคอร์สอบรม</h1>

        <form action="{{ route('courses.update', ['id' => $course->c_id]) }}" 
              method="POST" 
              enctype="multipart/form-data"
              class="grid grid-cols-1 md:grid-cols-2 gap-6"
              x-data="courseForm()"
              @submit.prevent="validateForm()">

            @csrf
            @method('PUT')

            <!-- Left: Image Upload -->
            <div class="flex flex-col gap-4 justify-center items-center w-full">
                <div class="border-2 border-dashed border-gray-400 rounded-lg bg-gray-200 overflow-hidden aspect-[16/10] w-full">
                    <!-- ใช้ img แค่ tag เดียว แสดง preview ถ้ามี หรือรูปจาก DB -->
                    <img :src="previewUrl || '{{ $course->c_image ? asset('storage/'.$course->c_image) : asset('image/web-image/ai-robot.jpg') }}'" 
                        alt="Course Image" 
                        class="w-full h-full object-cover rounded-lg">
                </div>

                <label class="border-2 border-dashed border-blue-600 rounded-lg px-4 py-2 text-center cursor-pointer hover:border-blue-700">
                    เลือกรูปภาพจากคอมพิวเตอร์ของคุณ
                    <input type="file" name="c_image" class="hidden"
                           @change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                </label>
            </div>

            <!-- Right: Course Form -->
            <div class="flex flex-col gap-5">

                <!-- Course Name -->
                <div class="relative gap-1">
                    <label class="block text-base-content mb-1">ชื่อคอร์สอบรม</label>
                    <div class="relative">
                        <input type="text" name="c_name" x-model="courseName" maxlength="50"
                               @input="courseNameError = false"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 pr-14 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100">
                        <span class="absolute top-1/2 right-3 -translate-y-1/2 text-sm text-gray-500" 
                              x-text="`${courseName.length} / 50`"></span>
                        <span class="absolute -bottom-5 left-0 text-red-600 text-sm" x-show="courseNameError">
                            กรุณากรอกชื่อคอร์ส
                        </span>
                    </div>
                </div>

                <!-- Description -->
                <div class="relative gap-1">
                    <label class="block text-base-content mb-1">คำอธิบายคอร์สอบรม</label>
                    <div class="relative">
                        <textarea name="c_description" x-model="desc" rows="4" maxlength="200"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 pr-14 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100 resize-none"></textarea>
                        <span class="absolute bottom-2 right-3 text-sm text-gray-500" 
                              x-text="`${desc.length} / 200`"></span>
                    </div>
                </div>

                <!-- Skills -->
                <div class="relative gap-1">
                    <label class="block text-base-content font-normal mb-1">ประเภททักษะ</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <template x-for="(skill,index) in selectedSkills" :key="index">
                            <span class="px-3 py-1 rounded-full text-base-content border border-gray-400"
                                  :class="index % 2 === 0 ? 'bg-blue-200' : 'bg-base-200'">
                                <span x-text="skill"></span>
                                <button type="button" @click="removeSkill(index)" class="ml-1">✕</button>
                            </span>
                        </template>
                        <template x-if="selectedSkills.length === 0">
                            <span class="px-3 py-1 rounded-full bg-base-200 text-base-content border border-gray-400">
                                ยังไม่มีข้อมูล
                            </span>
                        </template>
                    </div>

                    <select x-model="currentSkill" @change="addSkill"
                            class="font-normal w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100">
                        <option value="">เลือกทักษะ</option>
                        @foreach($skills as $skill)
                            <option value="{{ $skill->name }}">{{ $skill->name }}</option>
                        @endforeach
                    </select>

                    <input type="hidden" name="skills" :value="selectedSkills.join(',')">
                    <span class="absolute -bottom-5 left-0 text-red-600 text-sm" x-show="skillError">
                        กรุณาเลือกอย่างน้อย 1 ทักษะ
                    </span>
                </div>

                <!-- Code + Toggle -->
                <div class="flex items-start gap-6">
                    <!-- Course Code -->
                    <div class="flex flex-col w-1/2 gap-1">
                        <label class="block text-base-content mb-1">รหัสคอร์สอบรม</label>
                        <input type="text" name="c_code" x-model="courseCode"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-300 text-gray-600 cursor-not-allowed focus:outline-none"
                               readonly>
                    </div>

                    <!-- Toggle Publish (no approval, open/draft) -->
                    <div class="flex flex-col w-1/2 gap-1" x-data="{ tempStatus: '{{ $course->c_status }}' }">
                        <label class="mb-1 text-base-content">เผยแพร่คอร์สอบรม</label>
                        <div class="flex items-center h-full">
                            <div @click="tempStatus = (tempStatus === 'open') ? 'draft' : 'open'"
                                 :class="tempStatus === 'open' ? 'bg-blue-600' : 'bg-gray-300'"
                                 class="relative transition-colors rounded-full cursor-pointer w-14 h-7">
                                <span class="absolute w-5 h-5 transition-transform bg-white rounded-full shadow-md left-1 top-1"
                                      :class="tempStatus === 'open' ? 'translate-x-7' : 'translate-x-0'"></span>
                                <input type="hidden" name="c_status" :value="tempStatus">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-span-1 md:col-span-2 flex justify-center gap-4 mt-10">
                <button type="submit" 
                        class="btn bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-3">
                    บันทึก
                </button>
                <a href="{{ route('courses.show', ['id' => $course->c_id]) }}" 
                   class="btn btn-outline text-base-content rounded-lg px-6 py-3">
                    ยกเลิก
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function courseForm() {
    return {
        previewUrl: null,
        courseName: '{!! $course->c_name !!}',
        desc: '{!! $course->c_description !!}',
        selectedSkills: @json(collect($course->skills)->pluck('name')),
        currentSkill: '',
        courseCode: '{{ $course->c_code }}',
        courseNameError: false,
        skillError: false,

        addSkill() {
            if (this.currentSkill && !this.selectedSkills.includes(this.currentSkill)) {
                this.selectedSkills.push(this.currentSkill);
                this.skillError = false;
                this.generateCode();
            }
            this.currentSkill = '';
        },

        removeSkill(index) {
            this.selectedSkills.splice(index, 1);
            if (this.selectedSkills.length === 0) this.skillError = true;
            this.generateCode();
        },

        generateCode() {
            if (this.selectedSkills.length > 0) {
                let skillAbbr = this.selectedSkills[0].substring(0, 3).toUpperCase();
                let chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let code = '';
                do {
                    code = skillAbbr + '-';
                    for (let i = 0; i < 6; i++) code += chars.charAt(Math.floor(Math.random() * chars.length));
                } while (this.existingCodes && this.existingCodes.includes(code));
                this.courseCode = code;
            } else this.courseCode = '';
        },

        validateForm() {
            this.courseNameError = this.courseName.trim() === '';
            this.skillError = this.selectedSkills.length === 0;
            if (!this.courseNameError && !this.skillError) this.$el.submit();
        }
    }
}
</script>
@endsection
