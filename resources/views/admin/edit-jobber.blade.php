@extends('layouts.app')

@section('title', 'จัดการโปรไฟล์')

@section('content')

@php
    $resumes = $resumes ?? collect(); // ป้องกัน Undefined variable
@endphp

<div class="p-4 overflow-x-auto border shadow bg-base-200 rounded-2xl">

    <form method="POST"
        action="{{ auth()->user()->role === 'admin'
            ? route('profile-details.store', $targetUserId ?? auth()->id())
            : route('profile-jobber.store') }}">
        @csrf
        <div>
            @if ($errors->any())
                <ul class="mt-3 text-sm text-red-600 list-disc list-inside">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif

            {{-- ================= ข้อมูลส่วนตัว (user_profiles) ================= --}}
            <div class="flex justify-between">
                <div class="w-full">
                    <h1>โปรไฟล์</h1>
                    <p>จัดการข้อมูลบัญชีและการตั้งค่าของคุณ</p>
                </div>
                <div class="flex flex-col justify-end w-full gap-4">
                    <div class="flex justify-end gap-4">
                        <fieldset class="fieldset">
                            <legend class="mb-1 fieldset-legend">คำนำหน้า</legend>
                            <select name="up_prefix" class="pl-2 border border-gray-300 select w-72">
                                <option value="">-- เลือกคำนำหน้า --</option>
                                <option value="นาย" {{ old('up_prefix', $profile->up_prefix ?? '') == 'นาย' ? 'selected' : '' }}>นาย</option>
                                <option value="นาง" {{ old('up_prefix', $profile->up_prefix ?? '') == 'นาง' ? 'selected' : '' }}>นาง</option>
                                <option value="นางสาว" {{ old('up_prefix', $profile->up_prefix ?? '') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                <option value="เด็กชาย" {{ old('up_prefix', $profile->up_prefix ?? '') == 'เด็กชาย' ? 'selected' : '' }}>เด็กชาย</option>
                                <option value="เด็กหญิง" {{ old('up_prefix', $profile->up_prefix ?? '') == 'เด็กหญิง' ? 'selected' : '' }}>เด็กหญิง</option>
                                <option value="อื่นๆ" {{ old('up_prefix', $profile->up_prefix ?? '') == 'อื่นๆ' ? 'selected' : '' }}>อื่นๆ</option>
                            </select>
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="mb-1 fieldset-legend">ชื่อ-นามสกุล</legend>
                            <input value="{{ old('up_name', $profile->up_name ?? '') }}" type="text" name="up_name"
                                class="pl-2 border border-gray-300 input w-72" placeholder="ชื่อ-นามสกุล" />
                        </fieldset>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-10">
                <button type="submit" class="px-6 py-2 text-white bg-blue-600 rounded-lg shadow">
                    บันทึกข้อมูล
                </button>
            </div>
        </div>
    </form>
    {{-- ================= แสดงเรซูเม่ ================= --}}
    <div class="mt-12">
        <h2 class="text-lg font-semibold mb-4">เรซูเม่ของคุณ</h2>

        @if($resumes->isEmpty())
            <p class="text-gray-500">ยังไม่สร้างเรซูเม่ โปรดสร้างเรซูเม่ของคุณ</p>
        @else
            <div class="space-y-4">
                @foreach($resumes as $resume)
                    <div class="p-4 border rounded-lg bg-base-100 flex justify-between items-center">
                        <div>
                            <p class="font-semibold">{{ $resume->title ?? 'เรซูเม่ของคุณ' }}</p>
                            <p class="text-sm text-gray-500">สถานะ: 
                                @if($resume->is_published)
                                    <span class="text-green-600">เผยแพร่แล้ว</span>
                                @else
                                    <span class="text-red-600">ยังไม่เผยแพร่</span>
                                @endif
                            </p>
                        </div>
                        <a href="#" class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            ดูเรซูเม่
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- สคริปต์เก็บไว้ทั้งหมด --}}
<script>
    // Helper: attach validation between a start and end date inputs
    function attachDatePairValidation(startInput, endInput) {
        if (!startInput || !endInput) return;

        const validate = () => {
            if (startInput.value && endInput.value && endInput.value < startInput.value) {
                endInput.setCustomValidity('วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่ม');
            } else {
                endInput.setCustomValidity('');
            }
        };

        const syncMin = () => {
            if (startInput.value) {
                endInput.min = startInput.value;
            } else {
                endInput.removeAttribute('min');
            }
            validate();
        };

        startInput.addEventListener('input', syncMin);
        endInput.addEventListener('input', validate);

        // Initialize constraints on load
        syncMin();
    }

    // Initialize validation for existing rows on page load
    function initExistingDateValidation() {
        // Educations
        document.querySelectorAll('#education-container .relative').forEach(row => {
            const s = row.querySelector('input[name$="[ed_start_date]"]');
            const e = row.querySelector('input[name$="[ed_end_date]"]');
            attachDatePairValidation(s, e);
        });
        // Work experiences
        document.querySelectorAll('#work-container .relative').forEach(row => {
            const s = row.querySelector('input[name$="[we_start_date]"]');
            const e = row.querySelector('input[name$="[we_end_date]"]');
            attachDatePairValidation(s, e);
        });
    }

    initExistingDateValidation();

    function createRow(type, index) {
        const row = document.createElement('div');
        row.classList.add('relative');

        if (type === 'education') {
            row.innerHTML = `...`; // keep your original education row template
        }

        if (type === 'work') {
            row.innerHTML = `...`; // keep your original work row template
        }

        row.querySelector('.delete-row').addEventListener('click', () => row.remove());

        // Attach date validation for the newly created row
        if (type === 'education') {
            const s = row.querySelector(`input[name="educations[${index}][ed_start_date]"]`);
            const e = row.querySelector(`input[name="educations[${index}][ed_end_date]"]`);
            attachDatePairValidation(s, e);
        }
        if (type === 'work') {
            const s = row.querySelector(`input[name="work_experiences[${index}][we_start_date]"]`);
            const e = row.querySelector(`input[name="work_experiences[${index}][we_end_date]"]`);
            attachDatePairValidation(s, e);
        }
        return row;
    }

    document.getElementById('add-education')?.addEventListener('click', () => {
        const container = document.getElementById('education-container');
        const index = container.querySelectorAll('.relative').length;
        container.insertBefore(createRow('education', index), document.getElementById('add-education'));
    });

    document.getElementById('add-work')?.addEventListener('click', () => {
        const container = document.getElementById('work-container');
        const index = container.querySelectorAll('.relative').length;
        container.insertBefore(createRow('work', index), document.getElementById('add-work'));
    });

    // ลบ Education
    document.querySelectorAll('.delete-education').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (confirm('คุณต้องการลบข้อมูลนี้ใช่หรือไม่?')) {
                fetch(`/admin/profile/education/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.relative').remove();
                        } else {
                            alert(data.error || 'เกิดข้อผิดพลาด');
                        }
                    });
            }
        });
    });

    // ลบ Work
    document.querySelectorAll('.delete-work').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (confirm('คุณต้องการลบข้อมูลนี้ใช่หรือไม่?')) {
                fetch(`/admin/profile/work/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.relative').remove();
                        } else {
                            alert(data.error || 'เกิดข้อผิดพลาด');
                        }
                    });
            }
        });
    });
</script>

@endsection
