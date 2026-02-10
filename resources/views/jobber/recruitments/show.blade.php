@extends('layouts.app')

@section('title', 'รายละเอียดงาน')

@section('content')
<div class="w-full p-6 shadow bg-base-200 rounded-2xl">

    {{-- ================= Header ================= --}}
    <div class="flex items-center justify-between pb-4 mb-4 border-b">
        <a
            href="{{ (auth()->check() && auth()->user()->role === 'jobber')
                ? route('jobber.jobs.index')
                : route('jobs.index') }}"
            class="text-blue-600 hover:underline"
        >
            ← กลับไปหน้าหางาน
        </a>
    </div>

    {{-- ================= Layout ================= --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

        {{-- ================= รายละเอียดงาน ================= --}}
        <div class="p-5 bg-white shadow md:col-span-2 rounded-xl">

            {{-- Company + Job title --}}
            <div class="flex items-start gap-3">
                <div class="w-14 h-14 overflow-hidden bg-gray-100 rounded-full">
                    @if ($company && $company->co_profile_img)
                        <img src="{{ asset('storage/' . $company->co_profile_img) }}"
                             class="object-cover w-full h-full">
                    @else
                        <img src="{{ asset('image/web-image/logo.png') }}"
                             class="object-contain w-full h-full p-1">
                    @endif
                </div>

                <div>
                    <h1 class="text-2xl font-semibold text-base-content">
                        {{ $rec->rc_title }}
                    </h1>

                    <p class="text-sm text-gray-500">
                        {{ $company->co_name ?? 'ไม่ระบุบริษัท' }}
                    </p>

                    <div class="flex flex-wrap gap-2 mt-2 text-xs">
                        <span class="px-2 py-1 text-blue-700 bg-blue-100 rounded-full">
                            {{ ucfirst($rec->rc_type) }}
                        </span>

                        <span class="px-2 py-1 text-emerald-700 bg-emerald-100 rounded-full">
                            {{ ucfirst($rec->rc_work_mode) }}
                        </span>

                        @if ($rec->rc_salary)
                            <span class="px-2 py-1 text-gray-700 bg-gray-100 rounded-full">
                                เงินเดือน: {{ $rec->rc_salary }}
                            </span>
                        @endif

                        @if ($rec->rc_location_text)
                            <span class="px-2 py-1 text-gray-700 bg-gray-100 rounded-full">
                                สถานที่: {{ $rec->rc_location_text }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Job description --}}
            <div class="mt-6 space-y-6">
                <div>
                    <h2 class="text-lg font-semibold">รายละเอียดงาน</h2>
                    <p class="mt-2 text-gray-800 whitespace-pre-line">
                        {{ $rec->rc_description }}
                    </p>
                </div>

                @if ($rec->rc_requirements)
                    <div>
                        <h2 class="text-lg font-semibold">คุณสมบัติ/ข้อกำหนด</h2>
                        <p class="mt-2 text-gray-800 whitespace-pre-line">
                            {{ $rec->rc_requirements }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between mt-8 text-sm text-gray-500">
                <span>
                    โพสต์เมื่อ
                    {{ optional($rec->rc_posted_at)->timezone('Asia/Bangkok')->format('Y-m-d H:i') }}
                </span>

                @if ($rec->rc_expire_at)
                    <span>
                        หมดอายุ {{ \Illuminate\Support\Carbon::parse($rec->rc_expire_at)->format('Y-m-d') }}
                    </span>
                @endif
            </div>
        </div>

        {{-- ================= สมัครงาน ================= --}}
        <div class="p-5 bg-white shadow rounded-xl">
            <h3 class="text-lg font-semibold">สมัครงาน</h3>
            <p class="mt-1 text-sm text-gray-600">ส่งเรซูเม่ของคุณให้ผู้ประกอบการ</p>

            <div class="mt-4">
                @auth
                    @if(auth()->user()->role === 'jobber')
                        @php
                            $application = \App\Models\JobApplication::where('recruitment_id', $rec->rc_id)
                                ->where('jobber_id', auth()->id())
                                ->first();
                            
                            $hasApplied = $application && $application->status !== 'withdrawn';
                        @endphp

                        @if($hasApplied)
                            {{-- ถอนการสมัคร --}}
                            <button
                                onclick="document.getElementById('withdraw_modal').showModal()"
                                class="w-full px-4 py-2 text-white bg-orange-600 rounded-lg hover:bg-orange-700"
                            >
                                ถอนการสมัคร
                            </button>

                            <dialog id="withdraw_modal" class="modal">
                                <div class="modal-box">
                                    <h3 class="text-lg font-bold text-orange-600">
                                        ยืนยันการถอนการสมัคร
                                    </h3>

                                    <p class="py-4">
                                        คุณต้องการถอนการสมัครตำแหน่ง
                                        <strong>{{ $rec->rc_title }}</strong>
                                        ใช่หรือไม่?
                                    </p>

                                    <form action="{{ route('jobber.jobs.withdraw', $rec->rc_id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <div class="modal-action">
                                            <button type="button"
                                                onclick="document.getElementById('withdraw_modal').close()"
                                                class="btn">
                                                ยกเลิก
                                            </button>
                                            <button type="submit"
                                                class="text-white btn bg-orange-600 hover:bg-orange-700">
                                                ยืนยันการถอน
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <form method="dialog" class="modal-backdrop">
                                    <button>close</button>
                                </form>
                            </dialog>
                        @else
                            {{-- สมัครงาน --}}
                            <form action="{{ route('jobber.jobs.apply', $rec->rc_id) }}" method="POST">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                                >
                                    สมัครงานด้วยเรซูเม่ของคุณ
                                </button>
                            </form>
                        @endif
                    @else
                        <p class="text-sm text-gray-600">
                            บัญชีผู้ประกอบการไม่สามารถสมัครงานได้
                        </p>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                        class="block w-full px-4 py-2 text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        เข้าสู่ระบบเพื่อสมัครงาน
                    </a>
                @endauth
                
                {{-- แสดงสถานะการสมัคร --}}
                @auth
                    @if(auth()->user()->role === 'jobber' && isset($application) && $application)
                        <div class="p-3 mt-4 border-l-4 rounded-lg
                            @switch($application->status)
                                @case('applied')
                                    bg-blue-50 border-blue-500
                                    @break
                                @case('reviewing')
                                    bg-yellow-50 border-yellow-500
                                    @break
                                @case('accepted')
                                    bg-green-50 border-green-500
                                    @break
                                @case('rejected')
                                    bg-red-50 border-red-500
                                    @break
                                @case('withdrawn')
                                    bg-gray-50 border-gray-500
                                    @break
                            @endswitch
                        ">
                            <div class="flex items-start gap-2">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold
                                        @switch($application->status)
                                            @case('applied') text-blue-800 @break
                                            @case('reviewing') text-yellow-800 @break
                                            @case('accepted') text-green-800 @break
                                            @case('rejected') text-red-800 @break
                                            @case('withdrawn') text-gray-800 @break
                                        @endswitch
                                    ">
                                        สถานะการสมัคร: 
                                        @switch($application->status)
                                            @case('applied') 
                                                <span class="px-2 py-1 text-xs bg-blue-100 rounded">สมัครแล้ว</span>
                                                @break
                                            @case('reviewing') 
                                                <span class="px-2 py-1 text-xs bg-yellow-100 rounded">กำลังพิจารณา</span>
                                                @break
                                            @case('accepted') 
                                                <span class="px-2 py-1 text-xs bg-green-100 rounded">ยอมรับ</span>
                                                @break
                                            @case('rejected') 
                                                <span class="px-2 py-1 text-xs bg-red-100 rounded">ปฏิเสธ</span>
                                                @break
                                            @case('withdrawn') 
                                                <span class="px-2 py-1 text-xs bg-gray-100 rounded">ถอนการสมัคร</span>
                                                @break
                                        @endswitch
                                    </p>
                                    
                                    <p class="mt-1 text-xs text-gray-600">
                                        สมัครเมื่อ: {{ $application->applied_at->format('d/m/Y H:i') }}
                                    </p>
                                    
                                    @if($application->reviewed_at)
                                        <p class="text-xs text-gray-600">
                                            พิจารณาเมื่อ: {{ $application->reviewed_at->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                    
                                    @if($application->review_note)
                                        <p class="mt-2 text-xs text-gray-700">
                                            <strong>หมายเหตุจากผู้ประกอบการ:</strong><br>
                                            {{ $application->review_note }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>

            @if(session('success'))
                <div class="p-3 mt-3 text-sm text-green-700 bg-green-100 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-3 mt-3 text-sm text-red-700 bg-red-100 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- ================= ข้อมูลบริษัท ================= --}}
        <div class="p-5 bg-white shadow rounded-xl">
            <h3 class="text-lg font-semibold">ข้อมูลบริษัท</h3>

            <p class="mt-2 text-sm text-gray-800">
                {{ $company->co_name ?? 'ไม่ระบุบริษัท' }}
            </p>

            @if ($company?->co_email)
                <p class="mt-1 text-sm text-gray-600">
                    อีเมล: {{ $company->co_email }}
                </p>
            @endif

            @if ($company?->co_phone)
                <p class="mt-1 text-sm text-gray-600">
                    โทร: {{ $company->co_phone }}
                </p>
            @endif

            @if ($company?->co_address)
                <p class="mt-1 text-sm text-gray-600 whitespace-pre-line">
                    ที่อยู่: {{ $company->co_address }}
                </p>
            @endif

            @if ($rec->rc_location_link)
                <a href="{{ $rec->rc_location_link }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-block mt-3 text-blue-600 hover:underline">
                    ดูแผนที่/สถานที่
                </a>
            @endif
        </div>

    </div>
</div>
@endsection
