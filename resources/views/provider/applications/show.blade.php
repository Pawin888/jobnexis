@extends('layouts.app')

@section('title', 'รายละเอียดใบสมัคร')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-28">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('provider.applications.index') }}" class="link link-primary">← กลับ</a>
            <h1 class="text-3xl font-bold mt-4">{{ $application->recruitment->rc_title }}</h1>
            <p class="text-gray-600 mt-2">ผู้สมัคร: {{ $application->jobber->profile->up_name ?? $application->jobber->name }}</p>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="col-span-2 space-y-6">
                <!-- Jobber Profile -->
                <div class="card bg-white shadow-md">
                    <div class="card-body">
                        <h2 class="card-title">ข้อมูลผู้สมัคร</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-500">ชื่อเต็ม</p>
                                <p class="font-bold">{{ $application->resume->first_name }} {{ $application->resume->last_name }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">อีเมล</p>
                                <p class="font-bold">{{ $application->resume->email }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">เบอร์โทร</p>
                                <p class="font-bold">{{ $application->resume->phone }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">ตำแหน่ง</p>
                                <p class="font-bold">{{ $application->resume->summary ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resume Content -->
                <div class="card bg-white shadow-md">
                    <div class="card-body">
                        <h2 class="card-title">รีซูเมต่อ</h2>
                        
                        @if($application->resume->profile_image)
                            <img src="{{ asset('storage/' . $application->resume->profile_image) }}" alt="Profile" class="w-32 h-32 rounded-full object-cover">
                        @endif

                        @if($application->resume->summary)
                            <div>
                                <h3 class="font-bold">สรุปตัวเอง</h3>
                                <p>{{ $application->resume->summary }}</p>
                            </div>
                        @endif

                        <!-- Work Experiences -->
                        @if($application->resume->workExperiences->count())
                            <div>
                                <h3 class="font-bold mt-4">ประสบการณ์การทำงาน</h3>
                                @foreach($application->resume->workExperiences as $work)
                                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                                        <p class="font-bold">{{ $work->position }}</p>
                                        <p class="text-gray-600">{{ $work->company_name }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $work->start_date }} - {{ $work->end_date ?? 'ปัจจุบัน' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Education -->
                        @if($application->resume->educations->count())
                            <div>
                                <h3 class="font-bold mt-4">การศึกษา</h3>
                                @foreach($application->resume->educations as $edu)
                                    <div class="border-l-4 border-green-500 pl-4 py-2">
                                        <p class="font-bold">{{ $edu->major }}</p>
                                        <p class="text-gray-600">{{ $edu->institute_name }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Skills -->
                        @if($application->resume->resumeSkills->count())
                            <div>
                                <h3 class="font-bold mt-4">ทักษะ</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($application->resume->resumeSkills as $skill)
                                        <span class="badge badge-outline">{{ $skill->skill_name ?? $skill->id }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Cover Letter -->
                @if($application->cover_letter)
                    <div class="card bg-white shadow-md">
                        <div class="card-body">
                            <h2 class="card-title">จดหมายสมัคร</h2>
                            <p>{{ $application->cover_letter }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Status Update -->
                <div class="card bg-white shadow-md sticky top-4">
                    <div class="card-body">
                        <h3 class="card-title">สถานะใบสมัคร</h3>
                        <p class="text-sm mb-4">
                            <span class="badge badge-lg {{ $application->status === 'accepted' ? 'badge-success' : ($application->status === 'rejected' ? 'badge-error' : 'badge-info') }}">
                                {{ ['applied' => 'สมัครแล้ว', 'reviewing' => 'กำลังพิจารณา', 'accepted' => 'ยอมรับ', 'rejected' => 'ปฏิเสธ', 'withdrawn' => 'ถอนการสมัคร'][$application->status] ?? $application->status }}
                            </span>
                        </p>

                        <form action="{{ route('provider.applications.updateStatus', $application->id) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <select name="status" class="select select-bordered select-sm w-full">
                                <option value="reviewing" {{ $application->status === 'reviewing' ? 'selected' : '' }}>กำลังพิจารณา</option>
                                <option value="accepted" {{ $application->status === 'accepted' ? 'selected' : '' }}>ยอมรับ</option>
                                <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
                            </select>

                            <textarea name="review_note" placeholder="หมายเหตุการพิจารณา" class="textarea textarea-bordered w-full text-sm">{{ $application->review_note }}</textarea>

                            <button type="submit" class="btn btn-primary w-full">อัปเดตสถานะ</button>
                        </form>

                        <p class="text-xs text-gray-500 mt-4">
                            สมัครเมื่อ: {{ $application->applied_at->format('d/m/Y H:i') }}<br>
                            @if($application->reviewed_at)
                                พิจารณาเมื่อ: {{ $application->reviewed_at->format('d/m/Y H:i') }}
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Application Info -->
                <div class="card bg-white shadow-md mt-4">
                    <div class="card-body">
                        <h3 class="card-title text-base">ข้อมูลประกาศ</h3>
                        <p class="text-sm">
                            <strong>{{ $application->recruitment->rc_title }}</strong><br>
                            <span class="text-gray-600">{{ $application->recruitment->rc_location_text }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection