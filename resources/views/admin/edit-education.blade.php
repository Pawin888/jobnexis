@extends('layouts.app')

@section('title', 'แก้ไขโปรไฟล์สถานศึกษา')

@section('content')
    <div class="p-4 border shadow bg-base-200 rounded-2xl">
        @if (session('success'))
            <div class="p-3 my-4 text-green-700 bg-green-100 rounded">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-3 text-sm alert alert-error">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST"
            action="{{ $isAdmin
                ? route('admin.profile-education.store', ['userId' => $targetUserId])
                : route('profile-education.store.self') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @csrf

            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">ชื่อหน่วยงาน/สถาบัน *</legend>
                <input name="e_name" type="text" class="w-full pl-2 border border-gray-300 input"
                    value="{{ old('e_name', $profile->e_name ?? '') }}" required />
            </fieldset>

            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">อีเมล *</legend>
                <input name="e_email" type="email" class="w-full pl-2 border border-gray-300 input"
                    value="{{ old('e_email', $profile->e_email ?? '') }}" required />
            </fieldset>

            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">เบอร์โทร</legend>
                <input name="e_phone" type="text" class="w-full pl-2 border border-gray-300 input"
                    value="{{ old('e_phone', $profile->e_phone ?? '') }}" />
            </fieldset>

            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">เว็บไซต์</legend>
                <input name="e_website" type="url" class="w-full pl-2 border border-gray-300 input"
                    placeholder="https://example.ac.th" value="{{ old('e_website', $profile->e_website ?? '') }}" />
            </fieldset>

            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">วันก่อตั้ง/วันสำคัญ</legend>
                <input name="e_birthday" type="date" class="w-full pl-2 border border-gray-300 input"
                    value="{{ old('e_birthday', $profile && $profile->e_birthday ? \Illuminate\Support\Carbon::parse($profile->e_birthday)->format('Y-m-d') : '') }}" />
            </fieldset>

            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">เลขที่ใบอนุญาต/รหัส</legend>
                <input name="e_number" type="text" class="w-full pl-2 border border-gray-300 input"
                    value="{{ old('e_number', $profile->e_number ?? '') }}" />
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="mb-1 fieldset-legend">ที่อยู่</legend>
                <input name="e_address" type="text" class="w-full pl-2 border border-gray-300 input"
                    value="{{ old('e_address', $profile->e_address ?? '') }}" />
            </fieldset>

            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">จังหวัด</legend>
                <select name="e_province" class="pl-2 border border-gray-300 select w-72">
                    <option value="">-- เลือกจังหวัด --</option>
                    @foreach ($provinces as $prov)
                        <option value="{{ $prov }}"
                            {{ old('e_province', $profile->e_province ?? '') == $prov ? 'selected' : '' }}>
                            {{ $prov }}
                        </option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="mb-1 fieldset-legend">รายละเอียด</legend>
                <textarea name="e_detail" rows="4" class="w-full pl-2 border border-gray-300 textarea" placeholder="รายละเอียดเพิ่มเติม">{{ old('e_detail', $profile->e_detail ?? '') }}</textarea>
            </fieldset>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-6 py-2 text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700">บันทึก</button>
                <a href="{{ route('education.dashboard') }}" class="px-6 py-2 btn">ยกเลิก</a>
            </div>
        </form>
    </div>
@endsection
