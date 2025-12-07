@extends('layouts.app')

@section('title', 'เพิ่มประกาศงาน')

@section('content')
    <div class="max-w-4xl p-4 mx-auto shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">
                เพิ่มประกาศงาน
                <span class="opacity-70">สำหรับ {{ $company->co_name ?? ($provider->email ?? '-') }}</span>
            </h1>
        </div>

        <form method="POST"
            action="{{ $isAdmin ? route('admin.providers.recruitments.store', $ownerId) : route('provider.recruitments.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="text-sm opacity-70">ชื่องาน</label>
                    <input type="text" name="rc_title" class="w-full border border-gray-300 input input-bordered" required
                        value="{{ old('rc_title') }}">
                    @error('rc_title')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm opacity-70">รายละเอียด</label>
                    <textarea name="rc_description" rows="5" class="w-full border border-gray-300 textarea textarea-bordered"
                        required>{{ old('rc_description') }}</textarea>
                    @error('rc_description')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm opacity-70">คุณสมบัติ/ข้อกำหนด</label>
                    <textarea name="rc_requirements" rows="4" class="w-full border border-gray-300 textarea textarea-bordered">{{ old('rc_requirements') }}</textarea>
                    @error('rc_requirements')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm opacity-70">เงินเดือน (ข้อความอิสระ)</label>
                    <input type="text" name="rc_salary" class="w-full border border-gray-300 input input-bordered"
                        placeholder="30,000 / ตามตกลง" value="{{ old('rc_salary') }}">
                    @error('rc_salary')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm opacity-70">โหมดการทำงาน</label>
                    <select name="rc_work_mode" class="w-full border border-gray-300 select select-bordered" required>
                        @foreach (['onsite' => 'Onsite', 'remote' => 'Remote', 'hybrid' => 'Hybrid'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('rc_work_mode', 'onsite') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('rc_work_mode')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm opacity-70">ประเภท</label>
                    <select name="rc_type" class="w-full border border-gray-300 select select-bordered" required>
                        @foreach (['full-time' => 'Full-time', 'part-time' => 'Part-time', 'intern' => 'Intern', 'freelance' => 'Freelance'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('rc_type', 'full-time') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('rc_type')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="text-sm opacity-70">สถานที่ (ข้อความ)</label>
                    <input type="text" name="rc_location_text" class="w-full border border-gray-300 input input-bordered"
                        value="{{ old('rc_location_text') }}">
                    @error('rc_location_text')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm opacity-70">ลิงก์แผนที่/สถานที่</label>
                    <input type="url" name="rc_location_link" class="w-full border border-gray-300 input input-bordered"
                        value="{{ old('rc_location_link') }}">
                    @error('rc_location_link')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm opacity-70">ลิงก์สมัครงาน</label>
                    <input type="url" name="rc_application_url"
                        class="w-full border border-gray-300 input input-bordered" value="{{ old('rc_application_url') }}">
                    @error('rc_application_url')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm opacity-70">โพสต์เมื่อ</label>
                    <input type="datetime-local" name="rc_posted_at"
                        class="w-full border border-gray-300 input input-bordered" value="{{ old('rc_posted_at') }}">
                    @error('rc_posted_at')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm opacity-70">หมดอายุ</label>
                    <input type="date" name="rc_expire_at" class="w-full border border-gray-300 input input-bordered"
                        value="{{ old('rc_expire_at') }}">
                    @error('rc_expire_at')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm opacity-70">สถานะ</label>
                    <div class="flex items-center gap-3 p-3 bg-white border rounded-lg shadow-sm">
                        <span>เผยแพร่ทันที</span>
                        <span class="text-xs text-gray-500">ปิด = ฉบับร่าง</span>
                        <input type="checkbox" id="toggle-status" class="toggle toggle-primary"
                            {{ old('rc_status', 'draft') === 'open' ? 'checked' : '' }}>
                        <input type="hidden" name="rc_status" id="rc_status_input" value="{{ old('rc_status', 'draft') }}">
                        <span class="text-xs text-gray-500">เปิด = เผยแพร่</span>
                    </div>
                    @error('rc_status')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror

                    <!-- เก็บ select เดิมไว้เป็น fallback แต่ซ่อน -->
                    <div class="hidden">
                        <select id="rc_status_select" class="w-full border border-gray-300 select select-bordered">
                            @foreach (['open' => 'เปิดรับ', 'closed' => 'ปิดรับ', 'draft' => 'ฉบับร่าง'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('rc_status', 'draft') === $k)>{{ $v }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 mt-6">
                <button class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">บันทึก</button>

                @if ($isAdmin)
                    <a href="{{ route('admin.providers.recruitments.index', $ownerId) }}" class="btn">ยกเลิก</a>
                @else
                    <a href="{{ route('provider.recruitments.index') }}" class="btn">ยกเลิก</a>
                @endif
            </div>
        </form>
        <script>
            (function() {
                const toggle = document.getElementById('toggle-status');
                const input = document.getElementById('rc_status_input');
                const select = document.getElementById('rc_status_select');
                const sync = () => {
                    const val = toggle.checked ? 'open' : 'draft';
                    input.value = val;
                    if (select) select.value = val;
                };
                if (toggle) {
                    toggle.addEventListener('change', sync);
                    sync();
                }
            })();
        </script>
    </div>
@endsection
