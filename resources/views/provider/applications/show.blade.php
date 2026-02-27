@extends('layouts.app')

@section('title', 'รายละเอียดใบสมัคร')

@section('content')
<div class="flex flex-col gap-4 p-4 shadow bg-base-200 rounded-2xl">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold mt-1">{{ $application->recruitment->rc_title }}</h1>
            <p class="text-sm opacity-70">ผู้สมัคร: {{ $application->jobber->profile->up_name ?? $application->jobber->email }}</p>
        </div>
        @php
            $statusColor = [
                'reviewing' => 'text-yellow-700 bg-yellow-200',
                'accepted'  => 'text-green-600 bg-green-200',
                'rejected'  => 'text-red-600 bg-red-200',
            ][$application->status] ?? 'bg-gray-200';
            $statusLabel = [
                'reviewing' => 'รอประเมิน',
                'accepted'  => 'ยอมรับ',
                'rejected'  => 'ปฏิเสธ',
            ][$application->status] ?? $application->status;
        @endphp
        <span class="px-3 py-1 text-sm rounded-full {{ $statusColor }}">{{ $statusLabel }}</span>
    </div>

    {{-- ข้อมูลส่วนตัว --}}
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">ข้อมูลส่วนตัว</h2>
        <div class="flex items-center gap-4 mb-4">
            @if($application->resume->profile_image)
                <img src="{{ asset('storage/' . $application->resume->profile_image) }}"
                     class="object-cover w-20 h-20 rounded-full border-4 border-gray-200">
            @else
                <div class="flex items-center justify-center w-20 h-20 rounded-full bg-gray-200">
                    <i class="fa-solid fa-user text-3xl text-gray-400"></i>
                </div>
            @endif
            <div>
                <p class="text-lg font-bold">
                    {{ $application->resume->first_name }}
                    {{ $application->resume->middle_name }}
                    {{ $application->resume->last_name }}
                </p>
                @if($application->resume->summary)
                    <p class="text-sm opacity-70">{{ $application->resume->summary }}</p>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <p class="opacity-60">วันเกิด</p>
                <p class="font-medium">{{ $application->resume->birth_date ? \Carbon\Carbon::parse($application->resume->birth_date)->format('d/m/Y') : '-' }}</p>
            </div>
            <div>
                <p class="opacity-60">เพศ</p>
                <p class="font-medium">{{ ['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ'][$application->resume->gender] ?? '-' }}</p>
            </div>
            <div>
                <p class="opacity-60">อีเมล</p>
                <p class="font-medium">{{ $application->resume->email ?? '-' }}</p>
            </div>
            <div>
                <p class="opacity-60">เบอร์โทร</p>
                <p class="font-medium">{{ $application->resume->phone ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- ประสบการณ์ทำงาน --}}
    @if($application->resume->workExperiences->count())
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">ประสบการณ์ทำงาน</h2>
        <div class="flex flex-col gap-3">
            @foreach($application->resume->workExperiences as $work)
            <div class="pl-4 border-l-4 border-blue-500">
                <p class="font-bold">{{ $work->job_title }}</p>
                <p class="text-sm opacity-70">{{ $work->company_name }}</p>
                <p class="text-xs opacity-60">
                    {{ $work->start_date }} —
                    {{ $work->is_current ? 'ปัจจุบัน' : ($work->end_date ?? '-') }}
                </p>
                @if($work->description)
                    <p class="text-sm mt-1">{{ $work->description }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- การศึกษา --}}
    @if($application->resume->educations->count())
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">การศึกษา</h2>
        <div class="flex flex-col gap-3">
            @foreach($application->resume->educations as $edu)
            <div class="pl-4 border-l-4 border-green-500">
                <p class="font-bold">{{ $edu->field_of_study }}</p>
                <p class="text-sm opacity-70">{{ $edu->institution }}</p>
                <p class="text-xs opacity-60">{{ $edu->education_level }} · {{ $edu->start_year }} — {{ $edu->end_year ?? 'ปัจจุบัน' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ทักษะ --}}
    @if($application->resume->resumeSkills->count())
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">ทักษะ</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($application->resume->resumeSkills as $skill)
                <span class="px-3 py-1 text-sm border rounded-full bg-base-100">
                    {{ $skill->skillGroup->name ?? '-' }} · {{ $skill->skill->name ?? '-' }}
                    <span class="opacity-60 text-xs ml-1">
                        · {{ ['beginner' => 'เริ่มต้น', 'intermediate' => 'ปานกลาง', 'advanced' => 'ขั้นสูง', 'expert' => 'ผู้เชี่ยวชาญ'][$skill->proficiency_level] ?? $skill->proficiency_level }}
                    </span>
                </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ใบรับรอง --}}
    @if($application->resume->certificates->count())
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">ใบรับรอง/ประกาศนียบัตร</h2>
        <div class="flex flex-col gap-3">
            @foreach($application->resume->certificates as $cert)
            <div class="pl-4 border-l-4 border-yellow-400">
                <p class="font-bold">{{ $cert->name }}</p>
                @if($cert->issued_by)
                    <p class="text-sm opacity-70">{{ $cert->issued_by }} {{ $cert->issued_year ? '· '.$cert->issued_year : '' }}</p>
                @endif
                @if($cert->file_path)
                    <a href="{{ asset('storage/' . $cert->file_path) }}" target="_blank"
                       class="text-xs text-blue-600 hover:underline">ดูไฟล์</a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ภาษา --}}
    @if($application->resume->languages->count())
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">ทักษะด้านภาษา</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($application->resume->languages as $lang)
                <span class="px-3 py-1 text-sm border rounded-full bg-base-100">
                    {{ $lang->language }}
                    <span class="opacity-60 text-xs ml-1">
                        · {{ ['basic' => 'พื้นฐาน', 'conversational' => 'สนทนาได้', 'fluent' => 'คล่องแคล่ว', 'native' => 'เจ้าของภาษา'][$lang->level] ?? $lang->level }}
                    </span>
                </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Cover Letter --}}
    @if($application->cover_letter)
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">จดหมายสมัครงาน</h2>
        <p class="text-sm whitespace-pre-line">{{ $application->cover_letter }}</p>
    </div>
    @endif

    {{-- ข้อมูลการสมัคร --}}
    <div class="p-4 border shadow bg-base-200 rounded-2xl text-sm">
        <h2 class="font-semibold mb-3">ข้อมูลการสมัคร</h2>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <p class="opacity-60">วันที่สมัคร</p>
                <p class="font-medium">{{ \Carbon\Carbon::parse($application->applied_at)->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="opacity-60">ตำแหน่งงาน</p>
                <p class="font-medium">{{ $application->recruitment->rc_title }}</p>
            </div>
            @if($application->resume->available_start_date)
            <div>
                <p class="opacity-60">เริ่มงานได้เมื่อ</p>
                <p class="font-medium">{{ \Carbon\Carbon::parse($application->resume->available_start_date)->format('d/m/Y') }}</p>
            </div>
            @endif
            @if($application->resume->preferred_location)
            <div>
                <p class="opacity-60">สถานที่ที่ต้องการ</p>
                <p class="font-medium">{{ $application->resume->preferred_location }}</p>
            </div>
            @endif
            @if($application->resume->expected_salary)
            <div>
                <p class="opacity-60">เงินเดือนที่คาดหวัง</p>
                <p class="font-medium">{{ number_format($application->resume->expected_salary) }} บาท</p>
            </div>
            @endif
        </div>
    </div>

    {{-- อัปเดตสถานะ --}}
    @if($application->status === 'reviewing')
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="font-semibold mb-3">ผลการพิจารณา</h2>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('provider.applications.index') }}"
               class="px-4 py-2 border border-gray-400 rounded-lg hover:bg-gray-100">
                ยกเลิก
            </a>
            <form action="{{ route('provider.applications.updateStatus', $application->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <button type="submit"
                    onclick="return confirm('ยืนยันไม่ผ่านการพิจารณา?')"
                    class="px-4 py-2 text-red-600 border border-red-400 rounded-lg hover:bg-red-50">
                    ไม่ผ่าน
                </button>
            </form>
            <form action="{{ route('provider.applications.updateStatus', $application->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="accepted">
                <button type="submit"
                    onclick="return confirm('ยืนยันผ่านการพิจารณา?')"
                    class="px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700">
                    ผ่าน
                </button>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
