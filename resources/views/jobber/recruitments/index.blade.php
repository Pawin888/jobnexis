@extends('layouts.app')

@section('title', 'หางาน')

@section('content')
    <div class="w-full p-6 border shadow-sm bg-base-200/90 rounded-2xl border-base-300">
        <div class="flex items-center justify-between pb-4 mb-4 border-b">
            <div>
                <h1 class="text-2xl font-semibold">หางานที่เปิดรับสมัคร</h1>
                <p class="text-sm opacity-70">ประกาศเปิดรับทั้งหมด {{ number_format($recs->total()) }} รายการ</p>
            </div>
        </div>

        <form method="GET" class="grid items-end grid-cols-1 gap-4 md:grid-cols-12">
            <fieldset class="fieldset md:col-span-4">
                <legend class="mb-1 fieldset-legend">ค้นหา</legend>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                       class="w-full border border-gray-300 input input-bordered"
                       placeholder=" ชื่องาน / รายละเอียด / คุณสมบัติ">
            </fieldset>
            <fieldset class="fieldset md:col-span-3">
                <legend class="mb-1 fieldset-legend">ประเภท</legend>
                <select name="type" class="w-full border border-gray-300 select select-bordered">
                    <option value="">— ทั้งหมด —</option>
                    @foreach (['full-time' => 'เต็มเวลา (Full-time)', 'part-time' => 'พาร์ทไทม์ (Part-time)', 'intern' => 'ฝึกงาน (Internship)', 'freelance' => 'ฟรีแลนซ์ (Freelance)'] as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </fieldset>
            <fieldset class="fieldset md:col-span-3">
                <legend class="mb-1 fieldset-legend">โหมดทำงาน</legend>
                <select name="work_mode" class="w-full border border-gray-300 select select-bordered">
                    <option value="">— ทั้งหมด —</option>
                        @foreach (['onsite' => 'เข้าออฟฟิศ (Work on Site)', 'remote' => 'ทำที่บ้าน (Work from Home)', 'hybrid' => 'ผสมผสาน (Hybrid Work)', 'distributed' => 'ทำที่ไหนก็ได้ (Distributed Work)'] as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['work_mode'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </fieldset>
            <div class="flex gap-2 md:col-span-2">
                <button class="flex-1 px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    ค้นหา
                </button>

                <a href="{{ url()->current() }}" class="flex items-center justify-center flex-1 px-4 py-2 text-white bg-gray-500 rounded-lg hover:bg-gray-600">
                    ล้าง
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($recs as $r)
                @php
                    $company = $companies[$r->rc_u_id] ?? null;
                    $typeLabels = collect($r->type_labels ?? []);
                    $modeLabel = $r->work_mode_label ?: 'ไม่ระบุโหมดการทำงาน';
                    $descriptionText = trim((string) ($r->rc_description ?? '')) !== '' ? $r->rc_description : 'ไม่ได้ระบุรายละเอียดงาน';
                    $salaryText = trim((string) ($r->rc_salary ?? '')) !== '' ? $r->rc_salary : 'ไม่ระบุ';
                    $locationText = trim((string) ($r->rc_location_text ?? '')) !== '' ? $r->rc_location_text : 'ไม่ระบุ';
                    $postedDate = optional($r->rc_posted_at)->timezone('Asia/Bangkok')->format('d/m/Y') ?: '-';
                    $isAlwaysOpen = empty($r->rc_expire_at);
                    $expireDateText = $r->rc_expire_at
                        ? \Illuminate\Support\Carbon::parse($r->rc_expire_at)->format('d/m/Y')
                        : null;
                @endphp
                <div class="flex flex-col h-full p-4 transition-all duration-200 bg-white border shadow-sm rounded-2xl border-slate-100 hover:-translate-y-0.5 hover:shadow-lg">
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

                    <p class="mt-3 text-sm leading-relaxed text-gray-700 line-clamp-3">{{ $descriptionText }}</p>

                    <div class="flex flex-col gap-2 mt-3 text-xs">
                        <div class="flex flex-wrap gap-2 ">
                            @if($typeLabels->count())
                                @foreach ($typeLabels as $typeLabel)
                                    <span class="px-2 py-1 text-blue-700 bg-blue-100 rounded-full">{{ $typeLabel }}</span>
                                @endforeach
                            @else
                                <span class="px-2 py-1 text-gray-700 bg-gray-100 rounded-full">ไม่ระบุประเภทงาน</span>
                            @endif
                            <span class="px-2 py-1 rounded-full {{ $r->work_mode_label ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">{{ $modeLabel }}</span>
                            @if($isAlwaysOpen)
                                <span class="px-2 py-1 text-green-700 bg-green-100 rounded-full">เปิดรับตลอด</span>
                            @else
                                <span class="px-2 py-1 text-amber-700 bg-amber-100 rounded-full">หมดเขตรับสมัคร {{ $expireDateText }}</span>
                            @endif
                        </div>

                        <span class="px-2 py-1 text-gray-700 bg-gray-100 rounded-full">เงินเดือน: {{ $salaryText }}</span>
                        <span class="px-2 py-1 text-gray-700 bg-gray-100 rounded-full line-clamp-2">สถานที่: {{ $locationText }}</span>
                    </div>

                    <div class="flex items-end justify-between pt-4 mt-auto border-t border-slate-100">
                        <span class="text-xs text-gray-500">โพสต์เมื่อ {{ $postedDate }}</span>
                        <div class="flex items-center gap-2">
                            <a href="{{ (auth()->check() && auth()->user()->role==='jobber') ? route('jobber.jobs.show', $r->rc_id) : route('jobs.show', $r->rc_id) }}" class="px-4 py-2 text-blue-700 transition-colors border border-blue-600 rounded-lg hover:bg-blue-600 hover:text-white">ดูรายละเอียด</a>
                        </div>
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
