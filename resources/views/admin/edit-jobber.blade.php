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
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold tracking-tight text-gray-800">เรซูเม่ของคุณ</h2>
        </div>

        @if ($resumes->isEmpty())
            <div class="p-10 text-center bg-white border border-dashed rounded-2xl">
                <p class="mb-4 text-gray-500">ยังไม่สร้างเรซูเม่</p>
                <a href="{{ route('jobber.resumes.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">
                    สร้างเรซูเม่
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($resumes as $resume)
                    @php
                        $resumeSkills = $resume->resumeSkills ?? collect();
                        $workExperiences = $resume->workExperiences ?? collect();
                        $educations = $resume->educations ?? collect();
                        $certificates = $resume->certificates ?? collect();
                        $languages = $resume->languages ?? collect();

                        $profileImageUrl = null;
                        if (!empty($resume->profile_image)) {
                            if (\Illuminate\Support\Str::startsWith($resume->profile_image, ['http://', 'https://'])) {
                                $profileImageUrl = $resume->profile_image;
                            } else {
                                $imgPath = preg_replace('#^/?storage/#', '', $resume->profile_image);
                                $profileImageUrl = \Illuminate\Support\Facades\Storage::url($imgPath);
                            }
                        }

                        $genderLabels = ['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ'];
                        $skillLevelLabels = ['beginner' => 'เริ่มต้น', 'intermediate' => 'ปานกลาง', 'advanced' => 'ขั้นสูง', 'expert' => 'ผู้เชี่ยวชาญ'];
                        $languageLevelLabels = ['basic' => 'พื้นฐาน', 'conversational' => 'สนทนาได้', 'fluent' => 'คล่องแคล่ว', 'native' => 'เจ้าของภาษา'];

                        $fullName = trim(($resume->first_name ?? '') . ' ' . ($resume->middle_name ?? '') . ' ' . ($resume->last_name ?? ''));
                        $birthDateObj = $resume->birth_date ? \Carbon\Carbon::parse($resume->birth_date) : null;
                        $birthDate = $birthDateObj ? $birthDateObj->format('d/m/Y') : '-';
                        $ageText = '';
                        if ($birthDateObj) {
                            $now = \Carbon\Carbon::now();
                            $diff = $birthDateObj->diff($now);
                            if ($diff->y > 0) {
                                $ageText = $diff->y . ' ปี';
                            }
                        }
                        $availableStartDate = $resume->available_start_date ? \Carbon\Carbon::parse($resume->available_start_date)->format('d/m/Y') : '-';
                        $salaryMin = $resume->salary_min ?? null;
                        $salaryMax = $resume->salary_max ?? null;
                        $salaryText = '-';
                        if ($salaryMin && $salaryMax) {
                            $salaryText = number_format($salaryMin) . ' - ' . number_format($salaryMax) . ' บาท';
                        } elseif ($salaryMin) {
                            $salaryText = number_format($salaryMin) . ' บาทขึ้นไป';
                        } elseif ($salaryMax) {
                            $salaryText = 'ไม่เกิน ' . number_format($salaryMax) . ' บาท';
                        }
                    @endphp

                    <details class="overflow-hidden bg-white border shadow-sm group rounded-2xl" open>
                        <summary class="flex items-center justify-between gap-4 p-5 cursor-pointer bg-gradient-to-r from-slate-50 to-white">
                            <div class="flex items-center gap-4 min-w-0">
                                @if ($profileImageUrl)
                                    <img src="{{ $profileImageUrl }}" alt="Profile"
                                         class="object-cover w-14 h-14 border-2 border-gray-200 rounded-full" />
                                @else
                                    <div class="flex items-center justify-center w-14 h-14 text-gray-400 bg-gray-100 border-2 border-gray-200 rounded-full">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <h3 class="text-lg font-bold text-gray-800 truncate">{{ $fullName ?: '-' }}</h3>
                                    <div class="flex gap-2 mt-2">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $resume->is_visible ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                            {{ $resume->is_visible ? 'เปิดเผยเรซูเม่' : 'ซ่อนเรซูเม่' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <svg class="w-5 h-5 text-gray-400 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>

                        <div class="p-5 space-y-5 border-t">

    {{-- เกี่ยวกับตัวคุณ --}}
    <div class="flex flex-col items-center text-center py-4">
    @if ($profileImageUrl)
        <img src="{{ $profileImageUrl }}" alt="Profile"
             class="object-cover w-20 h-20 border-2 border-gray-200 rounded-full mb-3" />
    @else
        <div class="flex items-center justify-center w-20 h-20 text-gray-400 bg-gray-100 border-2 border-gray-200 rounded-full mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
    @endif
    <h3 class="text-lg font-bold text-gray-800">{{ $fullName ?: '-' }}</h3>
    <p class="text-sm leading-relaxed text-gray-500 mt-1 max-w-lg text-justify">{{ $resume->summary ?: 'ไม่มีข้อมูล' }}</p>
</div>

    {{-- ข้อมูลส่วนตัว / ข้อมูลการสมัครงาน --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="p-4 border rounded-xl">
        <h4 class="mb-2 font-semibold text-gray-800">ข้อมูลส่วนตัว</h4>
        <div class="space-y-1 text-sm">
            <div><span class="text-gray-500">อีเมล:</span> {{ $resume->email ?? '-' }}</div>
            <div><span class="text-gray-500">เบอร์โทร:</span> {{ $resume->phone ?? '-' }}</div>
            <div>
                <span class="text-gray-500">วันเกิด:</span> {{ $birthDate }}
                @if($ageText)
                    <span class="text-xs text-gray-400">({{ $ageText }})</span>
                @endif
            </div>
            <div><span class="text-gray-500">เพศ:</span> {{ $genderLabels[$resume->gender ?? ''] ?? '-' }}</div>
        </div>
    </div>

    <div class="p-4 border rounded-xl">
        <h4 class="mb-2 font-semibold text-gray-800">ข้อมูลการสมัครงาน</h4>
        <div class="space-y-1 text-sm">
            <div><span class="text-gray-500">เริ่มงานได้:</span> {{ $availableStartDate }}</div>
            <div><span class="text-gray-500">สถานที่ต้องการ:</span> {{ $resume->preferred_location ?? '-' }}</div>
            <div><span class="text-gray-500">เงินเดือนคาดหวัง:</span> {{ $salaryText }}</div>
        </div>
    </div>
</div>

    {{-- ทักษะ / ภาษา --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="p-4 border rounded-xl">
            <h4 class="mb-2 font-semibold">ทักษะ</h4>
            @forelse ($resumeSkills as $rs)
                <div class="py-1 text-sm">
                    <span class="font-medium">{{ $rs->skill->name ?? $rs->skill_name ?? 'ทักษะ' }}</span>
                    @if ($rs->proficiency_level)
                        <span class="text-blue-700">- {{ $skillLevelLabels[$rs->proficiency_level] ?? $rs->proficiency_level }}</span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">ไม่มีข้อมูล</p>
            @endforelse
        </div>

        <div class="p-4 border rounded-xl">
            <h4 class="mb-2 font-semibold">ภาษา</h4>
            @forelse ($languages as $lang)
                <div class="py-1 text-sm">
                    {{ $lang->language ?: '-' }}
                    <span class="text-blue-700">- {{ $languageLevelLabels[$lang->level ?? ''] ?? ($lang->level ?: '-') }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">ไม่มีข้อมูล</p>
            @endforelse
        </div>
    </div>

    {{-- ประสบการณ์ / การศึกษา / ใบรับรอง --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="p-4 border rounded-xl">
            <h4 class="mb-2 font-semibold">ประสบการณ์ทำงาน</h4>
            @forelse ($workExperiences as $we)
                @php
                    $start = $we->start_date ? \Carbon\Carbon::parse($we->start_date) : null;
                    $end = $we->is_current ? \Carbon\Carbon::now() : ($we->end_date ? \Carbon\Carbon::parse($we->end_date) : null);
                    $durationText = '-';
                    if ($start && $end) {
                        $diff = $start->diff($end);
                        if ($diff->y > 0) {
                            $durationText = $diff->y . ' ปี';
                            if ($diff->m > 0) $durationText .= ' ' . $diff->m . ' เดือน';
                        } elseif ($diff->m > 0) {
                            $durationText = $diff->m . ' เดือน';
                        } else {
                            $durationText = 'น้อยกว่า 1 เดือน';
                        }
                    }
                @endphp
                <div class="flex items-start gap-3 py-2 border-b last:border-0">
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium text-gray-800">
                            {{ $we->job_title ?: '-' }}
                            <span class="font-normal text-gray-500">@ {{ $we->company_name ?: '-' }}</span>
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $we->start_date ?: '-' }} – {{ $we->is_current ? 'ปัจจุบัน' : ($we->end_date ?: '-') }}
                            <span class="text-blue-500 ml-1">· {{ $durationText }}</span>
                        </div>
                        @if (!empty($we->description))
                            <div class="mt-0.5 text-xs text-gray-500 line-clamp-1">{{ $we->description }}</div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">ไม่มีข้อมูล</p>
            @endforelse
        </div>

        <div class="p-4 border rounded-xl">
            <h4 class="mb-2 font-semibold">การศึกษา</h4>
            @forelse ($educations as $ed)
                @php
                    $startYear = $ed->start_year ? (int) $ed->start_year : null;
                    $endYear = $ed->end_year ? (int) $ed->end_year : null;
                    $studyDuration = ($startYear && $endYear && $endYear >= $startYear) ? ($endYear - $startYear + 1) : null;
                    $studyDurationText = $studyDuration ? $studyDuration . ' ปี' : '-';
                @endphp
                <div class="flex items-start gap-3 py-2 border-b last:border-0">
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium text-gray-800">
                            {{ $ed->education_level ?: '-' }}
                            <span class="font-normal text-gray-500">{{ $ed->field_of_study ? '· ' . $ed->field_of_study : '' }}</span>
                        </div>
                        <div class="text-xs text-gray-500">{{ $ed->institution ?: '-' }}</div>
                        <div class="text-xs text-gray-400">
                            {{ $ed->start_year ?: '-' }} – {{ $ed->end_year ?: 'ปัจจุบัน' }}
                            <span class="text-blue-500 ml-1">· {{ $studyDurationText }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">ไม่มีข้อมูล</p>
            @endforelse
        </div>

        <div class="p-4 border rounded-xl">
            <h4 class="mb-2 font-semibold">ใบรับรอง / ประกาศนียบัตร</h4>
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
                <div class="flex items-center gap-3 py-2 border-b last:border-0">
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium text-gray-800">{{ $cert->name ?: '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $cert->issued_by ?: '-' }} · {{ $cert->issued_year ?: '-' }}</div>
                    </div>
                    @if ($certUrl)
                        <a href="{{ $certUrl }}" target="_blank"
                           class="shrink-0 px-2.5 py-1 text-xs text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition">
                            ดูไฟล์
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">ไม่มีข้อมูล</p>
            @endforelse
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('jobber.resumes.edit', $resume->id) }}"
           class="px-4 py-2 text-sm text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">
            แก้ไข
        </a>
        <form id="form-delete-resume-{{ $resume->id }}"
              method="POST"
              action="{{ route('jobber.resumes.destroy', $resume->id) }}">
            @csrf
            @method('DELETE')
        </form>
        <button type="button"
            class="delete-resume-btn px-4 py-2 text-sm text-white transition bg-red-600 rounded-xl hover:bg-red-700"
            data-id="{{ $resume->id }}"
            data-name="{{ addslashes($fullName ?: 'เรซูเม่') }}">
            ลบ
        </button>
    </div>
</div>
                    </details>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if(session('swal_success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: '{{ session('swal_success') }}',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    @endif

    // ===== ลบเรซูเม่ =====
    document.querySelectorAll('.delete-resume-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const name = this.dataset.name;

            Swal.fire({
                title: 'ยืนยันการลบเรซูเม่',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fa-solid fa-trash" style="margin-right:6px"></i>ลบ',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById(`form-delete-resume-${id}`).submit();
                }
            });
        });
    });

    // ===== Date pair validation helpers =====
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
            if (startInput.value) { endInput.min = startInput.value; } else { endInput.removeAttribute('min'); }
            validate();
        };
        startInput.addEventListener('input', syncMin);
        endInput.addEventListener('input', validate);
        syncMin();
    }

    function initExistingDateValidation() {
        document.querySelectorAll('#education-container .relative').forEach(row => {
            attachDatePairValidation(
                row.querySelector('input[name$="[ed_start_date]"]'),
                row.querySelector('input[name$="[ed_end_date]"]')
            );
        });
        document.querySelectorAll('#work-container .relative').forEach(row => {
            attachDatePairValidation(
                row.querySelector('input[name$="[we_start_date]"]'),
                row.querySelector('input[name$="[we_end_date]"]')
            );
        });
    }

    initExistingDateValidation();

    function createRow(type, index) {
        const row = document.createElement('div');
        row.classList.add('relative');
        if (type === 'education') { row.innerHTML = `...`; }
        if (type === 'work') { row.innerHTML = `...`; }
        row.querySelector('.delete-row').addEventListener('click', () => row.remove());
        if (type === 'education') {
            attachDatePairValidation(
                row.querySelector(`input[name="educations[${index}][ed_start_date]"]`),
                row.querySelector(`input[name="educations[${index}][ed_end_date]"]`)
            );
        }
        if (type === 'work') {
            attachDatePairValidation(
                row.querySelector(`input[name="work_experiences[${index}][we_start_date]"]`),
                row.querySelector(`input[name="work_experiences[${index}][we_end_date]"]`)
            );
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
</script>
@endpush
