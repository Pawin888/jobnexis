@extends('layouts.app')

@section('title', 'Edit Resume')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">แก้ไขเรซูเม่</h1>
                <p class="text-gray-600 mt-2">อัปเดตข้อมูลเรซูเม่ของคุณ</p>
            </div>
            <a href="{{ route('profile-jobber.edit') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                กลับไปหน้าโปรไฟล์
            </a>
        </div>
    </div>

    <form id="resume-form" method="POST" action="{{ route('jobber.resumes.update', $resume->id) }}" enctype="multipart/form-data" novalidate>
        @include('jobber.resumes._form')
    </form>

    <div class="mt-8 flex justify-end items-center bg-white p-6 rounded-lg shadow-sm border">
        <div class="flex gap-3">
            <button type="button" id="delete-btn" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    ลบเรซูเม่
                </span>
            </button>
            <button type="submit" form="resume-form" id="submit-btn" class="px-8 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    บันทึกการแก้ไข
                </span>
            </button>
        </div>
    </div>

    {{-- Hidden Delete Form --}}
    <form id="delete-form" method="POST" action="{{ route('jobber.resumes.destroy', $resume) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    {{-- Custom Delete Confirmation Modal --}}
    <div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50" style="display:none">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full mx-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">ยืนยันการลบเรซูเม่</h3>
                    <p class="text-sm text-gray-500">การกระทำนี้ไม่สามารถย้อนกลับได้</p>
                </div>
            </div>
            <p class="text-gray-600 mb-6">คุณแน่ใจหรือว่าต้องการลบเรซูเม่นี้? ข้อมูลทั้งหมดรวมถึงทักษะ ประสบการณ์ และไฟล์แนบจะถูกลบออกอย่างถาวร</p>
            <div class="flex gap-3 justify-end">
                <button type="button" id="cancel-delete" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    ยกเลิก
                </button>
                <button type="button" id="confirm-delete" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                    ยืนยันการลบ
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Reset form elements */
    input, select, textarea {
        margin: 0;
        font-family: inherit;
        font-size: inherit;
        line-height: inherit;
    }

    select {
        text-transform: none;
    }

    .tab-button {
        position: relative;
        padding: 1rem 1.5rem;
        font-weight: 500;
        color: #6B7280;
        transition: all 0.3s;
        border-bottom: 3px solid transparent;
    }

    .tab-button:hover {
        color: #2563EB;
        background-color: #EFF6FF;
    }

    .tab-button.active {
        color: #2563EB;
        border-bottom-color: #2563EB;
        background-color: #EFF6FF;
    }

    .tab-button.completed {
        color: #059669;
    }

    .tab-button.completed::after {
        content: '✓';
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background-color: #10B981;
        color: white;
        border-radius: 50%;
        width: 1.25rem;
        height: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .input {
        width: 100% !important;
        padding: 0.75rem !important;
        border: 1px solid #D1D5DB !important;
        border-radius: 0.5rem !important;
        transition: all 0.2s !important;
        font-size: 1rem !important;
        line-height: 1.5rem !important;
        height: auto !important;
        min-height: 2.75rem !important;
        color: #1F2937 !important;
        background-color: #FFFFFF !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        display: block !important;
        vertical-align: middle !important;
    }

    select.input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E") !important;
        background-position: right 0.5rem center !important;
        background-repeat: no-repeat !important;
        background-size: 1.5em 1.5em !important;
        padding-right: 2.5rem !important;
        cursor: pointer !important;
    }

    .input:focus {
        outline: none !important;
        border-color: #2563EB !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
    }

    .input::placeholder {
        color: #9CA3AF !important;
    }

    .input:disabled {
        background-color: #F3F4F6 !important;
        color: #9CA3AF !important;
        cursor: not-allowed !important;
    }

    select.input option {
        font-size: 1rem !important;
        padding: 0.75rem !important;
        color: #1F2937 !important;
        line-height: 1.5rem !important;
        height: auto !important;
    }

    input[type="text"].input,
    input[type="email"].input,
    input[type="date"].input,
    input[type="number"].input,
    input[type="file"].input {
        height: 2.75rem !important;
    }

    textarea.input {
        resize: vertical !important;
        min-height: 100px !important;
        padding: 0.75rem !important;
    }

    .section-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }
</style>

<script>
    // Delete modal
    const deleteModal = document.getElementById('delete-modal');

    document.getElementById('delete-btn').addEventListener('click', function() {
        deleteModal.style.display = 'flex';
    });

    document.getElementById('cancel-delete').addEventListener('click', function() {
        deleteModal.style.display = 'none';
    });

    document.getElementById('confirm-delete').addEventListener('click', function() {
        document.getElementById('delete-form').submit();
    });

    // Close modal when clicking the backdrop
    deleteModal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
</script>
@endsection
