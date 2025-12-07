@extends('layouts.app')

@section('title', 'จัดการคำถาม')

@section('content')
<div class="bg-base-200 p-6 rounded-lg shadow max-w-4xl mx-auto">
    <div class="flex justify-center mb-6">
        <h2 class="text-2xl font-semibold">จัดการคำถาม</h2>
    </div>
    <div class="mb-6">
        <p class="text-gray-600">แบบทดสอบ: {{ $exam->e_name }}</p>
    </div>

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

    <!-- ส่วนเพิ่มคำถามใหม่ -->
    <div class="bg-white rounded-lg p-6 shadow mb-6" x-data="questionManager()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold">เพิ่มคำถามใหม่</h3>
            <!-- ปุ่มเพิ่มคำถามเมื่อยังไม่มีคำถาม -->
            <button type="button" @click="addQuestion()" 
                    x-show="questions.length === 0"
                    class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                <i class="fa-solid fa-plus mr-1"></i>
                เพิ่มคำถาม
            </button>
        </div>

        <!-- ฟอร์มเพิ่มคำถาม -->
        <form action="{{ route('questions.store') }}" method="POST" x-show="questions.length > 0">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->e_id }}">
            
            <div class="space-y-4">
                <template x-for="(question, index) in questions" :key="question.id">
                    <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium" x-text="`คำถามใหม่ที่ ${index + 1}`"></h4>
                            <button type="button" @click="removeQuestion(index)" 
                                    class="text-red-600 hover:text-red-800">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">คำถาม</label>
                            <textarea x-model="question.q_question" 
                                    :name="`questions[${index}][q_question]`"
                                    rows="2" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100 resize-none"
                                    placeholder="กรอกคำถาม..." 
                                    required></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <template x-for="(answer, answerIndex) in ['q_answer1', 'q_answer2', 'q_answer3', 'q_answer4']" :key="answerIndex">
                                <div class="flex items-center gap-2">
                                    <input type="radio" 
                                        :name="`questions[${index}][q_correct_answer]`"
                                        :value="answerIndex + 1"
                                        x-model="question.q_correct_answer"
                                        class="text-blue-600 focus:ring-green-500" 
                                        required>
                                    
                                    <input type="text" 
                                        x-model="question[answer]"
                                        :name="`questions[${index}][${answer}]`"
                                        :placeholder="`ตัวเลือกที่ ${answerIndex + 1}`"
                                        class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100"
                                        required>
                                </div>
                            </template>
                        </div>

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

                <!-- ปุ่มจัดการคำถามใหม่ -->
                <div class="flex justify-center relative mt-6">
                    <button type="button" @click="addQuestion()" 
                            class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                        <i class="fa-solid fa-plus mr-1"></i>
                        เพิ่มคำถามใหม่
                    </button>
                    
                    <div class="absolute right-0 flex gap-2">
                        <button type="submit" 
                                class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                            บันทึก
                        </button>
                        <button type="button" @click="questions = []"
                                class="px-3 py-1 text-base-content rounded-lg hover:bg-gray-200 text-sm">
                            ยกเลิก
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div x-show="questions.length === 0" 
             class="text-center py-6 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
            <i class="fa-solid fa-question-circle text-3xl mb-2 text-gray-400"></i>
            <p>กดปุ่ม "เพิ่มคำถาม" เพื่อเริ่มเพิ่มคำถามใหม่</p>
        </div>
    </div>

    <!-- รายการคำถามที่มีอยู่ -->
    <div class="bg-white rounded-lg p-6 shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">คำถามที่มีอยู่ ({{ $exam->questions->count() }} ข้อ)</h3>
        </div>

        @if($exam->questions && $exam->questions->count() > 0)
            <div class="space-y-4">
                @foreach($exam->questions as $question)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50" x-data="{ editing: false, editData: {} }">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="font-medium text-gray-800">{{ $loop->iteration }}. {{ $question->q_question }}</h4>
                            <div class="flex gap-3">
                                <button type="button" @click="editing = !editing; editData = editing ? {
                                    q_question: '{{ addslashes($question->q_question) }}',
                                    q_answer1: '{{ addslashes($question->q_answer1) }}',
                                    q_answer2: '{{ addslashes($question->q_answer2) }}',
                                    q_answer3: '{{ addslashes($question->q_answer3) }}',
                                    q_answer4: '{{ addslashes($question->q_answer4) }}',
                                    q_correct_answer: '{{ $question->q_correct_answer }}'
                                } : {}"
                                        class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    <i class="fa-solid fa-edit mr-1"></i>
                                    <span x-text="editing ? 'ยกเลิก' : 'แก้ไข'"></span>
                                </button>
                                <form action="{{ route('questions.destroy', $question->q_id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('คุณต้องการลบคำถามนี้หรือไม่?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">
                                        <i class="fa-solid fa-trash text-red-600 hover:text-red-800 mr-1"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- แสดงคำถามปกติ -->
                        <div x-show="!editing">
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

                        <!-- ฟอร์มแก้ไขแยกกัน -->
                        <div x-show="editing">
                            <form action="{{ route('questions.update', $question->q_id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">คำถาม</label>
                                    <textarea x-model="editData.q_question" 
                                            name="q_question"
                                            rows="2" 
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100 resize-none"
                                            required></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="q_correct_answer" value="1" 
                                            :checked="editData.q_correct_answer == 1"
                                            class="text-green-600 focus:ring-green-500" required>
                                        <input type="text" x-model="editData.q_answer1" name="q_answer1"
                                            placeholder="ตัวเลือกที่ 1"
                                            class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="q_correct_answer" value="2" 
                                            :checked="editData.q_correct_answer == 2"
                                            class="text-green-600 focus:ring-green-500" required>
                                        <input type="text" x-model="editData.q_answer2" name="q_answer2"
                                            placeholder="ตัวเลือกที่ 2"
                                            class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="q_correct_answer" value="3" 
                                            :checked="editData.q_correct_answer == 3"
                                            class="text-green-600 focus:ring-green-500" required>
                                        <input type="text" x-model="editData.q_answer3" name="q_answer3"
                                            placeholder="ตัวเลือกที่ 3"
                                            class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="q_correct_answer" value="4" 
                                            :checked="editData.q_correct_answer == 4"
                                            class="text-green-600 focus:ring-green-500" required>
                                        <input type="text" x-model="editData.q_answer4" name="q_answer4"
                                            placeholder="ตัวเลือกที่ 4"
                                            class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring focus:ring-blue-100"
                                            required>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2">
                                    <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                        บันทึก
                                    </button>
                                    <button type="button" @click="editing = false" class="px-3 py-1 text-base-content rounded-lg hover:bg-gray-200 text-sm">
                                        ยกเลิก
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <i class="fa-solid fa-question-circle text-4xl mb-3 text-gray-400"></i>
                <p class="text-lg mb-2">ยังไม่มีคำถามในแบบทดสอบนี้</p>
                <p class="text-sm">เริ่มเพิ่มคำถามแรกของคุณด้านบน</p>
            </div>
        @endif
    </div>

    <!-- ปุ่มกลับ -->
    <div class="flex justify-center mt-6">
        <button type="button" onclick="window.location='{{ route('courses.show', ['id' => $exam->e_c_id]) }}'"
                class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            กลับไปยังคอร์ส
        </button>
    </div>
</div>

<script>
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
</script>
@endsection