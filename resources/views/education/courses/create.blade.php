@extends('layouts.app')

@section('title', 'สร้างคอร์สอบรม')

@section('content')
<div class="max-w-5xl p-6 mx-auto rounded-lg shadow bg-base-200">
    <h1 class="mb-6 text-2xl font-bold text-center text-base-content">สร้างคอร์สอบรม</h1>

    <form action="{{ route('courses.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="grid grid-cols-1 gap-6 md:grid-cols-2"
          x-data="courseForm()"
          @submit.prevent="validateForm()">

        @csrf

        <!-- ซ้าย: อัปโหลดรูปภาพ -->
        <div class="flex flex-col items-center justify-center w-full gap-4">
            <div class="border-2 border-dashed border-gray-400 rounded-lg bg-gray-200 overflow-hidden aspect-[16/10] w-full">
                <template x-if="!previewUrl">
                    <img src="{{ asset('image/web-image/ai-robot.jpg') }}"
                         alt="Placeholder"
                         class="object-cover w-full h-full">
                </template>
                <template x-if="previewUrl">
                    <img :src="previewUrl"
                         class="object-cover w-full h-full">
                </template>
            </div>

            <label class="px-4 py-2 text-center border-2 border-blue-600 border-dashed rounded-lg cursor-pointer hover:border-blue-700">
                เลือกรูปภาพจากคอมพิวเตอร์ของคุณ
                <input type="file"
                       class="hidden"
                       name="c_image"
                       @change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
            </label>
        </div>

        <!-- ขวา: ฟอร์มคอร์ส -->
        <div class="flex flex-col gap-5">

            <!-- ชื่อคอร์ส -->
            <div class="relative gap-1">
                <label class="block mb-1 text-base-content">ชื่อคอร์สอบรม</label>
                <div class="relative">
                    <input type="text"
                           name="c_name"
                           x-model="courseName"
                           maxlength="50"
                           @input="courseNameError = false"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md pr-14 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100">

                    <span class="absolute text-sm text-gray-500 -translate-y-1/2 top-1/2 right-3"
                          x-text="`${courseName.length} / 50`"></span>

                    <span class="absolute left-0 text-sm text-red-600 -bottom-5"
                          x-show="courseNameError">
                          กรุณากรอกชื่อคอร์ส
                    </span>
                </div>
            </div>

            <!-- คำอธิบาย -->
            <div class="relative gap-1">
                <label class="block mb-1 text-base-content">คำอธิบายคอร์สอบรม</label>
                <div class="relative">
                    <textarea name="c_description"
                              x-model="desc"
                              rows="4"
                              maxlength="200"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md resize-none pr-14 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100"></textarea>

                    <span class="absolute text-sm text-gray-500 bottom-2 right-3"
                          x-text="`${desc.length} / 200`"></span>
                </div>
            </div>

            <!-- เลือกทักษะ -->
            <div class="relative gap-1">
                <label class="block mb-1 font-normal text-base-content">ประเภททักษะ</label>

                <div class="flex flex-wrap gap-1 mb-2">
                    <template x-for="(skill,index) in selectedSkills" :key="index">
                        <span class="px-3 py-1 border border-gray-400 rounded-full text-base-content"
                              :class="index % 2 === 0 ? 'bg-blue-200' : 'bg-base-200'">
                            <span x-text="skill"></span>
                            <button type="button"
                                    @click="removeSkill(index)"
                                    class="ml-1">✕</button>
                        </span>
                    </template>

                    <template x-if="selectedSkills.length === 0">
                        <span class="px-3 py-1 border border-gray-400 rounded-full bg-base-200 text-base-content">
                            ยังไม่มีข้อมูล
                        </span>
                    </template>
                </div>

                <select x-model="currentSkill"
                        @change="addSkill"
                        class="w-full px-3 py-2 font-normal border border-gray-300 rounded-md focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100">
                    <option value="">เลือกทักษะ</option>
                    @foreach($skills as $skill)
                        <option value="{{ $skill->name }}">{{ $skill->name }}</option>
                    @endforeach
                </select>

                <input type="hidden" name="skills" :value="selectedSkills.join(',')">

                <span class="absolute left-0 text-sm text-red-600 -bottom-5"
                      x-show="skillError">
                      กรุณาเลือกอย่างน้อย 1 ทักษะ
                </span>
            </div>

            <!-- รหัสคอร์ส + Toggle เผยแพร่ -->
            <div class="flex items-start gap-6">

                <!-- รหัสคอร์ส -->
                <div class="flex flex-col w-1/2 gap-1">
                    <label class="block mb-1 text-base-content">รหัสคอร์สอบรม</label>
                    <input type="text"
                           name="c_code"
                           x-model="courseCode"
                           class="w-full px-3 py-2 text-gray-600 bg-gray-300 border border-gray-300 rounded-md cursor-not-allowed focus:outline-none"
                           readonly>
                </div>

                <!-- Toggle เผยแพร่คอร์สอบรม -->
                <div class="flex flex-col w-1/2 gap-1">
                    <label class="mb-1 text-base-content">เผยแพร่คอร์สอบรม</label>
                    <div class="flex items-center h-full">
                        <div x-data="{ published: false }"
                             @click="published = !published"
                             :class="published ? 'bg-blue-600' : 'bg-gray-300'"
                             class="relative transition-colors rounded-full cursor-pointer w-14 h-7">

                            <span class="absolute w-5 h-5 transition-transform bg-white rounded-full shadow-md left-1 top-1"
                                  :class="published ? 'translate-x-7' : 'translate-x-0'"></span>

                            <input type="hidden"
                                   name="c_status"
                                   :value="published ? 'open' : 'draft'">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ปุ่ม Action -->
        <div class="flex justify-center col-span-1 gap-4 mt-10 md:col-span-2">
            <button type="submit"
                    class="px-6 py-3 text-white bg-blue-600 rounded-lg btn hover:bg-blue-700">
                สร้าง
            </button>
            <a href="{{ route('courses.index') }}"
               class="px-6 py-3 rounded-lg btn btn-outline text-base-content">
                ยกเลิก
            </a>
        </div>
    </form>
</div>

<script>
function courseForm() {
    return {
        previewUrl: null,
        courseName: '',
        desc: '',
        selectedSkills: [],
        currentSkill: '',
        courseCode: '',
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
            if (this.selectedSkills.length === 0) {
                this.skillError = true;
            }
            this.generateCode();
        },

        generateCode() {
            if (this.selectedSkills.length > 0) {
                let skillAbbr = this.selectedSkills[0].substring(0, 3).toUpperCase();
                let chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let code = '';

                do {
                    code = skillAbbr + '-';
                    for (let i = 0; i < 6; i++) {
                        code += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                } while (this.existingCodes && this.existingCodes.includes(code));

                this.courseCode = code;
            } else {
                this.courseCode = '';
            }
        },

        validateForm() {
            this.courseNameError = this.courseName.trim() === '';
            this.skillError = this.selectedSkills.length === 0;

            if (!this.courseNameError && !this.skillError) {
                this.$el.submit();
            }
        }
    }
}
</script>
@endsection
