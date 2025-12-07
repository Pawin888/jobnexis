@extends('layouts.app')

@section('title', 'ทำแบบทดสอบ - ' . $exam->e_name)

@section('content')
<div class="bg-base-200 p-6 rounded-lg shadow max-w-4xl mx-auto">
    <div class="text-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">{{ $exam->e_name }}</h2>
        <p class="text-gray-600 mt-2">จำนวนคำถาม {{ $exam->questions->count() }} ข้อ</p>
        <p class="text-gray-600 mt-1">เกณฑ์ผ่าน: อย่างน้อย {{ $exam->pass_threshold ?? (int)ceil($exam->questions->count()*0.6) }} ข้อ</p>
    </div>

    @if($exam->questions->count() === 0)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
            <i class="fa-solid fa-exclamation-triangle mr-2"></i>
            แบบทดสอบนี้ยังไม่มีคำถาม
        </div>
        
        <div class="text-center mt-6">
            <a href="{{ route('courses.view', $exam->e_c_id) }}" 
               class="btn bg-gray-500 hover:bg-gray-600 text-white">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                กลับไปยังคอร์ส
            </a>
        </div>
    @else
        <form action="{{ route('exams.submit', $exam->e_id) }}" method="POST" id="exam-form">
            @csrf
            
            <div class="space-y-6">
                @foreach($exam->questions as $index => $question)
                    <div class="bg-white rounded-lg p-6 shadow border border-gray-200">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-800">
                                {{ $index + 1 }}. {{ $question->q_question }}
                            </h3>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="radio" 
                                       id="q{{ $question->q_id }}_1" 
                                       name="answers[{{ $question->q_id }}]" 
                                       value="1" 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                       required>
                                <label for="q{{ $question->q_id }}_1" class="ml-3 text-gray-700">
                                    1. {{ $question->q_answer1 }}
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="radio" 
                                       id="q{{ $question->q_id }}_2" 
                                       name="answers[{{ $question->q_id }}]" 
                                       value="2" 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                       required>
                                <label for="q{{ $question->q_id }}_2" class="ml-3 text-gray-700">
                                    2. {{ $question->q_answer2 }}
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="radio" 
                                       id="q{{ $question->q_id }}_3" 
                                       name="answers[{{ $question->q_id }}]" 
                                       value="3" 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                       required>
                                <label for="q{{ $question->q_id }}_3" class="ml-3 text-gray-700">
                                    3. {{ $question->q_answer3 }}
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="radio" 
                                       id="q{{ $question->q_id }}_4" 
                                       name="answers[{{ $question->q_id }}]" 
                                       value="4" 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                       required>
                                <label for="q{{ $question->q_id }}_4" class="ml-3 text-gray-700">
                                    4. {{ $question->q_answer4 }}
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-center gap-4 mt-8">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-3"
                        onclick="return confirm('คุณต้องการส่งคำตอบหรือไม่? กรุณาตรวจสอบคำตอบก่อนส่ง')">
                    ส่งคำตอบ
                </button>

                <a href="{{ route('courses.view', $exam->e_c_id) }}" 
                   class="text-base-content rounded-lg px-6 py-3 hover:bg-gray-200">
                    ยกเลิก
                </a>
            </div>
        </form>
    @endif
</div>

<script>
// ป้องกันการ refresh หน้า
window.addEventListener('beforeunload', function (e) {
    const formData = new FormData(document.getElementById('exam-form'));
    let hasAnswers = false;
    
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('answers[') && value) {
            hasAnswers = true;
            break;
        }
    }
    
    if (hasAnswers) {
        e.preventDefault();
        e.returnValue = 'คุณมีคำตอบที่ยังไม่ได้บันทึก คุณต้องการออกจากหน้านี้หรือไม่?';
    }
});
</script>
@endsection
