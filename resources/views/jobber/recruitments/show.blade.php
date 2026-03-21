@extends('layouts.app')

@section('title', 'รายละเอียดงาน')

@section('content')
<div class="w-full p-4 border shadow-sm md:p-6 rounded-3xl border-slate-200 bg-gradient-to-b from-sky-50/70 to-white">

    @php
        $typeLabels = collect($rec->type_labels ?? []);
        $modeLabel = $rec->work_mode_label ?: 'ไม่ระบุโหมดการทำงาน';
        $descriptionText = trim((string) ($rec->rc_description ?? '')) !== '' ? $rec->rc_description : 'ไม่ได้ระบุรายละเอียดงาน';
        $requirementsText = trim((string) ($rec->rc_requirements ?? '')) !== '' ? $rec->rc_requirements : 'ไม่ได้ระบุคุณสมบัติทั่วไป';
        $salaryText = trim((string) ($rec->rc_salary ?? '')) !== '' ? $rec->rc_salary : 'ไม่ระบุ';
        $locationText = trim((string) ($rec->rc_location_text ?? '')) !== '' ? $rec->rc_location_text : 'ไม่ระบุ';
        $postedDate = optional($rec->rc_posted_at)->timezone('Asia/Bangkok')->format('d/m/Y') ?: '-';
        $expireDateText = $rec->rc_expire_at
            ? \Illuminate\Support\Carbon::parse($rec->rc_expire_at)->format('d/m/Y')
            : null;
        $locationLink = trim((string) ($rec->rc_location_link ?? ''));
        $hasLocationLink = $locationLink !== '';
        $isEmbedLocationLink = $hasLocationLink
            && (bool) preg_match('~^https://(www\.)?google\.com/maps/embed\?pb=.+$~i', $locationLink);

        $isExpired = $rec->rc_expire_at
            ? \Illuminate\Support\Carbon::parse($rec->rc_expire_at)->endOfDay()->isPast()
            : false;

        $jobStatusMeta = match ($rec->rc_status) {
            'open' => ['label' => 'เปิดรับสมัคร', 'class' => 'text-emerald-700 bg-emerald-100 border border-emerald-200'],
            'closed' => ['label' => 'ปิดรับสมัคร', 'class' => 'text-rose-700 bg-rose-100 border border-rose-200'],
            'draft' => ['label' => 'ฉบับร่าง', 'class' => 'text-slate-700 bg-slate-100 border border-slate-200'],
            default => ['label' => $rec->rc_status ?: 'ไม่ระบุสถานะ', 'class' => 'text-slate-700 bg-slate-100 border border-slate-200'],
        };

        $isJobOpenFromIssuer = $rec->rc_status === 'open';
        $isJobAvailableNow = $isJobOpenFromIssuer && !$isExpired;
        $showAlwaysOpen = is_null($rec->rc_expire_at) && $isJobOpenFromIssuer;

        $expireInfoText = $rec->rc_expire_at
            ? $expireDateText
            : ($showAlwaysOpen ? 'เปิดรับตลอด' : 'ไม่มีวันหมดเขต');

        $typeBadgeClass = 'text-sky-700 bg-sky-100 border border-sky-200';
        $modeBadgeClass = match ($rec->rc_work_mode) {
            'onsite' => 'text-orange-700 bg-orange-100 border border-orange-200',
            'remote' => 'text-cyan-700 bg-cyan-100 border border-cyan-200',
            'hybrid' => 'text-violet-700 bg-violet-100 border border-violet-200',
            'distributed' => 'text-teal-700 bg-teal-100 border border-teal-200',
            default => 'text-slate-700 bg-slate-100 border border-slate-200',
        };

        $deadlineBadgeClass = $rec->rc_expire_at
            ? ($isExpired
                ? 'text-rose-700 bg-rose-100 border border-rose-200'
                : 'text-amber-700 bg-amber-100 border border-amber-200')
            : 'text-emerald-700 bg-emerald-100 border border-emerald-200';

    @endphp

    {{-- Header --}}
    <div class="p-5 mb-6 bg-white border shadow-sm rounded-2xl border-slate-200">
        <div class="flex items-center justify-between">
           @php
    $sessionReferer = session()->pull('back_url');
    $fromParam = request()->query('from'); // ✅ รับ ?from=applications

    $backUrl = $sessionReferer ?? null;

    if (!$backUrl) {
        if ($fromParam === 'applications' && auth()->check() && auth()->user()->role === 'jobber') {
            $backUrl = route('jobber.applications.index');
        } else {
            $previousPath = parse_url(url()->previous(), PHP_URL_PATH) ?? '';

            if (auth()->check() && auth()->user()->role === 'jobber') {
                if (str_contains($previousPath, '/jobber/applications')) {
                    $backUrl = route('jobber.applications.index');
                } elseif (str_contains($previousPath, '/jobber/jobs')) {
                    $backUrl = route('jobber.jobs.index');
                } else {
                    $backUrl = route('jobber.jobs.index');
                }
            } elseif (auth()->check() && auth()->user()->role === 'provider') {
                $backUrl = route('provider.recruitments.index');
            } elseif (auth()->check() && auth()->user()->role === 'admin') {
                $backUrl = route('admin.recruitments.index', ['userId' => $rec->rc_u_id]);
            } else {
                $backUrl = route('jobs.index');
            }
        }
    }
@endphp
            <a
                href="{{ $backUrl }}"
                class="flex items-center justify-center w-9 h-9 border border-gray-400 rounded-2xl bg-base-100 hover:bg-gray-200 transition"
                title="กลับ">
                <i class="fa-solid fa-arrow-left text-gray-600"></i>
            </a>
        </div>

        <div class="flex items-start gap-4 mt-4">
            <div class="w-14 h-14 overflow-hidden bg-white border rounded-full shrink-0">
                @if ($company && $company->co_profile_img)
                    <img src="{{ asset('storage/' . $company->co_profile_img) }}" class="object-cover w-full h-full" alt="company-logo">
                @else
                    <img src="{{ asset('image/web-image/logo.png') }}" class="object-contain w-full h-full p-1" alt="default-logo">
                @endif
            </div>

            <div class="min-w-0">
                <h1 class="text-2xl font-bold leading-tight md:text-3xl text-base-content break-words">{{ $rec->rc_title }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $company->co_name ?? 'ไม่ระบุบริษัท' }}</p>

                <div class="flex flex-wrap gap-2 mt-4 text-xs">
                    @if($typeLabels->isNotEmpty())
                        @foreach($typeLabels as $typeLabel)
                            <span class="px-3 py-1 rounded-full {{ $typeBadgeClass }}">{{ $typeLabel }}</span>
                        @endforeach
                    @else
                        <span class="px-3 py-1 rounded-full text-slate-700 bg-slate-100 border border-slate-200">ไม่ระบุประเภทงาน</span>
                    @endif

                    <span class="px-3 py-1 rounded-full {{ $modeBadgeClass }}">{{ $modeLabel }}</span>
                    <span class="px-3 py-1 rounded-full {{ $jobStatusMeta['class'] }}">{{ $jobStatusMeta['label'] }}</span>

                    @if($rec->rc_expire_at)
                        <span class="px-3 py-1 rounded-full {{ $deadlineBadgeClass }}">
                            {{ $isExpired ? 'หมดเขตรับสมัครแล้ว' : 'หมดเขตรับสมัคร ' . $expireDateText }}
                        </span>
                    @elseif($showAlwaysOpen)
                        <span class="px-3 py-1 rounded-full {{ $deadlineBadgeClass }}">เปิดรับตลอด</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

        {{-- Main --}}
        <div class="space-y-6 lg:col-span-8">

            {{-- Overview --}}
            <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">ภาพรวมตำแหน่งงาน</h2>
                <div class="grid grid-cols-1 gap-3 mt-4 sm:grid-cols-2">
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">เงินเดือน</p>
                        <p class="text-sm font-medium text-gray-800">{{ $salaryText }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">สถานที่ทำงาน</p>
                        <p class="text-sm font-medium text-gray-800">{{ $locationText }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">โพสต์เมื่อ</p>
                        <p class="text-sm font-medium text-gray-800">{{ $postedDate }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">หมดอายุประกาศ</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $expireInfoText }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-4">
                    @if($rec->rc_application_url)
                        <a href="{{ $rec->rc_application_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                            สมัครผ่านลิงก์ภายนอก
                        </a>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">รายละเอียดงาน</h2>
                <p class="mt-3 leading-relaxed text-gray-800 whitespace-pre-line">{{ $descriptionText }}</p>
            </div>

            {{-- Requirements --}}
            <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">คุณสมบัติเบื้องต้น</h2>
                @php
                    $genderLabel = [
                        'unspecified' => 'ไม่ระบุ',
                        'any' => 'ไม่จำกัดเพศ',
                        'male' => 'ชาย',
                        'female' => 'หญิง',
                    ][$rec->rc_gender ?? 'unspecified'] ?? 'ไม่ระบุ';

                    $educationLabel = [
                        'unspecified' => 'ไม่ระบุ',
                        'any' => 'ไม่จำกัดวุฒิ',
                        'below_bachelor' => 'ต่ำกว่าปริญญาตรี',
                        'bachelor' => 'ปริญญาตรี',
                        'master' => 'ปริญญาโท',
                        'doctorate' => 'ปริญญาเอก',
                    ][$rec->rc_education_level ?? 'unspecified'] ?? 'ไม่ระบุ';

                    $experienceLabel = [
                        'unspecified' => 'ไม่ระบุ',
                        'no_experience' => 'ไม่ต้องมีประสบการณ์',
                        '0_1' => '0-1 ปี',
                        '1_3' => '1-3 ปี',
                        '3_5' => '3-5 ปี',
                        'more_5' => 'มากกว่า 5 ปี',
                    ][$rec->rc_experience_level ?? 'unspecified'] ?? 'ไม่ระบุ';
                @endphp

                <div class="grid grid-cols-1 gap-3 mt-3 md:grid-cols-2">
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">เพศ</p>
                        <p class="text-sm font-medium text-gray-800">{{ $genderLabel }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">วุฒิการศึกษา</p>
                        <p class="text-sm font-medium text-gray-800">{{ $educationLabel }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">ประสบการณ์ทำงาน</p>
                        <p class="text-sm font-medium text-gray-800">{{ $experienceLabel }}</p>
                    </div>
                </div>

                <h3 class="mt-4 text-base font-semibold text-gray-700">คุณสมบัติทั่วไป</h3>
                <p class="mt-3 leading-relaxed text-gray-800 whitespace-pre-line">{{ $requirementsText }}</p>
            </div>

            {{-- Skills --}}
            <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">ทักษะที่ต้องการ</h2>
                <h3 class="mt-4 text-base font-semibold text-gray-700">ทักษะความสามารถ</h3>

                @php
                    $skillItems = collect();

                    if (isset($rec->recruitmentSkills) && $rec->recruitmentSkills->count()) {
                        $skillItems = $rec->recruitmentSkills->map(function ($row) {
                            return [
                                'group' => $row->skillGroup->name ?? '-',
                                'name'  => $row->skill->name ?? '-',
                                'level' => $row->proficiency_level ?? null,
                                'is_required' => (bool) ($row->is_required ?? true),
                            ];
                        });
                    }
                    elseif (isset($rec->skills) && $rec->skills->count()) {
                        $skillItems = $rec->skills->map(function ($skill) {
                            return [
                                'group' => $skill->skillGroup->name ?? '-',
                                'name'  => $skill->name ?? '-',
                                'level' => data_get($skill, 'pivot.proficiency_level'),
                                'is_required' => true,
                            ];
                        });
                    }

                    $levelMap = [
                        'beginner' => 'เริ่มต้น',
                        'intermediate' => 'ปานกลาง',
                        'advanced' => 'ขั้นสูง',
                        'expert' => 'ผู้เชี่ยวชาญ',
                    ];

                    $levelColorMap = [
                        'beginner' => 'text-sky-700',
                        'intermediate' => 'text-emerald-700',
                        'advanced' => 'text-amber-700',
                        'expert' => 'text-fuchsia-700',
                    ];

                    $requiredSkillItems = $skillItems->filter(fn ($item) => (bool) ($item['is_required'] ?? true))->values();
                    $optionalSkillItems = $skillItems->filter(fn ($item) => !(bool) ($item['is_required'] ?? true))->values();
                @endphp

                @if($skillItems->count())
                    <div class="mt-3 space-y-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">ทักษะสำคัญ</p>
                            @if($requiredSkillItems->isNotEmpty())
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($requiredSkillItems as $item)
                                        @php $levelClass = $levelColorMap[$item['level'] ?? ''] ?? 'bg-gray-100 text-gray-600'; @endphp
                                        <div class="min-w-[230px] max-w-full px-3 py-2 border rounded-xl bg-rose-50 border-rose-100 text-slate-800 shadow-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-rose-800">ทักษะ: {{ $item['name'] }}</span>
                                                @if(!empty($item['level']))
                                                    <span class="text-sm font-medium {{ $levelClass }}">ระดับ: {{ $levelMap[$item['level']] ?? $item['level'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-1 text-sm text-gray-500">ไม่ระบุ</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">ทักษะโบนัส</p>
                            @if($optionalSkillItems->isNotEmpty())
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($optionalSkillItems as $item)
                                        @php $levelClass = $levelColorMap[$item['level'] ?? ''] ?? 'bg-gray-100 text-gray-600'; @endphp
                                        <div class="min-w-[230px] max-w-full px-3 py-2 border rounded-xl bg-emerald-50 border-emerald-100 text-slate-800 shadow-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-emerald-800">ทักษะ: {{ $item['name'] }}</span>
                                                @if(!empty($item['level']))
                                                    <span class="text-sm font-medium {{ $levelClass }}">ระดับ: {{ $levelMap[$item['level']] ?? $item['level'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-1 text-sm text-gray-500">ไม่ระบุ</p>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="mt-3 text-sm text-gray-500">ไม่ระบุทักษะความสามารถ</p>
                @endif

                <h3 class="mt-6 text-base font-semibold text-gray-700">ทักษะด้านภาษา</h3>
                @if(isset($rec->languages) && $rec->languages->count())
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($rec->languages as $lang)
                            @php
                                $p = $lang->proficiency ?? data_get($lang, 'pivot.proficiency');
                                $pLabel = [
                                    'basic' => 'พื้นฐาน',
                                    'conversational' => 'สนทนาได้',
                                    'fluent' => 'คล่องแคล่ว',
                                    'native' => 'เจ้าของภาษา',
                                ][$p] ?? ($p ?: '-');

                                $langLevelColorMap = [
                                    'basic' => 'text-slate-700',
                                    'conversational' => 'text-cyan-700',
                                    'fluent' => 'text-emerald-700',
                                    'native' => 'text-violet-700',
                                ];
                                $langLevelClass = $langLevelColorMap[$p] ?? 'bg-gray-100 text-gray-600';
                            @endphp

                            <div class="inline-flex items-center gap-3 px-3 py-2 border rounded-xl bg-emerald-50 border-emerald-100 text-slate-800 shadow-sm">
                                <span class="text-sm font-medium text-teal-700">
                                    ภาษา: {{ $lang->language ?? '-' }}
                                </span>
                                <span class="text-sm font-medium {{ $langLevelClass }}">
                                    ระดับ: {{ $pLabel }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-sm text-gray-500">ไม่ระบุทักษะด้านภาษา</p>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6 lg:col-span-4">

            @php
                $matchingData = $matching ?? [
                    'total_score' => 0,
                    'breakdown' => [
                        'skill_match' => 0,
                        'skill_level' => 0,
                        'language' => 0,
                        'experience' => 0,
                        'education' => 0,
                        'location' => 0,
                        'gender' => 0,
                    ],
                    'criteria_defined' => [
                        'skill_match' => false,
                        'skill_level' => false,
                        'language' => false,
                        'experience' => false,
                        'education' => false,
                        'location' => false,
                        'gender' => false,
                    ],
                    'required_skills_matched' => 0,
                    'required_skills_total' => 0,
                    'optional_skills_matched' => 0,
                    'optional_skills_total' => 0,
                ];

                $showFactor = function (string $key) use ($matchingData) {
                    $isDefined = (bool) data_get($matchingData, 'criteria_defined.' . $key, false);
                    if (!$isDefined) {
                        return 'ไม่ระบุ';
                    }

                    return (int) data_get($matchingData, 'breakdown.' . $key, 0) . '%';
                };
            @endphp

            @auth
                @if(auth()->user()->role === 'jobber')
                    <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-xl font-bold text-slate-800">ความเหมาะสม {{ (int) ($matchingData['total_score'] ?? 0) }}%</h3>
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-[11px] rounded-full bg-slate-100 text-slate-700 border border-slate-200"
                                title="สูตรคะแนนความเหมาะสม: ทักษะ 45%, ระดับทักษะ 20%, ภาษา 10%, ประสบการณ์ 10%, การศึกษา 5%, สถานที่ 5%, เพศ 5%">
                                สูตรคะแนน
                                <i class="fa-regular fa-circle-question"></i>
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">คะแนนความเหมาะสมแบบถ่วงน้ำหนัก</p>

                        <div class="mt-3 space-y-2 text-sm">
                            <div class="flex items-center justify-between"><span>ทักษะ</span><span class="font-semibold">{{ $showFactor('skill_match') }}</span></div>
                            <div class="flex items-center justify-between"><span>ระดับทักษะ</span><span class="font-semibold">{{ $showFactor('skill_level') }}</span></div>
                            <div class="flex items-center justify-between"><span>ภาษา</span><span class="font-semibold">{{ $showFactor('language') }}</span></div>
                            <div class="flex items-center justify-between"><span>ประสบการณ์</span><span class="font-semibold">{{ $showFactor('experience') }}</span></div>
                            <div class="flex items-center justify-between"><span>การศึกษา</span><span class="font-semibold">{{ $showFactor('education') }}</span></div>
                            <div class="flex items-center justify-between"><span>สถานที่</span><span class="font-semibold">{{ $showFactor('location') }}</span></div>
                            <div class="flex items-center justify-between"><span>เพศ</span><span class="font-semibold">{{ $showFactor('gender') }}</span></div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div class="p-2 rounded-lg bg-rose-50 border border-rose-100">
                                ทักษะสำคัญ: {{ (int) ($matchingData['required_skills_matched'] ?? 0) }}/{{ (int) ($matchingData['required_skills_total'] ?? 0) }}
                            </div>
                            <div class="p-2 rounded-lg bg-emerald-50 border border-emerald-100">
                                ทักษะโบนัส: {{ (int) ($matchingData['optional_skills_matched'] ?? 0) }}/{{ (int) ($matchingData['optional_skills_total'] ?? 0) }}
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            {{-- Apply card --}}
            <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
                <h3 class="text-xl font-bold text-slate-800">สมัครงาน</h3>
                <p class="mt-1 text-sm text-gray-600">ส่งเรซูเม่ของคุณให้ผู้ประกอบการ</p>

                @php
                    $application = null;
                    $hasApplied = false;
                    $canWithdraw = false;

                    if (auth()->check() && auth()->user()->role === 'jobber') {
                        $application = \App\Models\JobApplication::where('recruitment_id', $rec->rc_id)
                            ->where('jobber_id', auth()->id())
                            ->first();
                        $hasApplied = $application && $application->status !== 'withdrawn';
                        $canWithdraw = $application && in_array($application->status, ['applied', 'reviewing'], true);
                    }

                    $statusMap = [
                        'applied'   => ['label' => 'สมัครแล้ว',       'box' => 'bg-sky-50 border-sky-500',      'text' => 'text-sky-800',      'tag' => 'bg-sky-100'],
                        'reviewing' => ['label' => 'กำลังพิจารณา',    'box' => 'bg-amber-50 border-amber-500',  'text' => 'text-amber-800',    'tag' => 'bg-amber-100'],
                        'accepted'  => ['label' => 'ยอมรับ',          'box' => 'bg-emerald-50 border-emerald-500','text' => 'text-emerald-800', 'tag' => 'bg-emerald-100'],
                        'rejected'  => ['label' => 'ปฏิเสธ',          'box' => 'bg-rose-50 border-rose-500',    'text' => 'text-rose-800',     'tag' => 'bg-rose-100'],
                        'withdrawn' => ['label' => 'ถอนการสมัคร',     'box' => 'bg-slate-50 border-slate-500',  'text' => 'text-slate-800',    'tag' => 'bg-slate-100'],
                    ];
                @endphp

                <div class="mt-4">
                    @auth
                        @if(auth()->user()->role === 'jobber')
                            @if($hasApplied)
                                @if($canWithdraw)
                                    {{-- ปุ่มถอนการสมัคร (สถานะพิจารณา/รอพิจารณา) --}}
                                    <form id="form-withdraw" action="{{ route('jobber.jobs.withdraw', $rec->rc_id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" id="btn-withdraw"
                                            class="w-full px-4 py-2 text-sm font-medium text-white transition-colors bg-orange-600 rounded-lg hover:bg-orange-700">
                                        ถอนการสมัคร
                                    </button>
                                @else
                                    <div class="w-full px-4 py-2 text-sm text-center text-gray-600 bg-gray-100 rounded-lg">
                                        ไม่สามารถถอนการสมัครได้ในสถานะนี้
                                    </div>
                                @endif
                            @else
                                @if($isJobAvailableNow)
                                    {{-- ปุ่มสมัครงาน --}}
                                    <form id="form-apply" action="{{ route('jobber.jobs.apply', $rec->rc_id) }}" method="POST">
                                        @csrf
                                    </form>
                                    <button type="button" id="btn-apply"
                                            class="w-full px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                                        สมัครงานด้วยเรซูเม่ของคุณ
                                    </button>
                                @else
                                    <div class="w-full px-4 py-2 text-sm text-center text-gray-600 bg-gray-100 rounded-lg">
                                        ประกาศนี้ปิดรับสมัครแล้ว
                                    </div>
                                @endif
                            @endif
                        @else
                            <p class="text-sm text-gray-600">บัญชีผู้ประกอบการไม่สามารถสมัครงานได้</p>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                           class="block w-full px-4 py-2 text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            เข้าสู่ระบบเพื่อสมัครงาน
                        </a>
                    @endauth

                    @auth
                        @if(auth()->user()->role === 'jobber' && $application)
                            @php $s = $statusMap[$application->status] ?? $statusMap['applied']; @endphp
                            <div class="p-3 mt-4 border-l-4 rounded-lg {{ $s['box'] }}">
                                <p class="text-sm font-semibold {{ $s['text'] }}">
                                    สถานะการสมัคร:
                                    <span class="px-2 py-1 text-xs rounded {{ $s['tag'] }}">{{ $s['label'] }}</span>
                                </p>
                                <p class="mt-1 text-xs text-gray-600">
                                    สมัครเมื่อ: {{ optional($application->applied_at)->format('d/m/Y') ?: '-' }}
                                </p>
                                @if($application->reviewed_at)
                                    <p class="text-xs text-gray-600">พิจารณาเมื่อ: {{ $application->reviewed_at->format('d/m/Y') }}</p>
                                @endif
                                @if($application->review_note)
                                    <p class="mt-2 text-xs text-gray-700 whitespace-pre-line">
                                        <strong>หมายเหตุจากผู้ประกอบการ:</strong><br>{{ $application->review_note }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    @endauth

                    @if(session('success'))
                        <div class="p-3 mt-3 text-sm text-green-700 bg-green-100 rounded-lg">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="p-3 mt-3 text-sm text-red-700 bg-red-100 rounded-lg">{{ session('error') }}</div>
                    @endif
                </div>
            </div>

            {{-- Map card --}}
            @if($hasLocationLink)
                <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
                    <h3 class="text-xl font-bold text-slate-800">แผนที่สถานที่ทำงาน</h3>
                    @if($isEmbedLocationLink)
                        <div class="mt-3 overflow-hidden border rounded-lg border-slate-200">
                            <iframe
                                src="{{ $locationLink }}"
                                class="w-full h-64"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen>
                            </iframe>
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-600">
                            ลิงก์แผนที่นี้ไม่ใช่รูปแบบ Embed จึงไม่สามารถแสดงแผนที่ในหน้าได้
                        </p>
                    @endif
                    <a href="{{ $locationLink }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center mt-3 text-sm font-medium text-blue-700 hover:underline">
                        เปิดแผนที่ในแท็บใหม่
                    </a>
                </div>
            @endif

            {{-- Company card --}}
            <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
                <h3 class="text-xl font-bold text-slate-800">ข้อมูลบริษัท</h3>
                <p class="mt-2 text-sm text-gray-800">{{ $company->co_name ?? 'ไม่ระบุบริษัท' }}</p>
                <div class="mt-3 space-y-1 text-sm text-gray-600">
                    <p>อีเมล: {{ $company?->co_email ?: '-' }}</p>
                    <p>โทร: {{ $company?->co_phone ?: '-' }}</p>
                    <p class="whitespace-pre-line">ที่อยู่: {{ $company?->co_address ?: '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const jobTitle = "{{ addslashes($rec->rc_title) }}";
    const companyName = "{{ addslashes($company->co_name ?? 'ไม่ระบุบริษัท') }}";

    // ปุ่มสมัครงาน
    document.getElementById('btn-apply')?.addEventListener('click', () => {
        Swal.fire({
            title: 'ยืนยันการสมัครงาน',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-paper-plane" style="margin-right:6px"></i> สมัคร',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('form-apply').submit();
            }
        });
    });

    // ปุ่มถอนการสมัคร
    document.getElementById('btn-withdraw')?.addEventListener('click', () => {
        Swal.fire({
            title: 'ยืนยันการถอนการสมัคร',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ea580c',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-xmark" style="margin-right:6px"></i> ถอนการสมัคร',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('form-withdraw').submit();
            }
        });
    });

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
</script>
@endpush
