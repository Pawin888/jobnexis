@extends('layouts.app')

@section('title', 'หางาน')

@section('content')
    <div class="w-full p-6 shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-between pb-4 mb-4 border-b">
            <div>
                <h1 class="text-2xl font-semibold">หางานที่เปิดรับสมัคร</h1>
                <p class="text-sm opacity-70">ประกาศเปิดรับทั้งหมด {{ number_format($recs->total()) }} รายการ</p>
            </div>
        </div>

        <form method="GET" class="grid items-end grid-cols-1 gap-4 md:grid-cols-5">
            <fieldset class="fieldset md:col-span-2">
                <legend class="mb-1 fieldset-legend">ค้นหา</legend>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                       class="w-full border border-gray-300 input input-bordered"
                       placeholder=" ชื่องาน / รายละเอียด / คุณสมบัติ">
            </fieldset>
            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">ประเภท</legend>
                <select name="type" class="w-full border border-gray-300 select select-bordered">
                    <option value="">— ทั้งหมด —</option>
                    @foreach (['full-time' => 'Full-time', 'part-time' => 'Part-time', 'intern' => 'Intern', 'freelance' => 'Freelance'] as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">โหมดทำงาน</legend>
                <select name="work_mode" class="w-full border border-gray-300 select select-bordered">
                    <option value="">— ทั้งหมด —</option>
                    @foreach (['onsite' => 'Onsite', 'remote' => 'Remote', 'hybrid' => 'Hybrid'] as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['work_mode'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </fieldset>
            <div class="flex gap-2 md:col-span-1">
                <button class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ค้นหา</button>
                <a href="{{ url()->current() }}" class="btn">ล้าง</a>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($recs as $r)
                @php $company = $companies[$r->rc_u_id] ?? null; @endphp
                <div class="p-4 bg-white shadow rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 overflow-hidden bg-gray-100 rounded-full">
                            @if($company && $company->co_profile_img)
                                <img src="{{ asset('storage/'.$company->co_profile_img) }}" class="object-cover w-full h-full" />
                            @else
                                <img src="{{ asset('image/web-image/logo.png') }}" class="object-contain w-full h-full p-1" />
                            @endif
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-base-content line-clamp-1">
                                <a href="{{ (auth()->check() && auth()->user()->role==='jobber') ? route('jobber.jobs.show', $r->rc_id) : route('jobs.show', $r->rc_id) }}" class="hover:text-blue-600">{{ $r->rc_title }}</a>
                            </h2>
                            <p class="text-sm text-gray-500 line-clamp-1">{{ $company->co_name ?? 'ไม่ระบุบริษัท' }}</p>
                        </div>
                    </div>

                    <p class="mt-3 text-sm text-gray-700 line-clamp-2">{{ $r->rc_description }}</p>

                    <div class="flex flex-col gap-2 mt-3 text-xs">
                        <div class="flex flex-wrap gap-2 ">
                            <span class="px-2 py-1 text-blue-700 bg-blue-100 rounded-full">{{ ucfirst($r->rc_type) }}</span>
                        <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">{{ ucfirst($r->rc_work_mode) }}</span>
                    </div>

                        @if($r->rc_salary)
                            <span class="px-2 py-1 text-gray-700 bg-gray-100 rounded-full ">เงินเดือน: {{ $r->rc_salary }}</span>
                        @endif
                        @if($r->rc_location_text)
                            <span class="px-2 py-1 text-gray-700 bg-gray-100 rounded-full line-clamp-2">สถานที่: {{ $r->rc_location_text }}</span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <span class="text-xs text-gray-500">โพสต์เมื่อ {{ optional($r->rc_posted_at)->timezone('Asia/Bangkok')->format('Y-m-d H:i') }}</span>
                        @if($r->rc_application_url)
                            <div class="flex items-center gap-2">
                                <a href="{{ (auth()->check() && auth()->user()->role==='jobber') ? route('jobber.jobs.show', $r->rc_id) : route('jobs.show', $r->rc_id) }}" class="px-2 py-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50">ดูรายละเอียด</a>
                                <a href="{{ $r->rc_application_url }}" target="_blank" rel="noopener" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">สมัคร</a>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <a href="{{ (auth()->check() && auth()->user()->role==='jobber') ? route('jobber.jobs.show', $r->rc_id) : route('jobs.show', $r->rc_id) }}" class="px-4 py-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50">ดูรายละเอียด</a>
                                <button class="px-4 py-2 text-gray-600 bg-gray-200 rounded-lg cursor-not-allowed" disabled>สมัคร</button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 bg-white shadow rounded-xl md:col-span-3">ไม่พบงานที่ตรงกับเงื่อนไข</div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center mt-6">
            {{ $recs->links() }}
        </div>
    </div>
@endsection
