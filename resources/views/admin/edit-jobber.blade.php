@extends('layouts.app')

@section('title', 'จัดการโปรไฟล์')

@section('content')

@php
    $resumes = $resumes ?? collect();
@endphp

<div class="p-4 overflow-x-auto border shadow bg-base-200 rounded-2xl">

    {{-- ================= ฟอร์มโปรไฟล์ ================= --}}
    <form method="POST"
        action="{{ auth()->user()->role === 'admin'
            ? route('profile-details.store', $targetUserId ?? auth()->id())
            : route('profile-jobber.store') }}">
        @csrf

        @if ($errors->any())
            <ul class="mt-3 text-sm text-red-600 list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        @endif

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
                            @foreach (['นาย','นาง','นางสาว','เด็กชาย','เด็กหญิง','อื่นๆ'] as $prefix)
                                <option value="{{ $prefix }}"
                                    {{ old('up_prefix', $profile->up_prefix ?? '') == $prefix ? 'selected' : '' }}>
                                    {{ $prefix }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">ชื่อ-นามสกุล</legend>
                        <input
                            type="text"
                            name="up_name"
                            value="{{ old('up_name', $profile->up_name ?? '') }}"
                            class="pl-2 border border-gray-300 input w-72"
                            placeholder="ชื่อ-นามสกุล"
                        />
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-10">
            <button type="submit" class="px-6 py-2 text-white bg-blue-600 rounded-lg shadow">
                บันทึกข้อมูล
            </button>
        </div>
    </form>

    {{-- ================= แสดงเรซูเม่ ================= --}}
    <div class="mt-12">
        <h2 class="mb-4 text-lg font-semibold">เรซูเม่ของคุณ</h2>
            @if ($resumes->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-500 mb-4">ยังไม่สร้างเรซูเม่</p>
                    <a href="{{ route('jobber.resumes.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        สร้างเรซูเม่
                    </a>
                </div>
            @else

            <div class="space-y-4">
                @foreach ($resumes as $resume)
                    @php
                        $workExperiences = $resume->workExperiences ?? collect();
                        $educations      = $resume->educations ?? collect();
                        $certificates    = $resume->certificates ?? collect();
                        $languages       = $resume->languages ?? collect();
                        $resumeSkills    = $resume->resumeSkills ?? collect();
                        $profileImageUrl = null;
                        if (!empty($resume->profile_image)) {
                            if (\Illuminate\Support\Str::startsWith($resume->profile_image, ['http://', 'https://'])) {
                                $profileImageUrl = $resume->profile_image;
                            } else {
                                $profileImagePath = preg_replace('#^/?storage/#', '', $resume->profile_image);
                                $profileImageUrl = \Illuminate\Support\Facades\Storage::url($profileImagePath);
                            }
                        }
                    @endphp

                    <div class="p-6 space-y-6 border rounded-lg bg-base-100">

                        {{-- Header --}}
                        <div class="flex flex-col gap-4 lg:flex-row lg:justify-between">
                            <div class="flex items-center gap-4">
                                @if ($profileImageUrl)
                                    <img
                                        src="{{ $profileImageUrl }}"
                                        alt="Profile"
                                        class="h-16 w-16 rounded-full object-cover border"
                                    />
                                @endif

                                <div>
                                    <p class="text-lg font-semibold">{{ $resume->title ?? '-' }}</p>
                                    <p class="text-sm text-gray-500">
                                        สถานะ:
                                        @if ($resume->is_visible)
                                            <span class="text-green-600">เปิดเผย</span>
                                        @else
                                            <span class="text-red-600">ซ่อน</span>
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ trim(($resume->first_name ?? '') . ' ' . ($resume->last_name ?? '')) ?: '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-sm text-gray-600">
                                <p>อีเมล: {{ $resume->email ?? '-' }}</p>
                                <p>เบอร์โทร: {{ $resume->phone ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- ข้อมูลส่วนตัว --}}
                        <div>
                            <h3 class="mb-2 font-semibold">ข้อมูลส่วนตัว</h3>
                            <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                                <div>วันเกิด: {{ $resume->birth_date ?? '-' }}</div>
                                <div>เพศ: {{ $resume->gender ?? '-' }}</div>
                                <div>ที่อยู่: {{ $resume->address ?? '-' }}</div>
                                <div>สรุป: {{ $resume->summary ?? '-' }}</div>
                            </div>
                        </div>

                        {{-- ทักษะ --}}
                        <div>
                            <h3 class="mb-2 font-semibold">ทักษะ</h3>
                            @forelse ($resumeSkills as $rs)
                                <div class="text-sm">
                                    {{ $rs->skill->name ?? $rs->skill_name ?? 'ทักษะ' }}
                                    @if($rs->proficiency_level)
                                        - ระดับ {{ $rs->proficiency_level }}
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">ไม่มีข้อมูล</p>
                            @endforelse
                        </div>

                        {{-- ประสบการณ์ทำงาน --}}
                        <div>
                            <h3 class="mb-2 font-semibold">ประสบการณ์ทำงาน</h3>
                            @forelse ($workExperiences as $we)
                                <div class="text-sm">
                                    <div class="font-medium">{{ $we->job_title }} - {{ $we->company_name }}</div>
                                    <div class="text-gray-600">
                                        {{ $we->start_date }} ถึง {{ $we->is_current ? 'ปัจจุบัน' : $we->end_date }}
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">ไม่มีข้อมูล</p>
                            @endforelse
                        </div>

                        {{-- การศึกษา --}}
                        <div>
                            <h3 class="mb-2 font-semibold">การศึกษา</h3>
                            @forelse ($educations as $ed)
                                <div class="text-sm">
                                    {{ $ed->education_level }} - {{ $ed->institution }}
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">ไม่มีข้อมูล</p>
                            @endforelse
                        </div>

                        {{-- ใบรับรอง --}}
                        <div>
                            <h3 class="mb-2 font-semibold">ใบรับรอง</h3>
                            @forelse ($certificates as $cert)
                                @php
                                    $certPath = $cert->file_path ?? $cert->file ?? null;

                                    if ($certPath && \Illuminate\Support\Str::startsWith($certPath, ['http://', 'https://'])) {
                                        $certUrl = $certPath;
                                    } elseif ($certPath) {
                                        $certPath = preg_replace('#^/?storage/#', '', $certPath);
                                        $certUrl = \Illuminate\Support\Facades\Storage::url($certPath);
                                    } else {
                                        $certUrl = null;
                                    }
                                @endphp
                                <div class="text-sm">
                                    {{ $cert->name }} ({{ $cert->issued_by }})
                                    @if ($certUrl)
                                        <a href="{{ $certUrl }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            ดูไฟล์
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">ไม่มีข้อมูล</p>
                            @endforelse
                        </div>

                        {{-- ภาษา --}}
                        <div>
                            <h3 class="mb-2 font-semibold">ภาษา</h3>
                            @forelse ($languages as $lang)
                                <div class="text-sm">{{ $lang->language }} - {{ $lang->level }}</div>
                            @empty
                                <p class="text-sm text-gray-500">ไม่มีข้อมูล</p>
                            @endforelse
                        </div>
                        {{-- ปุ่มจัดการเรซูเม่ --}}
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <a href="{{ route('jobber.resumes.edit', $resume->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                แก้ไข
                            </a>
                            <form method="POST" action="{{ route('jobber.resumes.destroy', $resume->id) }}" onsubmit="return confirm('คุณต้องการลบเรซูเม่นี้หรือไม่? การลบจะไม่สามารถกู้คืนได้');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    ลบ
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection


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
