@extends('layouts.app')

@section('title', 'รายละเอียด Resume ผู้สมัคร')

@section('content')
@php
    $skillLevelLabels = ['beginner' => 'เริ่มต้น', 'intermediate' => 'ปานกลาง', 'advanced' => 'ขั้นสูง', 'expert' => 'ผู้เชี่ยวชาญ'];
    $langLevelLabels  = ['basic' => 'พื้นฐาน', 'conversational' => 'สนทนาได้', 'fluent' => 'คล่องแคล่ว', 'native' => 'เจ้าของภาษา'];
    $genderLabels     = ['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ'];
    $fullName = trim(($resume->first_name ?? '') . ' ' . ($resume->middle_name ?? '') . ' ' . ($resume->last_name ?? ''));
@endphp

<div class="p-4">
<div class="flex flex-col gap-4 p-5 bg-base-200 shadow rounded-2xl">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('provider.candidates.index') }}"
               class="flex items-center justify-center w-9 h-9 border border-gray-300 rounded-xl bg-white hover:bg-gray-100 transition shadow-sm"
               title="กลับ">
                <i class="fa-solid fa-arrow-left text-gray-500 text-sm"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-gray-800 leading-tight">รายละเอียด Resume ผู้สมัคร</h1>
                <p class="text-xs text-gray-400 mt-0.5">{{ $fullName ?: '-' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <form method="POST" action="{{ route('provider.candidates.save', $resume->id) }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-xl border transition {{ $isSaved ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50' }}">
                    <i class="fa-{{ $isSaved ? 'solid' : 'regular' }} fa-bookmark text-xs"></i>
                    {{ $isSaved ? 'บันทึกแล้ว' : 'บันทึกผู้สมัคร' }}
                </button>
            </form>
            <a href="{{ route('provider.candidates.resume-pdf', $resume->id) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs text-white rounded-xl bg-blue-600 hover:bg-blue-700 transition shadow-sm">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

        <div class="lg:col-span-2 flex flex-col gap-4">
            <div class="bg-white border shadow-sm rounded-2xl overflow-hidden">
                <div class="h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
                <div class="p-6">
                    <div class="flex items-center gap-5">
                        @if($resume->profile_image)
                            <img src="{{ asset('storage/' . $resume->profile_image) }}"
                                 class="w-20 h-20 rounded-2xl object-cover border-2 border-gray-100 shadow-sm flex-shrink-0">
                        @else
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                                <i class="fa-solid fa-user text-3xl text-indigo-400"></i>
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <h2 class="text-xl font-bold text-gray-800">{{ $fullName ?: '-' }}</h2>

                            <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-500">
                                @if($resume->email ?? $resume->user->email)
                                    <a href="mailto:{{ $resume->email ?? $resume->user->email }}"
                                       class="inline-flex items-center gap-1.5 hover:text-blue-600 transition">
                                        <i class="fa-regular fa-envelope text-xs"></i>
                                        {{ $resume->email ?? $resume->user->email }}
                                    </a>
                                @endif
                                @if($resume->phone)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $resume->phone) }}"
                                       class="inline-flex items-center gap-1.5 hover:text-emerald-600 transition">
                                        <i class="fa-solid fa-phone text-xs"></i>
                                        {{ $resume->phone }}
                                    </a>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 mt-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 text-xs text-gray-600">
                                    <i class="fa-solid fa-venus-mars text-gray-400 text-xs"></i>
                                    {{ $genderLabels[$resume->gender ?? ''] ?? 'ไม่ระบุเพศ' }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 text-xs text-gray-600">
                                    <i class="fa-regular fa-calendar text-gray-400 text-xs"></i>
                                    @if($resume->birth_date)
                                        {{ \Carbon\Carbon::parse($resume->birth_date)->format('d/m/Y') }}
                                        <span class="text-gray-400">({{ \Carbon\Carbon::parse($resume->birth_date)->age }} ปี)</span>
                                    @else
                                        ไม่ระบุวันเกิด
                                    @endif
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 text-xs text-gray-600">
                                    <i class="fa-solid fa-location-dot text-gray-400 text-xs"></i>
                                    {{ $resume->preferred_location ?: 'ไม่ระบุสถานที่' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <p class="mt-4 text-sm leading-relaxed border-t pt-4 {{ $resume->summary ? 'text-gray-600' : 'text-gray-400 italic' }}">
                        {{ $resume->summary ?? 'ไม่มีข้อมูล' }}
                    </p>
                </div>
            </div>

            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-blue-500 rounded-full"></span>
                    ประสบการณ์ทำงาน
                </h3>
                <div class="flex flex-col gap-1">
                    @forelse($resume->workExperiences as $work)
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

            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-500 rounded-full"></span>
                    การศึกษา
                </h3>
                <div class="flex flex-col gap-1">
                    @forelse($resume->educations as $edu)
                        <div class="flex gap-4 py-3 border-b last:border-0">
                            <div class="flex flex-col items-center pt-1">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-emerald-100 flex-shrink-0"></div>
                                <div class="w-px flex-1 bg-gray-200 mt-1.5"></div>
                            </div>
                            <div class="pb-2 min-w-0 flex-1">
                                <p class="font-semibold text-gray-800 text-sm">{{ $edu->field_of_study }}</p>
                                <p class="text-sm text-gray-500">{{ $edu->institution }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $edu->education_level }} · {{ $edu->start_year }} — {{ $edu->end_year ?? 'ปัจจุบัน' }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 italic">ไม่มีข้อมูล</p>
                    @endforelse
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white border shadow-sm rounded-2xl p-5">
                    <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="w-1 h-5 bg-violet-500 rounded-full"></span>
                        ทักษะ
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($resume->resumeSkills as $skill)
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
                        @forelse($resume->languages as $lang)
                            @php
                                $langLevel = $lang->proficiency ?? data_get($lang, 'pivot.proficiency') ?? $lang->level;
                            @endphp
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-sky-50 border border-sky-100 text-xs text-sky-700 font-medium">
                                {{ $lang->language ?? '-' }}
                                <span class="text-sky-400 font-normal">· {{ $langLevelLabels[$langLevel] ?? $langLevel ?? '-' }}</span>
                            </span>
                        @empty
                            <p class="text-sm text-gray-400 italic">ไม่มีข้อมูล</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-amber-400 rounded-full"></span>
                    ใบรับรอง / ประกาศนียบัตร
                </h3>
                <div class="flex flex-col gap-2">
                    @forelse($resume->certificates as $cert)
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
        </div>

        <div class="flex flex-col gap-4">
            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-indigo-400 rounded-full"></span>
                    ข้อมูลผู้สมัคร
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-gray-400 uppercase tracking-wide">อัปเดตล่าสุด</span>
                        <span class="font-medium text-gray-700">{{ optional($resume->updated_at)->format('d/m/Y H:i') ?: '-' }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-gray-400 uppercase tracking-wide">สถานะการเปิดเผย</span>
                        <span class="font-medium {{ $resume->is_visible ? 'text-emerald-700' : 'text-gray-400 italic' }}">
                            {{ $resume->is_visible ? 'เปิดเผย' : 'ไม่เปิดเผย' }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-gray-400 uppercase tracking-wide">เริ่มงานได้เมื่อ</span>
                        <span class="font-medium {{ $resume->available_start_date ? 'text-gray-700' : 'text-gray-400 italic' }}">
                            {{ $resume->available_start_date ? \Carbon\Carbon::parse($resume->available_start_date)->format('d/m/Y') : 'ไม่มีข้อมูล' }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-gray-400 uppercase tracking-wide">เงินเดือนที่คาดหวัง</span>
                        <span class="font-medium {{ $resume->expected_salary ? 'text-gray-700' : 'text-gray-400 italic' }}">
                            {{ $resume->expected_salary ? number_format($resume->expected_salary) . ' บาท' : 'ไม่มีข้อมูล' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-teal-400 rounded-full"></span>
                    ติดต่อผู้สมัคร
                </h3>
                <div class="flex flex-col gap-2">
                    @if($resume->email ?? $resume->user->email)
                        <a href="mailto:{{ $resume->email ?? $resume->user->email }}"
                           class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition text-sm">
                            <i class="fa-regular fa-envelope w-4 text-center"></i>
                            <span class="truncate">{{ $resume->email ?? $resume->user->email }}</span>
                        </a>
                    @endif
                    @if($resume->phone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $resume->phone) }}"
                           class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition text-sm">
                            <i class="fa-solid fa-phone w-4 text-center"></i>
                            {{ $resume->phone }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="bg-white border shadow-sm rounded-2xl p-5">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-400 rounded-full"></span>
                    เชิญสมัครงาน
                </h3>
                <p class="text-xs text-gray-500 mb-3">เลือกประกาศงานที่เปิดรับเพื่อเชิญผู้สมัครคนนี้</p>
                <form method="POST" action="{{ route('provider.candidates.invite', $resume->id) }}" class="flex flex-col gap-2">
                    @csrf
                    <select name="recruitment_id" class="h-10 border border-gray-300 rounded-lg px-2 text-sm" required {{ $openRecruitments->isEmpty() ? 'disabled' : '' }}>
                        <option value="">เลือกประกาศงาน</option>
                        @foreach($openRecruitments as $job)
                            <option value="{{ $job->rc_id }}">{{ $job->rc_title }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="w-full h-10 inline-flex items-center justify-center px-4 text-sm text-gray-700 transition border border-gray-300 rounded-xl bg-white hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-400 disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ $openRecruitments->isEmpty() ? 'disabled' : '' }}>
                        <i class="fa-solid fa-paper-plane mr-1"></i> เชิญสมัครงาน
                    </button>
                </form>
                @if($openRecruitments->isEmpty())
                    <p class="text-xs text-amber-600 mt-2">ยังไม่มีประกาศงานที่เปิดรับสำหรับการเชิญสมัคร</p>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    @if (session('swal_success'))
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

    @if (session('swal_error'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: '{{ session('swal_error') }}',
            showConfirmButton: false,
            timer: 3200,
            timerProgressBar: true,
        });
    @endif
</script>
@endpush
