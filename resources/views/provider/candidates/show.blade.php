@extends('layouts.app')

@section('title', 'รายละเอียดเรซูเม่ผู้สมัคร')

@section('content')
<div class="flex flex-col gap-4 p-4 shadow bg-base-200 rounded-2xl">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('provider.candidates.index') }}"
               class="flex items-center justify-center w-9 h-9 border border-gray-400 rounded-2xl bg-base-100 hover:bg-gray-200 transition"
               title="กลับ">
                <i class="fa-solid fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-xl font-semibold mt-1">รายละเอียดเรซูเม่ผู้สมัคร</h1>
                <p class="text-sm opacity-70">{{ trim(($resume->first_name ?? '').' '.($resume->last_name ?? '')) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('provider.candidates.save', $resume->id) }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-xl border transition {{ $isSaved ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-gray-300 bg-base-100 text-gray-700 hover:bg-gray-50' }}">
                    <i class="fa-{{ $isSaved ? 'solid' : 'regular' }} fa-bookmark"></i>
                    {{ $isSaved ? 'บันทึกแล้ว' : 'บันทึกผู้สมัคร' }}
                </button>
            </form>
            <a href="{{ route('provider.candidates.resume-pdf', $resume->id) }}"
               class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-white rounded-xl bg-blue-600 hover:bg-blue-700 transition">
                <i class="fa-solid fa-file-pdf"></i> ดาวน์โหลด Resume
            </a>
        </div>
    </div>

    <div class="p-4 border shadow bg-gradient-to-r from-blue-50 via-white to-emerald-50 rounded-2xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold">เชิญสมัครงาน</h2>
                <p class="text-sm opacity-70">เลือกประกาศงานที่เปิดรับเพื่อเชิญผู้สมัครคนนี้</p>
            </div>
            <form method="POST" action="{{ route('provider.candidates.invite', $resume->id) }}" class="flex items-center gap-2">
                @csrf
                <select name="recruitment_id" class="h-10 border border-gray-300 rounded-lg px-2 text-sm min-w-[220px]" required {{ $openRecruitments->isEmpty() ? 'disabled' : '' }}>
                    <option value="">เลือกประกาศงาน</option>
                    @foreach($openRecruitments as $job)
                        <option value="{{ $job->rc_id }}">{{ $job->rc_title }}</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="h-10 inline-flex items-center justify-center px-4 text-sm text-gray-700 transition border border-gray-300 rounded-xl bg-base-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-400 disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ $openRecruitments->isEmpty() ? 'disabled' : '' }}>
                    <i class="fa-solid fa-paper-plane mr-1"></i> เชิญสมัครงาน
                </button>
            </form>
        </div>
        @if($openRecruitments->isEmpty())
            <p class="text-xs text-amber-600 mt-2">ยังไม่มีประกาศงานที่เปิดรับสำหรับการเชิญสมัคร</p>
        @endif
    </div>

    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">ข้อมูลส่วนตัว</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div>
                <p class="opacity-60">ชื่อ-นามสกุล</p>
                <p class="font-medium">{{ trim(($resume->first_name ?? '').' '.($resume->middle_name ?? '').' '.($resume->last_name ?? '')) ?: '-' }}</p>
            </div>
            <div>
                <p class="opacity-60">อีเมล</p>
                <p class="font-medium">{{ $resume->email ?? $resume->user->email ?? '-' }}</p>
            </div>
            <div>
                <p class="opacity-60">เบอร์โทร</p>
                <p class="font-medium">{{ $resume->phone ?? '-' }}</p>
            </div>
            <div>
                <p class="opacity-60">สถานที่ที่ต้องการ</p>
                <p class="font-medium">{{ $resume->preferred_location ?: '-' }}</p>
            </div>
            <div>
                <p class="opacity-60">วันเกิด</p>
                <p class="font-medium">{{ $resume->birth_date ? \Carbon\Carbon::parse($resume->birth_date)->format('d/m/Y') : '-' }}</p>
            </div>
            <div>
                <p class="opacity-60">เพศ</p>
                <p class="font-medium">{{ ['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ'][$resume->gender] ?? '-' }}</p>
            </div>
        </div>
    </div>

    @if($resume->summary)
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-3 font-semibold">เกี่ยวกับตัวคุณ</h2>
        <p class="text-sm whitespace-pre-line leading-relaxed">{{ $resume->summary }}</p>
    </div>
    @endif

    @if($resume->workExperiences->count())
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">ประสบการณ์ทำงาน</h2>
        <div class="flex flex-col gap-3">
            @foreach($resume->workExperiences as $work)
            <div class="pl-4 border-l-4 border-blue-500">
                <p class="font-bold">{{ $work->job_title }}</p>
                <p class="text-sm opacity-70">{{ $work->company_name }}</p>
                <p class="text-xs opacity-60">
                    {{ $work->start_date }} — {{ $work->is_current ? 'ปัจจุบัน' : ($work->end_date ?? '-') }}
                </p>
                @if($work->description)
                    <p class="text-sm mt-1">{{ $work->description }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($resume->educations->count())
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">การศึกษา</h2>
        <div class="flex flex-col gap-3">
            @foreach($resume->educations as $edu)
            <div class="pl-4 border-l-4 border-green-500">
                <p class="font-bold">{{ $edu->field_of_study }}</p>
                <p class="text-sm opacity-70">{{ $edu->institution }}</p>
                <p class="text-xs opacity-60">{{ $edu->education_level }} · {{ $edu->start_year }} — {{ $edu->end_year ?? 'ปัจจุบัน' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($resume->resumeSkills->count())
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-4 font-semibold">ทักษะ</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($resume->resumeSkills as $skill)
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
