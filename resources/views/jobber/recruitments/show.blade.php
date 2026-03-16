@extends('layouts.app')

@section('title', 'รายละเอียดงาน')

@section('content')
<div class="w-full p-4 md:p-6 shadow bg-base-200 rounded-2xl">

    @php
        $typeLabels = $rec->type_labels;
        $modeLabel = $rec->work_mode_label;

        $statusLabel = [
            'open' => 'เผยแพร่แล้ว',
            'draft' => 'ฉบับร่าง',
            'closed' => 'ปิดรับแล้ว',
        ][$rec->rc_status] ?? ($rec->rc_status ?: '-');

        $isExpired = $rec->rc_expire_at
            ? \Illuminate\Support\Carbon::parse($rec->rc_expire_at)->endOfDay()->isPast()
            : false;
    @endphp

    {{-- Header --}}
    <div class="flex flex-col gap-4 pb-5 mb-6 border-b">
        <div class="flex items-center justify-between">
            <a
                href="{{ (auth()->check() && auth()->user()->role === 'jobber')
                    ? route('jobber.jobs.index')
                    : route('jobs.index') }}"
                class="text-sm text-blue-600 hover:underline"
            >
                ← กลับไปหน้าหางาน
            </a>
        </div>

        <div class="flex items-start gap-4">
            <div class="w-14 h-14 overflow-hidden bg-white border rounded-full shrink-0">
                @if ($company && $company->co_profile_img)
                    <img src="{{ asset('storage/' . $company->co_profile_img) }}" class="object-cover w-full h-full" alt="company-logo">
                @else
                    <img src="{{ asset('image/web-image/logo.png') }}" class="object-contain w-full h-full p-1" alt="default-logo">
                @endif
            </div>
            <div class="min-w-0">
                <h1 class="text-xl font-semibold md:text-2xl text-base-content break-words">{{ $rec->rc_title }}</h1>
                <p class="text-sm text-gray-500">{{ $company->co_name ?? 'ไม่ระบุบริษัท' }}</p>

                <div class="flex flex-wrap gap-2 mt-3 text-xs">
                    @foreach($typeLabels as $typeLabel)
                        <span class="px-3 py-1 text-blue-700 bg-blue-100 rounded-full">{{ $typeLabel }}</span>
                    @endforeach
                    <span class="px-3 py-1 text-emerald-700 bg-emerald-100 rounded-full">{{ $modeLabel }}</span>
                    <span class="px-3 py-1 rounded-full {{ $rec->rc_status === 'open' ? 'text-green-700 bg-green-100' : 'text-gray-700 bg-gray-100' }}">
                        {{ $statusLabel }}
                    </span>
                    @if($rec->rc_expire_at)
                        <span class="px-3 py-1 rounded-full {{ $isExpired ? 'text-red-700 bg-red-100' : 'text-amber-700 bg-amber-100' }}">
                            {{ $isExpired ? 'หมดอายุแล้ว' : 'ยังเปิดรับสมัคร' }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

        {{-- Main --}}
        <div class="space-y-6 lg:col-span-8">

            {{-- Overview --}}
            <div class="p-5 bg-white shadow rounded-xl">
                <h2 class="text-lg font-semibold">ภาพรวมตำแหน่งงาน</h2>
                <div class="grid grid-cols-1 gap-3 mt-4 sm:grid-cols-2">
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">เงินเดือน</p>
                        <p class="text-sm font-medium text-gray-800">{{ $rec->rc_salary ?: '-' }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">สถานที่ทำงาน</p>
                        <p class="text-sm font-medium text-gray-800">{{ $rec->rc_location_text ?: '-' }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">โพสต์เมื่อ</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ optional($rec->rc_posted_at)->timezone('Asia/Bangkok')->format('d/m/Y H:i') ?: '-' }}
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500">หมดอายุประกาศ</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $rec->rc_expire_at ? \Illuminate\Support\Carbon::parse($rec->rc_expire_at)->format('d/m/Y') : '-' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-4">
                    @if($rec->rc_application_url)
                        <a href="{{ $rec->rc_application_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center px-4 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            สมัครผ่านลิงก์ภายนอก
                        </a>
                    @endif
                    @if($rec->rc_location_link)
                        <a href="{{ $rec->rc_location_link }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center px-4 py-2 text-sm text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200">
                            ดูแผนที่สถานที่ทำงาน
                        </a>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            <div class="p-5 bg-white shadow rounded-xl">
                <h2 class="text-lg font-semibold">รายละเอียดงาน</h2>
                <p class="mt-3 text-gray-800 whitespace-pre-line">{{ $rec->rc_description ?: '-' }}</p>
            </div>

            {{-- Requirements --}}
            <div class="p-5 bg-white shadow rounded-xl">
                <h2 class="text-lg font-semibold">คุณสมบัติเบื้องต้น</h2>
                @php
                    $genderLabel = [
                        'any' => 'ไม่จำกัดเพศ',
                        'male' => 'ชาย',
                        'female' => 'หญิง',
                    ][$rec->rc_gender ?? 'any'] ?? 'ไม่จำกัดเพศ';

                    $educationLabel = [
                        'any' => 'ไม่จำกัดวุฒิ',
                        'below_bachelor' => 'ต่ำกว่าปริญญาตรี',
                        'bachelor' => 'ปริญญาตรี',
                        'master' => 'ปริญญาโท',
                    ][$rec->rc_education_level ?? 'any'] ?? 'ไม่จำกัดวุฒิ';

                    $experienceLabel = [
                        'no_experience' => 'ไม่ต้องมีประสบการณ์',
                        '0_1' => '0-1 ปี',
                        '1_3' => '1-3 ปี',
                        '3_5' => '3-5 ปี',
                        'more_5' => 'มากกว่า 5 ปี',
                    ][$rec->rc_experience_level ?? 'no_experience'] ?? 'ไม่ต้องมีประสบการณ์';
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

                <h3 class="mt-4 text-sm font-semibold text-gray-700">คุณสมบัติทั่วไป</h3>
                <p class="mt-3 text-gray-800 whitespace-pre-line">{{ $rec->rc_requirements ?: '-' }}</p>
            </div>

            {{-- Skills --}}
            <div class="p-5 bg-white shadow rounded-xl">
                <h2 class="text-lg font-semibold">ทักษะที่ต้องการ</h2>

                @php
                    $skillItems = collect();

                    // กรณีเก็บแบบ recruitmentSkills (มี skill_group_id, skill_id, proficiency_level)
                    if (isset($rec->recruitmentSkills) && $rec->recruitmentSkills->count()) {
                        $skillItems = $rec->recruitmentSkills->map(function ($row) {
                            return [
                                'group' => $row->skillGroup->name ?? '-',
                                'name'  => $row->skill->name ?? '-',
                                'level' => $row->proficiency_level ?? null,
                            ];
                        });
                    }
                    // กรณีเก็บแบบ many-to-many skills + pivot
                    elseif (isset($rec->skills) && $rec->skills->count()) {
                        $skillItems = $rec->skills->map(function ($skill) {
                            return [
                                'group' => $skill->skillGroup->name ?? '-',
                                'name'  => $skill->name ?? '-',
                                'level' => data_get($skill, 'pivot.proficiency_level'),
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
                        'beginner' => 'bg-sky-100 text-sky-700',
                        'intermediate' => 'bg-emerald-100 text-emerald-700',
                        'advanced' => 'bg-amber-100 text-amber-700',
                        'expert' => 'bg-fuchsia-100 text-fuchsia-700',
                    ];
                @endphp

                @if($skillItems->count())
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($skillItems as $item)
                            @php $levelClass = $levelColorMap[$item['level'] ?? ''] ?? 'bg-gray-100 text-gray-600'; @endphp
                            <div class="min-w-[230px] max-w-full px-3 py-2 border rounded-xl bg-blue-50 border-blue-100 text-slate-800">
                                <div class="mb-1">
                                    <span class="inline-block px-2 py-0.5 text-sm font-medium rounded-lg bg-indigo-100 text-indigo-700">
                                        กลุ่มทักษะ: {{ $item['group'] !== '-' ? $item['group'] : '-' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 text-sm font-medium rounded-lg bg-cyan-100 text-cyan-800">ทักษะ: {{ $item['name'] }}</span>
                                    @if(!empty($item['level']))
                                        <span class="px-2 py-0.5 text-sm font-medium rounded-full {{ $levelClass }}">ระดับ: {{ $levelMap[$item['level']] ?? $item['level'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-sm text-gray-500">-</p>
                @endif
            </div>

            {{-- Languages --}}
            <div class="p-5 bg-white shadow rounded-xl">
                <h2 class="text-lg font-semibold">ภาษาที่ต้องการ</h2>
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
                                    'basic' => 'bg-slate-100 text-slate-700',
                                    'conversational' => 'bg-cyan-100 text-cyan-700',
                                    'fluent' => 'bg-emerald-100 text-emerald-700',
                                    'native' => 'bg-violet-100 text-violet-700',
                                ];
                                $langLevelClass = $langLevelColorMap[$p] ?? 'bg-gray-100 text-gray-600';
                            @endphp

                            <div class="inline-flex flex-col px-3 py-2 border rounded-xl bg-emerald-50 border-emerald-100 text-slate-800">
                                <div class="mb-1">
                                    <span class="inline-block px-2 py-0.5 text-sm font-medium rounded-lg bg-teal-100 text-teal-700">
                                        ภาษา: {{ $lang->language ?? '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="inline-block px-2 py-0.5 text-sm font-medium rounded-full {{ $langLevelClass }}">
                                        ระดับ: {{ $pLabel }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-sm text-gray-500">-</p>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6 lg:col-span-4">

            {{-- Apply card --}}
            <div class="p-5 bg-white shadow rounded-xl lg:sticky lg:top-6">
                <h3 class="text-lg font-semibold">สมัครงาน</h3>
                <p class="mt-1 text-sm text-gray-600">ส่งเรซูเม่ของคุณให้ผู้ประกอบการ</p>

                @php
                    $application = null;
                    $hasApplied = false;

                    if (auth()->check() && auth()->user()->role === 'jobber') {
                        $application = \App\Models\JobApplication::where('recruitment_id', $rec->rc_id)
                            ->where('jobber_id', auth()->id())
                            ->first();
                        $hasApplied = $application && $application->status !== 'withdrawn';
                    }

                    $statusMap = [
                        'applied'   => ['label' => 'สมัครแล้ว',       'box' => 'bg-blue-50 border-blue-500',   'text' => 'text-blue-800',   'tag' => 'bg-blue-100'],
                        'reviewing' => ['label' => 'กำลังพิจารณา',    'box' => 'bg-yellow-50 border-yellow-500','text' => 'text-yellow-800', 'tag' => 'bg-yellow-100'],
                        'accepted'  => ['label' => 'ยอมรับ',          'box' => 'bg-green-50 border-green-500', 'text' => 'text-green-800',  'tag' => 'bg-green-100'],
                        'rejected'  => ['label' => 'ปฏิเสธ',          'box' => 'bg-red-50 border-red-500',     'text' => 'text-red-800',    'tag' => 'bg-red-100'],
                        'withdrawn' => ['label' => 'ถอนการสมัคร',     'box' => 'bg-gray-50 border-gray-500',   'text' => 'text-gray-800',   'tag' => 'bg-gray-100'],
                    ];
                @endphp

                <div class="mt-4">
                    @auth
                        @if(auth()->user()->role === 'jobber')
                            @if($hasApplied)
                                <button onclick="document.getElementById('withdraw_modal').showModal()"
                                        class="w-full px-4 py-2 text-white bg-orange-600 rounded-lg hover:bg-orange-700">
                                    ถอนการสมัคร
                                </button>

                                <dialog id="withdraw_modal" class="modal">
                                    <div class="modal-box">
                                        <h3 class="text-lg font-bold text-orange-600">ยืนยันการถอนการสมัคร</h3>
                                        <p class="py-4">คุณต้องการถอนการสมัครตำแหน่ง <strong>{{ $rec->rc_title }}</strong> ใช่หรือไม่?</p>
                                        <form action="{{ route('jobber.jobs.withdraw', $rec->rc_id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-action">
                                                <button type="button" onclick="document.getElementById('withdraw_modal').close()" class="btn">ยกเลิก</button>
                                                <button type="submit" class="text-white btn bg-orange-600 hover:bg-orange-700">ยืนยันการถอน</button>
                                            </div>
                                        </form>
                                    </div>
                                    <form method="dialog" class="modal-backdrop"><button>close</button></form>
                                </dialog>
                            @else
                                <form action="{{ route('jobber.jobs.apply', $rec->rc_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                        สมัครงานด้วยเรซูเม่ของคุณ
                                    </button>
                                </form>
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
                                    สมัครเมื่อ: {{ optional($application->applied_at)->format('d/m/Y H:i') ?: '-' }}
                                </p>
                                @if($application->reviewed_at)
                                    <p class="text-xs text-gray-600">พิจารณาเมื่อ: {{ $application->reviewed_at->format('d/m/Y H:i') }}</p>
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

            {{-- Company card --}}
            <div class="p-5 bg-white shadow rounded-xl">
                <h3 class="text-lg font-semibold">ข้อมูลบริษัท</h3>
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