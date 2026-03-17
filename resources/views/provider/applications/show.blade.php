@extends('layouts.app')

@section('title', 'รายละเอียดใบสมัคร')

@section('content')
<div class="flex flex-col gap-4 p-4 shadow bg-base-200 rounded-2xl">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('provider.applications.index') }}"
               class="flex items-center justify-center w-9 h-9 border border-gray-400 rounded-2xl bg-base-100 hover:bg-gray-200 transition"
               title="กลับ">
                <i class="fa-solid fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-xl font-semibold mt-1">{{ $application->recruitment->rc_title }}</h1>
                <p class="text-sm opacity-70">ผู้สมัคร: {{ $application->jobber->profile->up_name ?? $application->jobber->email }}</p>
            </div>
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
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 text-sm rounded-full {{ $statusColor }}">{{ $statusLabel }}</span>
            @if($application->status !== 'rejected')
                <form method="POST" action="{{ route('provider.applications.shortlist', $application->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-xl border transition {{ $application->is_shortlisted ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-gray-300 bg-base-100 text-gray-700 hover:bg-gray-50' }}"
                        title="{{ $application->is_shortlisted ? 'นำออกจากตัวเต็ง' : 'บันทึกเป็นตัวเต็ง' }}">
                        <i class="fa-{{ $application->is_shortlisted ? 'solid' : 'regular' }} fa-star"></i>
                        {{ $application->is_shortlisted ? 'ตัวเต็ง' : 'บันทึกตัวเต็ง' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="p-4 border shadow bg-gradient-to-r from-blue-50 via-white to-emerald-50 rounded-2xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold">การติดต่อและเอกสาร</h2>
                <p class="text-sm opacity-70">ติดต่อผู้สมัครหรือดาวน์โหลดเรซูเมสำหรับการสัมภาษณ์</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($application->status !== 'rejected')
                    <a href="mailto:{{ $application->resume->email ?? $application->jobber->email }}"
                       class="inline-flex items-center gap-2 px-3 py-2 border border-indigo-300 text-indigo-700 rounded-xl bg-white hover:bg-indigo-50 transition">
                        <i class="fa-solid fa-envelope"></i> Email
                    </a>
                    @if($application->resume->phone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $application->resume->phone) }}"
                           class="inline-flex items-center gap-2 px-3 py-2 border border-emerald-300 text-emerald-700 rounded-xl bg-white hover:bg-emerald-50 transition">
                            <i class="fa-solid fa-phone"></i> โทร
                        </a>
                    @endif
                @endif
                <a href="{{ route('provider.applications.resume-pdf', $application->id) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 text-white rounded-xl bg-blue-600 hover:bg-blue-700 transition">
                    <i class="fa-solid fa-file-pdf"></i> ดาวน์โหลด Resume (PDF)
                </a>
                @if($application->status === 'rejected')
                    <form method="POST" action="{{ route('provider.applications.destroy', $application->id) }}" onsubmit="return confirm('ยืนยันลบใบสมัครนี้?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 border border-red-300 text-red-700 rounded-xl bg-white hover:bg-red-50 transition">
                            <i class="fa-solid fa-trash"></i> ลบใบสมัคร
                        </button>
                    </form>
                @endif
            </div>
        </div>
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

    {{-- เกี่ยวกับตัวคุณ --}}
    @if($application->resume->summary)
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="mb-3 font-semibold">เกี่ยวกับตัวคุณ</h2>
        <p class="text-sm whitespace-pre-line leading-relaxed">{{ $application->resume->summary }}</p>
    </div>
    @endif

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

    {{-- Internal Note (เฉพาะทีม HR/ผู้ประกาศ) --}}
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Internal Note</h2>
            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">ผู้สมัครไม่เห็นข้อมูลนี้</span>
        </div>
        <form action="{{ route('provider.applications.internal-note', $application->id) }}" method="POST" class="space-y-3">
            @csrf
            @method('PATCH')
            <textarea name="internal_note" rows="4"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="บันทึกโน้ตภายใน เช่น จุดแข็ง จุดที่ต้องสัมภาษณ์เพิ่ม ความเห็นจากทีม...">{{ old('internal_note', $application->internal_note) }}</textarea>
            @error('internal_note')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">บันทึกโน้ต</button>
            </div>
        </form>
    </div>

    {{-- อัปเดตสถานะ --}}
    @if($application->status === 'reviewing')
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        <h2 class="font-semibold mb-3">ผลการพิจารณา</h2>

        {{-- hidden forms สำหรับ submit --}}
        <form id="form-rejected" action="{{ route('provider.applications.updateStatus', $application->id) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="rejected">
        </form>
        <form id="form-accepted" action="{{ route('provider.applications.updateStatus', $application->id) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="accepted">
        </form>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('provider.applications.index') }}"
               class="px-4 py-2 border border-gray-400 rounded-lg hover:bg-gray-100">
                ยกเลิก
            </a>
            <button type="button" id="btn-rejected"
                class="px-4 py-2 text-red-600 border border-red-400 rounded-lg hover:bg-red-50">
                ไม่ผ่าน
            </button>
            <button type="button" id="btn-accepted"
                class="px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700">
                ผ่าน
            </button>
        </div>
    </div>
    @endif

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

    const applicantName = "{{ $application->resume->first_name }} {{ $application->resume->last_name }}";
    const jobTitle      = "{{ $application->recruitment->rc_title }}";

    // ปุ่ม ไม่ผ่าน
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
            if (result.isConfirmed) {
                document.getElementById('form-rejected').submit();
            }
        });
    });

    // ปุ่ม ผ่าน
    document.getElementById('btn-accepted')?.addEventListener('click', () => {
        Swal.fire({
            title: 'ยืนยันผ่านการพิจารณา',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-check" style="margin-right:6px"></i> ยืนยัน ผ่าน',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('form-accepted').submit();
            }
        });
    });
</script>
@endpush
