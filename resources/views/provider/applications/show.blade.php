@extends('layouts.app')

@section('title', 'รายละเอียดใบสมัคร')

@section('content')

@php
    $statusColor = [
        'reviewing' => 'text-amber-700 bg-amber-100 border-amber-300',
        'accepted'  => 'text-emerald-700 bg-emerald-100 border-emerald-300',
        'rejected'  => 'text-red-600 bg-red-100 border-red-300',
    ][$application->status] ?? 'bg-gray-100 text-gray-600 border-gray-300';
    $statusLabel = [
        'reviewing' => 'รอประเมิน',
        'accepted'  => 'ยอมรับ',
        'rejected'  => 'ปฏิเสธ',
    ][$application->status] ?? $application->status;

    $skillLevelLabels = ['beginner' => 'เริ่มต้น', 'intermediate' => 'ปานกลาง', 'advanced' => 'ขั้นสูง', 'expert' => 'ผู้เชี่ยวชาญ'];
    $langLevelLabels  = ['basic' => 'พื้นฐาน', 'conversational' => 'สนทนาได้', 'fluent' => 'คล่องแคล่ว', 'native' => 'เจ้าของภาษา'];
    $genderLabels     = ['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ'];

    $fullName = trim(
        ($application->resume->first_name ?? '') . ' ' .
        ($application->resume->middle_name ?? '') . ' ' .
        ($application->resume->last_name ?? '')
    );
@endphp

<div class="p-4">
<div class="flex flex-col gap-4 p-5 bg-base-200 shadow rounded-2xl">

    {{-- ===== TOP BAR ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('provider.applications.index') }}"
               class="flex items-center justify-center w-9 h-9 border border-gray-300 rounded-xl bg-white hover:bg-gray-100 transition shadow-sm"
               title="กลับ">
                <i class="fa-solid fa-arrow-left text-gray-500 text-sm"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-gray-800 leading-tight">{{ $application->recruitment->rc_title }}</h1>
                <p class="text-xs text-gray-400 mt-0.5">ใบสมัครของ {{ $application->jobber->profile->up_name ?? $application->jobber->email }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <span class="px-3 py-1 text-xs font-medium rounded-full border {{ $statusColor }}">{{ $statusLabel }}</span>

            @if($application->status !== 'rejected')
                <form method="POST" action="{{ route('provider.applications.shortlist', $application->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-xl border transition
                            {{ $application->is_shortlisted
                                ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-{{ $application->is_shortlisted ? 'solid' : 'regular' }} fa-star text-xs"></i>
                        {{ $application->is_shortlisted ? 'ตัวเต็ง' : 'บันทึกตัวเต็ง' }}
                    </button>
                </form>
            @endif

            <a href="{{ route('provider.applications.resume-pdf', $application->id) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs text-white rounded-xl bg-blue-600 hover:bg-blue-700 transition shadow-sm">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>

            @if($application->status === 'rejected')
                <form id="form-delete" action="{{ route('provider.applications.destroy', $application->id) }}" method="POST">
                    @csrf @method('DELETE')
                </form>
                <button type="button" id="btn-delete"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border border-red-300 text-red-600 rounded-xl bg-white hover:bg-red-50 transition">
                    <i class="fa-solid fa-trash"></i> ลบ
                </button>
            @endif
        </div>
    </div>

    {{-- ===== MAIN LAYOUT: Resume left | Actions right ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

        {{-- ===== LEFT: Resume Card ===== --}}
        <div class="lg:col-span-2 flex flex-col gap-4">

            {{-- Hero: รูป + ชื่อ + ช่องทางติดต่อ --}}
            <div class="bg-white border shadow-sm rounded-2xl overflow-hidden">
                <div class="h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
                <div class="p-6">
                    <div class="flex items-center gap-5">
                        @if($application->resume->profile_image)
                            <img src="{{ asset('storage/' . $application->resume->profile_image) }}"
                                 class="w-20 h-20 rounded-2xl object-cover border-2 border-gray-100 shadow-sm flex-shrink-0">
                        @else
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                                <i class="fa-solid fa-user text-3xl text-indigo-400"></i>
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <h2 class="text-xl font-bold text-gray-800">{{ $fullName ?: '-' }}</h2>

                            <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-500">
                                @if($application->resume->email ?? $application->jobber->email)
                                    <a href="mailto:{{ $application->resume->email ?? $application->jobber->email }}"
                                       class="inline-flex items-center gap-1.5 hover:text-blue-600 transition">
                                        <i class="fa-regular fa-envelope text-xs"></i>
                                        {{ $application->resume->email ?? $application->jobber->email }}
                                    </a>
                                @endif
                                @if($application->resume->phone)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $application->resume->phone) }}"
                                       class="inline-flex items-center gap-1.5 hover:text-emerald-600 transition">
                                        <i class="fa-solid fa-phone text-xs"></i>
                                        {{ $application->resume->phone }}
                                    </a>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 mt-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 text-xs text-gray-600">
                                    <i class="fa-solid fa-venus-mars text-gray-400 text-xs"></i>
                                    {{ $genderLabels[$application->resume->gender ?? ''] ?? 'ไม่ระบุเพศ' }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 text-xs text-gray-600">
                                    <i class="fa-regular fa-calendar text-gray-400 text-xs"></i>
                                    @if($application->resume->birth_date)
                                        @php $age = \Carbon\Carbon::parse($application->resume->birth_date)->age; @endphp
                                        {{ \Carbon\Carbon::parse($application->resume->birth_date)->format('d/m/Y') }}
                                        <span class="text-gray-400">({{ $age }} ปี)</span>
                                    @else
                                        ไม่ระบุวันเกิด
                                    @endif
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 text-xs text-gray-600">
                                    <i class="fa-solid fa-location-dot text-gray-400 text-xs"></i>
                                    {{ $application->resume->preferred_location ?? 'ไม่ระบุสถานที่' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <p class="mt-4 text-sm leading-relaxed border-t pt-4 {{ $application->resume->summary ? 'text-gray-600' : 'text-gray-400 italic' }}">
                        {{ $application->resume->summary ?? 'ไม่มีข้อมูล' }}
                    </p>
                </div>
            </div>

            {{-- ประสบการณ์ทำงาน --}}
            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-blue-500 rounded-full"></span>
                    ประสบการณ์ทำงาน
                </h3>
                <div class="flex flex-col gap-1">
                    @forelse($application->resume->workExperiences as $work)
                    @php
                        $wStart = $work->start_date ? \Carbon\Carbon::parse($work->start_date) : null;
                        $wEnd   = $work->is_current ? \Carbon\Carbon::now() : ($work->end_date ? \Carbon\Carbon::parse($work->end_date) : null);
                        $wDur   = '';
                        if ($wStart && $wEnd) {
                            $d = $wStart->diff($wEnd);
                            if ($d->y > 0) { $wDur = $d->y . ' ปี' . ($d->m ? ' ' . $d->m . ' เดือน' : ''); }
                            elseif ($d->m > 0) { $wDur = $d->m . ' เดือน'; }
                            else { $wDur = 'น้อยกว่า 1 เดือน'; }
                        }
                    @endphp
                    <div class="flex gap-4 py-3 border-b last:border-0">
                        <div class="flex flex-col items-center pt-1">
                            <div class="w-2.5 h-2.5 rounded-full bg-blue-400 ring-2 ring-blue-100 flex-shrink-0"></div>
                            <div class="w-px flex-1 bg-gray-200 mt-1.5"></div>
                        </div>
                        <div class="pb-2 min-w-0 flex-1">
                            <p class="font-semibold text-gray-800 text-sm">{{ $work->job_title }}</p>
                            <p class="text-sm text-gray-500">{{ $work->company_name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $work->start_date }} — {{ $work->is_current ? 'ปัจจุบัน' : ($work->end_date ?? '-') }}
                                @if($wDur) <span class="ml-1 text-blue-400">· {{ $wDur }}</span> @endif
                            </p>
                            @if($work->description)
                                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">{{ $work->description }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 italic">ไม่มีข้อมูล</p>
                    @endforelse
                </div>
            </div>

            {{-- การศึกษา --}}
            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-500 rounded-full"></span>
                    การศึกษา
                </h3>
                <div class="flex flex-col gap-1">
                    @forelse($application->resume->educations as $edu)
                    <div class="flex gap-4 py-3 border-b last:border-0">
                        <div class="flex flex-col items-center pt-1">
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-emerald-100 flex-shrink-0"></div>
                            <div class="w-px flex-1 bg-gray-200 mt-1.5"></div>
                        </div>
                        <div class="pb-2 min-w-0 flex-1">
                            <p class="font-semibold text-gray-800 text-sm">{{ $edu->field_of_study }}</p>
                            <p class="text-sm text-gray-500">{{ $edu->institution }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $edu->education_level }}
                                · {{ $edu->start_year }} — {{ $edu->end_year ?? 'ปัจจุบัน' }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 italic">ไม่มีข้อมูล</p>
                    @endforelse
                </div>
            </div>

            {{-- ทักษะ + ภาษา --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="bg-white border shadow-sm rounded-2xl p-5">
                    <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="w-1 h-5 bg-violet-500 rounded-full"></span>
                        ทักษะ
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($application->resume->resumeSkills as $skill)
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-violet-50 border border-violet-100 text-xs text-violet-700 font-medium">
                                {{ $skill->skill->name ?? '-' }}
                                <span class="text-violet-400 font-normal">· {{ $skillLevelLabels[$skill->proficiency_level] ?? $skill->proficiency_level }}</span>
                            </span>
                        @empty
                            <p class="text-sm text-gray-400 italic">ไม่มีข้อมูล</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white border shadow-sm rounded-2xl p-5">
                    <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="w-1 h-5 bg-sky-500 rounded-full"></span>
                        ทักษะด้านภาษา
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($application->resume->languages as $lang)
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-sky-50 border border-sky-100 text-xs text-sky-700 font-medium">
                                {{ $lang->language }}
                                <span class="text-sky-400 font-normal">· {{ $langLevelLabels[$lang->level] ?? $lang->level }}</span>
                            </span>
                        @empty
                            <p class="text-sm text-gray-400 italic">ไม่มีข้อมูล</p>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- ใบรับรอง --}}
            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-amber-400 rounded-full"></span>
                    ใบรับรอง / ประกาศนียบัตร
                </h3>
                <div class="flex flex-col gap-2">
                    @forelse($application->resume->certificates as $cert)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $cert->name }}</p>
                            @if($cert->issued_by)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $cert->issued_by }}{{ $cert->issued_year ? ' · ' . $cert->issued_year : '' }}</p>
                            @endif
                        </div>
                        @if($cert->file_path)
                            <a href="{{ asset('storage/' . $cert->file_path) }}" target="_blank"
                               class="flex-shrink-0 px-2.5 py-1 text-xs text-amber-700 border border-amber-300 rounded-lg hover:bg-amber-100 transition">
                                ดูไฟล์
                            </a>
                        @endif
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 italic">ไม่มีข้อมูล</p>
                    @endforelse
                </div>
            </div>

            {{-- Cover Letter --}}
            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-pink-400 rounded-full"></span>
                    จดหมายสมัครงาน
                </h3>
                @if($application->cover_letter)
                    <p class="text-sm text-gray-600 whitespace-pre-line leading-relaxed">{{ $application->cover_letter }}</p>
                @else
                    <p class="text-sm text-gray-400 italic">ไม่มีข้อมูล</p>
                @endif
            </div>

        </div>{{-- end left --}}

        {{-- ===== RIGHT: Sidebar ===== --}}
        <div class="flex flex-col gap-4">

            {{-- ข้อมูลการสมัคร --}}
            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-indigo-400 rounded-full"></span>
                    ข้อมูลการสมัคร
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-gray-400 uppercase tracking-wide">วันที่สมัคร</span>
                        <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($application->applied_at)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-gray-400 uppercase tracking-wide">ตำแหน่งงาน</span>
                        <span class="font-medium text-gray-700">{{ $application->recruitment->rc_title }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-gray-400 uppercase tracking-wide">เริ่มงานได้เมื่อ</span>
                        <span class="font-medium {{ $application->resume->available_start_date ? 'text-gray-700' : 'text-gray-400 italic' }}">
                            {{ $application->resume->available_start_date ? \Carbon\Carbon::parse($application->resume->available_start_date)->format('d/m/Y') : 'ไม่มีข้อมูล' }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-gray-400 uppercase tracking-wide">เงินเดือนที่คาดหวัง</span>
                        <span class="font-medium {{ $application->resume->expected_salary ? 'text-gray-700' : 'text-gray-400 italic' }}">
                            {{ $application->resume->expected_salary ? number_format($application->resume->expected_salary) . ' บาท' : 'ไม่มีข้อมูล' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ติดต่อ --}}
            @if($application->status !== 'rejected')
            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-teal-400 rounded-full"></span>
                    ติดต่อ
                </h3>
                <div class="flex flex-col gap-2">
                    <a href="mailto:{{ $application->resume->email ?? $application->jobber->email }}"
                       class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition text-sm">
                        <i class="fa-regular fa-envelope w-4 text-center"></i>
                        <span class="truncate">{{ $application->resume->email ?? $application->jobber->email }}</span>
                    </a>
                    @if($application->resume->phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $application->resume->phone) }}"
                       class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition text-sm">
                        <i class="fa-solid fa-phone w-4 text-center"></i>
                        {{ $application->resume->phone }}
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Internal Note --}}
            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <span class="w-1 h-5 bg-gray-400 rounded-full"></span>
                        Internal Note
                    </h3>
                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-500">ผู้สมัครไม่เห็น</span>
                </div>
                <form action="{{ route('provider.applications.internal-note', $application->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <textarea name="internal_note" rows="4"
                        class="w-full text-sm rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition resize-none"
                        placeholder="บันทึกโน้ตภายใน เช่น จุดแข็ง จุดที่ต้องสัมภาษณ์เพิ่ม...">{{ old('internal_note', $application->internal_note) }}</textarea>
                    @error('internal_note')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <button type="submit"
                        class="mt-2 w-full py-2 text-sm text-white bg-gray-700 rounded-xl hover:bg-gray-800 transition">
                        บันทึกโน้ต
                    </button>
                </form>
            </div>

            {{-- ผลการพิจารณา --}}
            @if($application->status === 'reviewing')
            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-orange-400 rounded-full"></span>
                    ผลการพิจารณา
                </h3>

                <form id="form-rejected" action="{{ route('provider.applications.updateStatus', $application->id) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="rejected">
                </form>
                <form id="form-accepted" action="{{ route('provider.applications.updateStatus', $application->id) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="accepted">
                </form>

                <div class="flex flex-col gap-2">
                    <button type="button" id="btn-accepted"
                        class="w-full py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i> ผ่าน
                    </button>
                    <button type="button" id="btn-rejected"
                        class="w-full py-2.5 text-sm font-medium text-red-600 border border-red-300 rounded-xl hover:bg-red-50 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-xmark"></i> ไม่ผ่าน
                    </button>
                    <a href="{{ route('provider.applications.index') }}"
                       class="w-full py-2.5 text-sm text-center text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                        ยกเลิก
                    </a>
                </div>
            </div>
            @endif

        </div>{{-- end sidebar --}}

    </div>{{-- end grid --}}
</div>{{-- end outer card --}}
</div>{{-- end page padding --}}

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

    const applicantName = "{{ $fullName }}";
    const jobTitle      = "{{ $application->recruitment->rc_title }}";

    document.getElementById('btn-rejected')?.addEventListener('click', () => {
        Swal.fire({
            title: 'ยืนยันไม่ผ่านการพิจารณา',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-xmark" style="margin-right:6px"></i> ยืนยัน ไม่ผ่าน',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) document.getElementById('form-rejected').submit();
        });
    });

    document.getElementById('btn-accepted')?.addEventListener('click', () => {
        Swal.fire({
            title: 'ยืนยันผ่านการพิจารณา',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-check" style="margin-right:6px"></i>ยืนยัน',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) document.getElementById('form-accepted').submit();
        });
    });

    document.getElementById('btn-delete')?.addEventListener('click', () => {
        Swal.fire({
            title: 'ยืนยันการลบ',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-trash" style="margin-right:6px"></i>ลบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) document.getElementById('form-delete').submit();
        });
    });
</script>
@endpush
