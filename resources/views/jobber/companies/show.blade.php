@extends('layouts.app')

@section('title', 'ผู้ประกอบการ')

@section('content')
    <div class="w-full p-6 shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-between pb-4 mb-4 border-b">
            <a href="{{ (auth()->check() && auth()->user()->role==='jobber') ? route('jobber.companies.index') : route('companies.index') }}" class="text-blue-600 hover:underline">← ผู้ประกอบการทั้งหมด</a>
        </div>

        @php
            $banner = $company?->co_banner_img ? asset('storage/'.$company->co_banner_img) : asset('image/web-image/work-group.jpg');
            $logo   = $company?->co_profile_img ? asset('storage/'.$company->co_profile_img) : asset('image/web-image/logo.png');
        @endphp

        <div class="overflow-hidden bg-white rounded-xl shadow">
            <div class="relative h-48">
                <img src="{{ $banner }}" class="object-cover w-full h-full" />
                <div class="absolute bottom-[-28px] left-6 w-20 h-20 rounded-full overflow-hidden border-4 border-white bg-white">
                    <img src="{{ $logo }}" class="object-cover w-full h-full" />
                </div>
            </div>
            <div class="px-6 pt-10 pb-6">
                <h1 class="text-2xl font-semibold">{{ $company?->co_name ?? $user->email }}</h1>
                <p class="text-sm text-gray-500">{{ $company?->co_type ?? 'ไม่ระบุประเภท' }} • {{ $company?->co_province ?? 'ไม่ระบุจังหวัด' }}</p>

                <!-- ข้อมูลพื้นฐานทั้งหมดที่บันทึกไว้ -->
                <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
                    @if($company?->co_number)
                        <div class="p-3 bg-base-200 rounded-lg">
                            <div class="text-xs opacity-70">เลขผู้ประกอบการ</div>
                            <div class="font-medium">{{ $company->co_number }}</div>
                        </div>
                    @endif
                    @if($company?->co_type)
                        <div class="p-3 bg-base-200 rounded-lg">
                            <div class="text-xs opacity-70">ประเภทกิจการ</div>
                            <div class="font-medium">{{ $company->co_type }}</div>
                        </div>
                    @endif
                    @if($company?->co_jobber_amount)
                        <div class="p-3 bg-base-200 rounded-lg">
                            <div class="text-xs opacity-70">จำนวนพนักงาน</div>
                            <div class="font-medium">{{ number_format($company->co_jobber_amount) }}</div>
                        </div>
                    @endif
                    @if($company?->co_province)
                        <div class="p-3 bg-base-200 rounded-lg">
                            <div class="text-xs opacity-70">จังหวัด</div>
                            <div class="font-medium">{{ $company->co_province }}</div>
                        </div>
                    @endif
                    @if($company?->co_birthday)
                        @php $founded = \Illuminate\Support\Carbon::parse($company->co_birthday)->format('Y-m-d'); @endphp
                        <div class="p-3 bg-base-200 rounded-lg">
                            <div class="text-xs opacity-70">วันที่ก่อตั้ง</div>
                            <div class="font-medium">{{ $founded }}</div>
                        </div>
                    @endif
                    @if($company?->co_email)
                        <div class="p-3 bg-base-200 rounded-lg">
                            <div class="text-xs opacity-70">อีเมล</div>
                            <div class="font-medium">{{ $company->co_email }}</div>
                        </div>
                    @endif
                    @if($company?->co_phone)
                        <div class="p-3 bg-base-200 rounded-lg">
                            <div class="text-xs opacity-70">โทร</div>
                            <div class="font-medium">{{ $company->co_phone }}</div>
                        </div>
                    @endif
                    @if($company?->co_address)
                        <div class="p-3 bg-base-200 rounded-lg md:col-span-3">
                            <div class="text-xs opacity-70">ที่อยู่</div>
                            <div class="whitespace-pre-line">{{ $company->co_address }}</div>
                        </div>
                    @endif
                    @if($company?->co_details)
                        <div class="p-3 bg-base-200 rounded-lg md:col-span-3">
                            <div class="text-xs opacity-70">รายละเอียดเพิ่มเติม</div>
                            <div class="whitespace-pre-line">{{ $company->co_details }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h2 class="mb-3 text-xl font-semibold">งานที่เปิดรับ</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse($openJobs as $r)
                    <div class="p-4 bg-white rounded-xl shadow">
                        <h3 class="text-lg font-semibold line-clamp-1">
                            <a href="{{ (auth()->check() && auth()->user()->role==='jobber') ? route('jobber.jobs.show', $r->rc_id) : route('jobs.show', $r->rc_id) }}" class="hover:text-blue-600">{{ $r->rc_title }}</a>
                        </h3>
                        <p class="mt-2 text-sm text-gray-700 line-clamp-3">{{ $r->rc_description }}</p>
                        <div class="flex flex-wrap gap-2 mt-3 text-xs">
                            <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700">{{ ucfirst($r->rc_type) }}</span>
                            <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">{{ ucfirst($r->rc_work_mode) }}</span>
                            @if($r->rc_salary)
                                <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ $r->rc_salary }}</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <span class="text-xs text-gray-500">โพสต์เมื่อ {{ optional($r->rc_posted_at)->timezone('Asia/Bangkok')->format('Y-m-d H:i') }}</span>
                            @if($r->rc_application_url)
                                <a href="{{ $r->rc_application_url }}" target="_blank" rel="noopener" class="px-3 py-1 text-white bg-blue-600 rounded-lg hover:bg-blue-700 text-sm">สมัคร</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white rounded-xl shadow text-gray-500 md:col-span-3">ยังไม่มีงานเปิดรับ</div>
                @endforelse
            </div>
            <div class="flex justify-center mt-6">
                {{ $openJobs->links() }}
            </div>
        </div>
    </div>
@endsection
