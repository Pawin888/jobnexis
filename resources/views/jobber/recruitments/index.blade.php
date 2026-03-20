@extends('layouts.app')

@section('title', 'หางาน')

@section('content')
    @php
        $topMatches = $topMatches ?? collect();
        $recommendedJobs = $recommendedJobs ?? collect();
        $matchingEnabled = $matchingEnabled ?? false;
        $viewRouteResolver = function ($jobId) {
            return (auth()->check() && auth()->user()->role === 'jobber')
                ? route('jobber.jobs.show', $jobId)
                : route('jobs.show', $jobId);
        };
    @endphp

    <div class="w-full p-6 border shadow-sm bg-base-200/90 rounded-2xl border-base-300">
        <div class="flex items-center justify-between pb-4 mb-4 border-b">
            <div>
                <h1 class="text-2xl font-semibold">หางานที่เปิดรับสมัคร</h1>
                <p class="text-sm opacity-70">ประกาศเปิดรับทั้งหมด {{ number_format($recs->total()) }} รายการ</p>
            </div>
            @if($matchingEnabled)
                <div class="text-right">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs rounded-full bg-slate-100 text-slate-700 border border-slate-200"
                        title="สูตรคะแนนความเหมาะสม: ทักษะ 45%, ระดับทักษะ 20%, ภาษา 10%, ประสบการณ์ 10%, การศึกษา 5%, สถานที่ 5%, เพศ 5%">
                        สูตรคะแนนความเหมาะสม
                        <i class="fa-regular fa-circle-question"></i>
                    </span>
                </div>
            @endif
        </div>

        <form method="GET" class="grid items-end grid-cols-1 gap-4 md:grid-cols-12">
            <fieldset class="fieldset md:col-span-4">
                <legend class="mb-1 fieldset-legend">ค้นหา</legend>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                       class=" pl-2 w-full border border-gray-300 input input-bordered"
                       placeholder="ชื่องาน / รายละเอียด / คุณสมบัติ">
            </fieldset>
            <fieldset class="fieldset md:col-span-3">
                <legend class="mb-1 fieldset-legend">ประเภท</legend>
                <select name="type" class=" pl-2 w-full border border-gray-300 select select-bordered">
                    <option value="">— ทั้งหมด —</option>
                    @foreach (['full-time' => 'เต็มเวลา (Full-time)', 'part-time' => 'พาร์ทไทม์ (Part-time)', 'intern' => 'ฝึกงาน (Internship)', 'freelance' => 'ฟรีแลนซ์ (Freelance)'] as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </fieldset>
            <fieldset class="fieldset md:col-span-3">
                <legend class="mb-1 fieldset-legend">โหมดทำงาน</legend>
                <select name="work_mode" class=" pl-2 w-full border border-gray-300 select select-bordered">
                    <option value="">— ทั้งหมด —</option>
                        @foreach (['onsite' => 'เข้าออฟฟิศ (Work on Site)', 'remote' => 'ทำที่บ้าน (Work from Home)', 'hybrid' => 'ผสมผสาน (Hybrid Work)', 'distributed' => 'ทำที่ไหนก็ได้ (Distributed Work)'] as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['work_mode'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </fieldset>
            <div class="grid w-full grid-cols-2 gap-2 md:col-span-2">
                <button type="submit" class="inline-flex items-center justify-center w-full h-10 px-4 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
                    ค้นหา
                </button>

                <a href="{{ url()->current() }}" class="btn inline-flex items-center justify-center w-full h-10">
                    ล้าง
                </a>
            </div>
        </form>

        @if($matchingEnabled && $topMatches->isNotEmpty())
            <div class="mt-6 p-5 bg-gradient-to-r from-cyan-50 via-white to-emerald-50 border border-cyan-100 rounded-2xl">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h2 class="text-lg font-bold text-slate-800">งานที่เหมาะกับคุณ</h2>
                    <span class="text-xs px-2 py-1 rounded-full bg-cyan-100 text-cyan-700">5 อันดับแรก</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3 mt-4">
                    @foreach($topMatches as $job)
                        @php
                            $meta = $job->matching_meta ?? [];
                            $company = $companies[$job->rc_u_id] ?? null;
                        @endphp
                                <a href="{{ $viewRouteResolver($job->rc_id) }}" class="block p-3 bg-white border border-slate-200 rounded-xl hover:shadow-md transition"
                                    title="ทักษะ 45% | ระดับทักษะ 20% | ภาษา 10% | ประสบการณ์ 10% | การศึกษา 5% | สถานที่ 5% | เพศ 5%">
                            <p class="text-xs text-gray-500 line-clamp-1">{{ $company->co_name ?? 'ไม่ระบุบริษัท' }}</p>
                            <p class="text-sm font-semibold text-slate-800 line-clamp-2 mt-1">{{ $job->rc_title }}</p>
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700">ความเหมาะสม {{ (int) ($meta['total_score'] ?? 0) }}%</span>
                                <span class="text-slate-500">AI {{ (int) round(((float) ($meta['embedding_similarity'] ?? 0)) * 100) }}%</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($matchingEnabled && $recommendedJobs->isNotEmpty())
            <div class="mt-4 p-5 bg-white border border-slate-200 rounded-2xl">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h2 class="text-lg font-bold text-slate-800">แนะนำสำหรับคุณ</h2>
                    <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">AI + หมวดงานใกล้เคียง</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 mt-4">
                    @foreach($recommendedJobs as $job)
                        @php
                            $meta = $job->matching_meta ?? [];
                            $company = $companies[$job->rc_u_id] ?? null;
                            $typeLabels = collect($job->type_labels ?? []);
                        @endphp
                                <a href="{{ $viewRouteResolver($job->rc_id) }}" class="block p-3 border rounded-xl bg-slate-50 border-slate-200 hover:border-emerald-300 hover:bg-white transition"
                                    title="แนะนำจากคะแนนความเหมาะสม + AI + ความใกล้เคียงหมวดงาน/โหมดงาน">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-800 line-clamp-1">{{ $job->rc_title }}</p>
                                <span class="text-xs px-2 py-1 rounded-full bg-slate-200 text-slate-700">{{ (int) ($meta['total_score'] ?? 0) }}%</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 line-clamp-1">{{ $company->co_name ?? 'ไม่ระบุบริษัท' }}</p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach($typeLabels->take(2) as $tag)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-emerald-100 text-emerald-700">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

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
                    $meta = $r->matching_meta ?? [];
                    $breakdown = $meta['breakdown'] ?? [];
                    $defined = $meta['criteria_defined'] ?? [];
                    $factorLabel = function (string $key, string $label) use ($defined, $breakdown) {
                        $isDefined = (bool) ($defined[$key] ?? false);
                        if (!$isDefined) {
                            return $label . ' ไม่ระบุ';
                        }

                        return $label . ' ' . (int) ($breakdown[$key] ?? 0) . '%';
                    };
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
                                <a href="{{ $viewRouteResolver($r->rc_id) }}" class="hover:text-blue-600">{{ $r->rc_title }}</a>
                            </h2>
                            <p class="text-sm text-gray-500 line-clamp-1">{{ $company->co_name ?? 'ไม่ระบุบริษัท' }}</p>
                        </div>
                    </div>

                    @if($matchingEnabled)
                        <div class="mt-3 p-3 rounded-xl border border-cyan-100 bg-cyan-50/70">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-cyan-900">ความเหมาะสม {{ (int) ($meta['total_score'] ?? 0) }}%</p>
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-white border border-cyan-200 text-cyan-700">AI {{ (int) round(((float) ($meta['embedding_similarity'] ?? 0)) * 100) }}%</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] text-slate-600">
                                <span>{{ $factorLabel('skill_match', 'ทักษะ') }}</span>
                                <span>{{ $factorLabel('skill_level', 'ระดับทักษะ') }}</span>
                                <span>{{ $factorLabel('language', 'ภาษา') }}</span>
                                <span>{{ $factorLabel('experience', 'ประสบการณ์') }}</span>
                                <span>{{ $factorLabel('education', 'การศึกษา') }}</span>
                                <span>{{ $factorLabel('location', 'สถานที่') }}</span>
                                <span>{{ $factorLabel('gender', 'เพศ') }}</span>
                            </div>
                            <div class="mt-2 flex items-center gap-2 text-[11px]">
                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">สำคัญ {{ (int) ($meta['required_skills_matched'] ?? 0) }}/{{ (int) ($meta['required_skills_total'] ?? 0) }}</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">โบนัส {{ (int) ($meta['optional_skills_matched'] ?? 0) }}/{{ (int) ($meta['optional_skills_total'] ?? 0) }}</span>
                            </div>
                        </div>
                    @endif

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
                            <a href="{{ $viewRouteResolver($r->rc_id) }}" class="px-4 py-2 text-blue-700 transition-colors border border-blue-600 rounded-lg hover:bg-blue-600 hover:text-white">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 bg-white shadow rounded-xl md:col-span-3">ไม่พบงานที่ตรงกับเงื่อนไข</div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="flex flex-col items-center justify-center gap-3 mt-6 md:flex-row md:justify-between">
            <div class="text-sm text-gray-600">
                @if($matchingEnabled)
                    เรียงตามคะแนนความเหมาะสมสูงสุด
                @else
                    เรียงตามวันที่โพสต์ล่าสุด
                @endif
            </div>
            <div class="flex items-center gap-2">
                <label for="perPage" class="text-sm text-gray-700">แสดง</label>
                <select id="perPage" onchange="window.location.href='{{ url()->current() }}?'+new URLSearchParams({...Object.fromEntries(new URLSearchParams(window.location.search)), perPage:this.value}).toString()" class="h-9 px-2 border border-gray-300 rounded-lg text-sm">
                    @foreach([12,24,36] as $n)
                        <option value="{{ $n }}" {{ (int) ($filters['perPage'] ?? 12) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-center mt-4">
            {{ $recs->links() }}
        </div>
    </div>
@endsection
