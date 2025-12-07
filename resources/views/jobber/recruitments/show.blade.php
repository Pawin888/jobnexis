@extends('layouts.app')

@section('title', 'รายละเอียดงาน')

@section('content')
    <div class="w-full p-6 shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-between pb-4 mb-4 border-b">
            <div class="flex items-center gap-3">
                <a href="{{ (auth()->check() && auth()->user()->role==='jobber') ? route('jobber.jobs.index') : route('jobs.index') }}" class="text-blue-600 hover:underline">← กลับไปหน้าหางาน</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="md:col-span-2 p-5 bg-white rounded-xl shadow">
                <div class="flex items-start gap-3">
                    <div class="w-14 h-14 overflow-hidden bg-gray-100 rounded-full">
                        @if($company && $company->co_profile_img)
                            <img src="{{ asset('storage/'.$company->co_profile_img) }}" class="object-cover w-full h-full" />
                        @else
                            <img src="{{ asset('image/web-image/logo.png') }}" class="object-contain w-full h-full p-1" />
                        @endif
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-base-content">{{ $rec->rc_title }}</h1>
                        <p class="text-sm text-gray-500">{{ $company->co_name ?? 'ไม่ระบุบริษัท' }}</p>
                        <div class="flex flex-wrap gap-2 mt-2 text-xs">
                            <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700">{{ ucfirst($rec->rc_type) }}</span>
                            <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">{{ ucfirst($rec->rc_work_mode) }}</span>
                            @if($rec->rc_salary)
                                <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700">เงินเดือน: {{ $rec->rc_salary }}</span>
                            @endif
                            @if($rec->rc_location_text)
                                <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700">สถานที่: {{ $rec->rc_location_text }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold">รายละเอียดงาน</h2>
                        <p class="mt-2 whitespace-pre-line text-gray-800">{{ $rec->rc_description }}</p>
                    </div>
                    @if($rec->rc_requirements)
                    <div>
                        <h2 class="text-lg font-semibold">คุณสมบัติ/ข้อกำหนด</h2>
                        <p class="mt-2 whitespace-pre-line text-gray-800">{{ $rec->rc_requirements }}</p>
                    </div>
                    @endif
                </div>

                <div class="flex items-center justify-between mt-8 text-sm text-gray-500">
                    <span>โพสต์เมื่อ {{ optional($rec->rc_posted_at)->timezone('Asia/Bangkok')->format('Y-m-d H:i') }}</span>
                    @if($rec->rc_expire_at)
                        <span>หมดอายุ {{ \Illuminate\Support\Carbon::parse($rec->rc_expire_at)->format('Y-m-d') }}</span>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <div class="p-5 bg-white rounded-xl shadow">
                    <h3 class="text-lg font-semibold">สมัครงาน</h3>
                    <p class="mt-1 text-sm text-gray-600">สมัครผ่านลิงก์ของบริษัท</p>
                    <div class="mt-4">
                        @if($rec->rc_application_url)
                            <a href="{{ $rec->rc_application_url }}" target="_blank" rel="noopener" class="w-full px-4 py-2 text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 inline-block">ไปหน้าสมัคร</a>
                        @else
                            <button class="w-full px-4 py-2 text-gray-600 bg-gray-200 rounded-lg cursor-not-allowed" disabled>ยังไม่เปิดรับผ่านลิงก์</button>
                        @endif
                    </div>
                </div>

                <div class="p-5 bg-white rounded-xl shadow">
                    <h3 class="text-lg font-semibold">ข้อมูลบริษัท</h3>
                    <p class="mt-2 text-sm text-gray-800">{{ $company->co_name ?? 'ไม่ระบุบริษัท' }}</p>
                    @if($company?->co_email)
                        <p class="mt-1 text-sm text-gray-600">อีเมล: {{ $company->co_email }}</p>
                    @endif
                    @if($company?->co_phone)
                        <p class="mt-1 text-sm text-gray-600">โทร: {{ $company->co_phone }}</p>
                    @endif
                    @if($company?->co_address)
                        <p class="mt-1 text-sm text-gray-600 whitespace-pre-line">ที่อยู่: {{ $company->co_address }}</p>
                    @endif
                    @if($rec->rc_location_link)
                        <a href="{{ $rec->rc_location_link }}" target="_blank" rel="noopener" class="inline-block mt-3 text-blue-600 hover:underline">ดูแผนที่/สถานที่</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
