@extends('layouts.app')

@section('title', 'แก้ไขประกาศงาน')

@section('content')
<div class="max-w-4xl p-4 mx-auto shadow bg-base-200 rounded-2xl">
    <h1 class="mb-4 text-xl font-semibold">แก้ไขประกาศงาน</h1>

    <form method="POST"
          action="{{ $isAdmin
              ? route('admin.recruitments.update', $rec->rc_id)
              : route('provider.recruitments.update', $rec->rc_id) }}">
        @csrf @method('PATCH')

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="text-sm opacity-70">ชื่องาน</label>
                <input type="text" name="rc_title" class="w-full border border-gray-300 input input-bordered"
                       value="{{ old('rc_title', $rec->rc_title) }}" required>
                @error('rc_title') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="text-sm opacity-70">รายละเอียด</label>
                <textarea name="rc_description" rows="5" class="w-full border border-gray-300 textarea textarea-bordered" required>{{ old('rc_description', $rec->rc_description) }}</textarea>
                @error('rc_description') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="text-sm opacity-70">คุณสมบัติ/ข้อกำหนด</label>
                <textarea name="rc_requirements" rows="4" class="w-full border border-gray-300 textarea textarea-bordered">{{ old('rc_requirements', $rec->rc_requirements) }}</textarea>
                @error('rc_requirements') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">เงินเดือน (ข้อความอิสระ)</label>
                <input type="text" name="rc_salary" class="w-full border border-gray-300 input input-bordered"
                       placeholder="30,000 / ตามตกลง"
                       value="{{ old('rc_salary', $rec->rc_salary) }}">
                @error('rc_salary') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">โหมดการทำงาน</label>
                <select name="rc_work_mode" class="w-full border border-gray-300 select select-bordered" required>
                    @foreach (['onsite'=>'Onsite','remote'=>'Remote','hybrid'=>'Hybrid'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('rc_work_mode', $rec->rc_work_mode)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
                @error('rc_work_mode') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">ประเภท</label>
                <select name="rc_type" class="w-full border border-gray-300 select select-bordered" required>
                    @foreach (['full-time'=>'Full-time','part-time'=>'Part-time','intern'=>'Intern','freelance'=>'Freelance'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('rc_type', $rec->rc_type)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
                @error('rc_type') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">สถานะ</label>
                <select name="rc_status" class="w-full border border-gray-300 select select-bordered" required>
                    @foreach (['open'=>'เปิดรับ','closed'=>'ปิดรับ','draft'=>'ฉบับร่าง'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('rc_status', $rec->rc_status)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
                @error('rc_status') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">สถานที่ (ข้อความ)</label>
                <input type="text" name="rc_location_text" class="w-full border border-gray-300 input input-bordered"
                       value="{{ old('rc_location_text', $rec->rc_location_text) }}">
                @error('rc_location_text') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">ลิงก์แผนที่/สถานที่</label>
                <input type="url" name="rc_location_link" class="w-full border border-gray-300 input input-bordered"
                       value="{{ old('rc_location_link', $rec->rc_location_link) }}">
                @error('rc_location_link') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">ลิงก์สมัครงาน</label>
                <input type="url" name="rc_application_url" class="w-full border border-gray-300 input input-bordered"
                       value="{{ old('rc_application_url', $rec->rc_application_url) }}">
                @error('rc_application_url') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">โพสต์เมื่อ</label>
                {{-- datetime-local ต้องเป็นรูปแบบ Y-m-d\TH:i --}}
                @php
                    $postedVal = optional($rec->rc_posted_at)->format('Y-m-d\TH:i');
                @endphp
                <input type="datetime-local" name="rc_posted_at" class="w-full border border-gray-300 input input-bordered"
                       value="{{ old('rc_posted_at', $postedVal) }}">
                @error('rc_posted_at') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">หมดอายุ</label>
                <input type="date" name="rc_expire_at" class="w-full border border-gray-300 input input-bordered"
                       value="{{ old('rc_expire_at', optional($rec->rc_expire_at)->format('Y-m-d')) }}">
                @error('rc_expire_at') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="flex justify-start gap-2 mt-6">
      <button class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">บันทึก</button>

            @if ($isAdmin)
                <a href="{{ route('admin.providers.recruitments.index', $rec->rc_u_id) }}" class="btn">ยกเลิก</a>
            @else
                <a href="{{ route('provider.recruitments.index') }}" class="btn">ยกเลิก</a>
            @endif
        </div>
    </form>
</div>
@endsection
