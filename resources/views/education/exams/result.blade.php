@extends('layouts.app')

@section('title', 'ผลการทดสอบ - ' . $exam->e_name)

@section('content')
<div class="bg-base-200 p-6 rounded-lg shadow max-w-4xl mx-auto">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-semibold text-gray-800">ผลการทดสอบ</h2>
        <h3 class="text-xl text-gray-600 mt-2">{{ $exam->e_name }}</h3>
    </div>

    <div class="bg-white rounded-lg p-8 shadow text-center">
        <div class="mb-6">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full {{ $passed ? 'bg-green-100' : 'bg-red-100' }} mb-4">
                <i class="fa-solid {{ $passed ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }} text-4xl"></i>
            </div>
            
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-600">เกณฑ์ผ่าน:</span>
                <span class="font-semibold">อย่างน้อย {{ $required }} ข้อ</span>
            </div>
            
            <h3 class="text-2xl font-bold {{ $passed ? 'text-green-600' : 'text-red-600' }} mb-2">
                {{ $percentage >= 60 ? 'ผ่าน' : 'ไม่ผ่าน' }}
            </h3>
        </div>

        <div class="space-y-4 mb-8">
            <div class="flex justify-between items-center py-2 border-b">
                <span class="text-gray-600">คะแนนที่ได้:</span>
                <span class="font-semibold text-lg">{{ $score }} / {{ $totalQuestions }}</span>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b">
                <span class="text-gray-600">เปอร์เซ็นต์:</span>
                <span class="font-semibold text-lg {{ $passed ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($percentage, 1) }}%
                </span>
            </div>
            
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-600">เกณฑ์ผ่าน:</span>
                <span class="font-semibold">60%</span>
            </div>
        </div>

        @if($percentage >= 60)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-green-800">
                    <i class="fa-solid fa-trophy mr-2"></i>
                    ยินดีด้วย! คุณผ่านการทดสอบแล้ว
                </p>
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-800">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    คุณยังไม่ผ่านการทดสอบ กรุณาลองใหม่อีกครั้ง
                </p>
            </div>
        @endif

        <div class="flex justify-center gap-4">
             @if(!$passed)
                <a href="{{ route('exams.take', $exam->e_id) }}" 
                   class="hidden px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fa-solid fa-redo mr-2"></i>
                    ทำแบบทดสอบอีกครั้ง
                </a>
            @endif

            <a href="{{ route('exams.take', $exam->e_id) . '?retake=1' }}" 
               class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fa-solid fa-redo mr-2"></i>
                ทำแบบทดสอบอีกครั้ง
            </a>

            <a href="{{ route('courses.view', $exam->e_c_id) }}" 
               class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                กลับไปยังคอร์ส
            </a>
        </div>
    </div>
</div>
@endsection
