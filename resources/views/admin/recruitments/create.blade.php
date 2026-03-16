@extends('layouts.app')

@section('title', 'เพิ่มประกาศงาน')

@section('content')
<div class="flex flex-col gap-4 p-4 shadow bg-base-200 rounded-2xl">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between bg-base-200 rounded-2xl px-6 py-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight">เพิ่มประกาศงาน</h1>
            <p class="text-sm opacity-60 mt-0.5">
                สำหรับ {{ $company->co_name ?? ($provider->email ?? '-') }}
            </p>
        </div>
    </div>

    {{-- ================= FORM ================= --}}
    <form id="recruitment-form" method="POST" novalidate
        action="{{ $isAdmin
            ? route('admin.providers.recruitments.store', $ownerId)
            : route('provider.recruitments.store') }}">
        @csrf

        @php
            $selectedType = old('rc_type', '');
            $selectedWorkMode = old('rc_work_mode', '');
            $oldSkills = collect(old('skills', []))->filter(fn ($skill) => filled($skill['skill_group_id'] ?? null) || filled($skill['skill_id'] ?? null))->values();
            $langs = collect(old('languages', []))->filter(fn ($lang) => filled($lang['language'] ?? null))->values();
            $tomorrow = now()->addDay()->toDateString();
        @endphp

        <div class="space-y-3">

            {{-- ================= SECTION: ข้อมูลตำแหน่งงาน ================= --}}
            <div class="bg-base-200 rounded-2xl px-5 py-4">
                <h2 class="text-base font-semibold mb-3 flex items-center gap-1.5">
                    <span class="w-1.5 h-5 bg-primary rounded-full inline-block"></span>
                    ข้อมูลตำแหน่งงาน
                </h2>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                    {{-- ชื่องาน --}}
                    <div class="md:col-span-2">
                        <label class="label py-0"><span class="label-text font-medium">ชื่องาน <span class="text-error">*</span></span></label>
                        <input type="text" name="rc_title"
                            class="w-full input input-bordered focus:input-primary border border-base-300 pl-2"
                            placeholder="เช่น Senior Frontend Developer"
                            value="{{ old('rc_title') }}" required>
                        @error('rc_title') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label py-0"><span class="label-text font-medium">รายละเอียดงาน</span></label>
                        <textarea name="rc_description" rows="4"
                            class="w-full textarea textarea-bordered focus:textarea-primary border border-base-300 pl-2"
                            placeholder="ระบุรายละเอียดงาน เช่น หน้าที่ความรับผิดชอบ ลักษณะงาน...">{{ old('rc_description') }}</textarea>
                        @error('rc_description') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- โหมดการทำงาน --}}
                    <div>
                        <label class="label py-0"><span class="label-text font-medium">โหมดการทำงาน</span></label>
                        <select name="rc_work_mode" id="rc_work_mode" class="w-full select select-bordered focus:select-primary border border-base-300 pl-2 work-mode-radio">
                            <option value="">ไม่ระบุ</option>
                            <option value="onsite" @selected((string) $selectedWorkMode === 'onsite')>เข้าออฟฟิศ (Work on Site)</option>
                            <option value="remote" @selected((string) $selectedWorkMode === 'remote')>ทำที่บ้าน (Work from Home)</option>
                            <option value="hybrid" @selected((string) $selectedWorkMode === 'hybrid')>ผสมผสาน (Hybrid Work)</option>
                            <option value="distributed" @selected((string) $selectedWorkMode === 'distributed')>ทำที่ไหนก็ได้ (Distributed Work)</option>
                        </select>
                        @error('rc_work_mode') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="location-fields" class="contents">
                    <div id="location-text-field" class="md:col-span-2">
                        <label class="label py-0"><span class="label-text font-medium">สถานที่</span></label>
                        <select name="rc_location_text" id="rc_location_text" class="w-full select select-bordered focus:select-primary border border-base-300 pl-2">
                            <option value="">-- เลือกจังหวัด --</option>
                            @foreach(config('th_provinces', []) as $province)
                                <option value="{{ $province }}" @selected(old('rc_location_text') === $province)>{{ $province }}</option>
                            @endforeach
                        </select>
                        @error('rc_location_text') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="location-link-field" class="md:col-span-2">
                        <label class="label py-0"><span class="label-text font-medium">ลิงก์สถานที่</span></label>
                        <input type="url" name="rc_location_link" id="rc_location_link"
                            class="w-full input input-bordered focus:input-primary border border-base-300 pl-2"
                            placeholder="https://maps.google.com/..."
                            value="{{ old('rc_location_link') }}">
                        @error('rc_location_link') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    </div>

                    <div>
                        <label class="label py-0"><span class="label-text font-medium">ประเภทงาน</span></label>
                        <select name="rc_type" id="rc_type" class="w-full select select-bordered focus:select-primary border border-base-300 pl-2">
                            <option value="">ไม่ระบุ</option>
                            <option value="full-time" @selected((string) $selectedType === 'full-time')>เต็มเวลา (Full-time)</option>
                            <option value="part-time" @selected((string) $selectedType === 'part-time')>พาร์ทไทม์ (Part-time)</option>
                            <option value="intern" @selected((string) $selectedType === 'intern')>ฝึกงาน (Internship)</option>
                            <option value="freelance" @selected((string) $selectedType === 'freelance')>ฟรีแลนซ์ (Freelance)</option>
                        </select>
                        @error('rc_type') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SECTION: เงินเดือน ================= --}}
            <div class="bg-base-200 rounded-2xl px-5 py-4">
                <h2 class="text-base font-semibold mb-3 flex items-center gap-1.5">
                    <span class="w-1.5 h-5 bg-secondary rounded-full inline-block"></span>
                    เงินเดือน
                </h2>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-2">

                    {{-- เงินเดือน --}}
                    <div class="lg:col-span-2">
                        <label class="label py-0"><span class="label-text font-medium">ช่วงเงินเดือน (บาท)</span></label>
                        <input type="hidden" name="rc_salary" id="rc_salary" value="{{ old('rc_salary') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label class="label py-0"><span class="label-text text-xs opacity-70">ขั้นต่ำ</span></label>
                                <select id="salary_min" class="w-full select select-bordered focus:select-primary border border-base-300 pl-2">
                                    <option value="">ไม่ระบุ</option>
                                    @foreach([10000,15000,20000,25000,30000,35000,40000,45000,50000,60000,70000,80000,100000,120000,150000,200000] as $s)
                                        <option value="{{ $s }}">{{ number_format($s) }} บาท</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="label py-0"><span class="label-text text-xs opacity-70">สูงสุด</span></label>
                                <select id="salary_max" class="w-full select select-bordered focus:select-primary border border-base-300 pl-2">
                                    <option value="">ไม่ระบุ</option>
                                    @foreach([10000,15000,20000,25000,30000,35000,40000,45000,50000,60000,70000,80000,100000,120000,150000,200000] as $s)
                                        <option value="{{ $s }}">{{ number_format($s) }} บาท</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="inline-flex items-center gap-3 cursor-pointer px-3 py-2 rounded-lg border border-base-300 bg-base-100 transition hover:border-blue-300 hover:bg-blue-50">
                            <input type="checkbox" id="salary_negotiable"
                                class="checkbox checkbox-sm border-2 border-slate-500 bg-white checked:bg-blue-600 checked:border-blue-600">
                            <span class="text-sm font-medium text-base-content">เงินเดือนตามตกลง</span>
                        </label>
                        <p class="text-xs opacity-70 mt-1">เลือกช่วงเงินเดือน หรือเลือกตามตกลงเพื่อไม่ระบุช่วงเงินเดือน</p>
                        @error('rc_salary') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                </div>
            </div>

            {{-- ================= SECTION: คุณสมบัติผู้สมัคร ================= --}}
            <div class="bg-base-200 rounded-2xl px-5 py-4">
                <h2 class="text-base font-semibold mb-3 flex items-center gap-1.5">
                    <span class="w-1.5 h-5 bg-warning rounded-full inline-block"></span>
                    คุณสมบัติผู้สมัคร
                </h2>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="label py-0"><span class="label-text font-medium">เพศ</span></label>
                        <select name="rc_gender" class="w-full select select-bordered focus:select-primary border border-base-300 pl-2">
                            @foreach (['any' => 'ไม่จำกัดเพศ', 'male' => 'ชาย', 'female' => 'หญิง'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('rc_gender', 'any') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        @error('rc_gender') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label py-0"><span class="label-text font-medium">วุฒิการศึกษา</span></label>
                        <select name="rc_education_level" class="w-full select select-bordered focus:select-primary border border-base-300 pl-2">
                            @foreach (['any' => 'ไม่จำกัดวุฒิ', 'below_bachelor' => 'ต่ำกว่าปริญญาตรี', 'bachelor' => 'ปริญญาตรี', 'master' => 'ปริญญาโท', 'doctorate' => 'ปริญญาเอก'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('rc_education_level', 'any') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        @error('rc_education_level') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label py-0"><span class="label-text font-medium">ประสบการณ์ทำงาน</span></label>
                        <select name="rc_experience_level" class="w-full select select-bordered focus:select-primary border border-base-300 pl-2">
                            @foreach (['no_experience' => 'ไม่ต้องมีประสบการณ์', '0_1' => '0-1 ปี', '1_3' => '1-3 ปี', '3_5' => '3-5 ปี', 'more_5' => 'มากกว่า 5 ปี'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('rc_experience_level', 'no_experience') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        @error('rc_experience_level') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>


            {{-- ================= SECTION: ทักษะที่ต้องการ ================= --}}
            <div class="bg-base-200 rounded-2xl px-5 py-4">
                <h2 class="text-base font-semibold mb-3 flex items-center gap-1.5">
                    <span class="w-1.5 h-5 bg-warning rounded-full inline-block"></span>
                    ทักษะที่ต้องการ
                </h2>

                <div class="grid items-center gap-2 px-3 mb-1" style="grid-template-columns: 2fr 2fr 1.5fr 2rem">
                    <span class="text-xs font-medium opacity-60">กลุ่มทักษะ <span class="text-error">*</span></span>
                    <span class="text-xs font-medium opacity-60">ทักษะ <span class="text-error">*</span></span>
                    <span class="text-xs font-medium opacity-60">ระดับความชำนาญ <span class="text-error">*</span></span>
                    <span></span>
                </div>
                <div id="skills-wrapper" class="space-y-2">
                    @foreach ($oldSkills as $i => $skill)
                        @php
                            $selectedGroupId = $skill['skill_group_id'] ?? null;
                            $selectedGroup = $skillGroups->firstWhere('id', (int) $selectedGroupId);
                            $selectedSkillId = $skill['skill_id'] ?? null;
                        @endphp
                        <div class="skill-row grid items-center gap-2 p-3 bg-base-100 rounded-xl border border-base-300"
                             style="grid-template-columns: 2fr 2fr 1.5fr 2rem">
                            <div class="relative min-w-0 overflow-hidden">
                                <select name="skills[{{ $i }}][skill_group_id]"
                                    class="select select-bordered select-sm focus:select-primary skill-group w-full opacity-0 absolute inset-0 z-10 cursor-pointer">
                                    <option value="">-- กลุ่มทักษะ --</option>
                                    @foreach ($skillGroups as $group)
                                        <option value="{{ $group->id }}" @selected((string) $selectedGroupId === (string) $group->id)>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <div class="select select-bordered select-sm w-full flex items-center pointer-events-none overflow-hidden">
                                    <span class="truncate text-sm skill-group-label {{ $selectedGroupId ? '' : 'opacity-60' }}">{{ $selectedGroup?->name ?? '-- กลุ่มทักษะ --' }}</span>
                                </div>
                            </div>
                            <div class="relative min-w-0 overflow-hidden">
                                <select name="skills[{{ $i }}][skill_id]"
                                    class="select select-bordered select-sm focus:select-primary skill-select w-full opacity-0 absolute inset-0 z-10 cursor-pointer" {{ $selectedGroup ? '' : 'disabled' }}>
                                    <option value="">-- ทักษะ --</option>
                                    @foreach (($selectedGroup?->skills ?? collect())->sortBy(fn ($item) => mb_strtolower($item->name)) as $groupSkill)
                                        <option value="{{ $groupSkill->id }}" @selected((string) $selectedSkillId === (string) $groupSkill->id)>{{ $groupSkill->name }}</option>
                                    @endforeach
                                </select>
                                <div class="select select-bordered select-sm w-full flex items-center pointer-events-none overflow-hidden">
                                    <span class="truncate text-sm skill-select-label {{ $selectedSkillId ? '' : 'opacity-60' }}">{{ $selectedGroup?->skills?->firstWhere('id', (int) $selectedSkillId)?->name ?? '-- ทักษะ --' }}</span>
                                </div>
                            </div>
                            <select name="skills[{{ $i }}][proficiency_level]" class="select select-bordered select-sm focus:select-primary">
                                @foreach (['beginner' => 'เริ่มต้น', 'intermediate' => 'ปานกลาง', 'advanced' => 'ขั้นสูง', 'expert' => 'ผู้เชี่ยวชาญ'] as $level => $label)
                                    <option value="{{ $level }}" @selected(($skill['proficiency_level'] ?? 'beginner') === $level)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="remove-skill btn btn-xs shrink-0" style="background-color:#ef4444; color:white; border:none; min-width:2rem">✕</button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-skill"
                    class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    เพิ่มทักษะ
                </button>
            </div>

        </div>{{-- end space-y-4 --}}

        {{-- ================= SECTION: LANGUAGES ================= --}}
        @php
            $languageOptions = [
                'ภาษาไทย', 'ภาษาอังกฤษ', 'ภาษาจีนกลาง', 'ภาษาจีนกวางตุ้ง', 'ภาษาญี่ปุ่น', 'ภาษาเกาหลี',
                'ภาษาฝรั่งเศส', 'ภาษาเยอรมัน', 'ภาษาสเปน', 'ภาษาโปรตุเกส', 'ภาษาอิตาลี', 'ภาษาดัตช์',
                'ภาษารัสเซีย', 'ภาษายูเครน', 'ภาษาโปแลนด์', 'ภาษาเช็ก', 'ภาษาสโลวัก', 'ภาษาฮังการี',
                'ภาษาโรมาเนีย', 'ภาษาบัลแกเรีย', 'ภาษาเซอร์เบีย', 'ภาษาโครเอเชีย', 'ภาษาสโลวีเนีย',
                'ภาษาบอสเนีย', 'ภาษาแอลเบเนีย', 'ภาษากรีก', 'ภาษาตุรกี', 'ภาษาอาหรับ', 'ภาษาฮีบรู',
                'ภาษาเปอร์เซีย', 'ภาษาอูรดู', 'ภาษาฮินดี', 'ภาษาเบงกาลี', 'ภาษาปัญจาบ', 'ภาษาคุชราตี',
                'ภาษามราฐี', 'ภาษาทมิฬ', 'ภาษาเตลูกู', 'ภาษากันนาดา', 'ภาษามาลายาลัม', 'ภาษาสิงหล',
                'ภาษาเนปาลี', 'ภาษาพม่า', 'ภาษามลายู', 'ภาษาอินโดนีเซีย', 'ภาษาตากาล็อก', 'ภาษาเวียดนาม',
                'ภาษาลาว', 'ภาษาเขมร', 'ภาษามองโกเลีย', 'ภาษาคาซัค', 'ภาษาอุซเบก', 'ภาษาอาเซอร์ไบจาน',
                'ภาษาจอร์เจีย', 'ภาษาอาร์เมเนีย', 'ภาษาสวาฮีลี', 'ภาษาอัมฮาริก', 'ภาษาโซมาลี',
                'ภาษาโยรูบา', 'ภาษาอิกโบ', 'ภาษาเฮาซา', 'ภาษาแอฟริคานส์', 'ภาษาซูลู', 'ภาษาโคซา',
                'ภาษาเดนมาร์ก', 'ภาษานอร์เวย์', 'ภาษาสวีเดน', 'ภาษาฟินแลนด์', 'ภาษาไอซ์แลนด์',
                'ภาษาเอสโตเนีย', 'ภาษาลัตเวีย', 'ภาษาลิทัวเนีย', 'ภาษาไอริช', 'ภาษาเวลส์', 'ภาษาสก็อตเกลิก',
                'ภาษามอลตา', 'ภาษาคาตาลัน', 'ภาษาบาสก์', 'ภาษากาลิเซีย', 'ภาษาละติน',
                'ภาษากลาง (ลิงกวาฟรังกา)', 'ภาษานาวาโฮ', 'ภาษาอินุกติตุต', 'ภาษาเมารี', 'ภาษาฮาวาย',
                'ภาษาซามัว', 'ภาษาตองกา', 'ภาษาฟิจิ', 'ภาษาปาปิอาเมนโต', 'ภาษาเครโอลเฮติ',
                'ภาษาลักเซมเบิร์ก', 'ภาษามาซิโดเนีย', 'ภาษาเบลารุส', 'ภาษามอลโดวา', 'ภาษาคีร์กีซ',
                'ภาษาทาจิก', 'ภาษาเตอร์กเมน', 'ภาษาเคิร์ด', 'ภาษาอัสสัม', 'ภาษาโอเดีย', 'ภาษาสันสกฤต'
            ];
        @endphp

        <div class="bg-base-200 rounded-2xl px-6 py-5 mt-4">
            <h2 class="text-base font-semibold mb-3 flex items-center gap-1.5">
                <span class="w-1.5 h-5 bg-info rounded-full inline-block"></span>
                ภาษาที่ต้องการ
            </h2>

            <div class="grid items-center gap-2 px-3 mb-1" style="grid-template-columns: 2fr 2fr 2rem">
                <span class="text-xs font-medium opacity-60">ภาษา <span class="text-error">*</span></span>
                <span class="text-xs font-medium opacity-60">ระดับความชำนาญ <span class="text-error">*</span></span>
                <span></span>
            </div>
            <div id="languages-wrapper" class="space-y-2">
                @foreach ($langs as $i => $lang)
                <div class="language-row grid items-center gap-2 p-3 bg-base-100 rounded-xl border border-base-300"
                    style="grid-template-columns: 2fr 2fr 2rem">

                    <div class="relative min-w-0 overflow-hidden">
                        <select name="languages[{{ $i }}][language]"
                            class="select select-bordered select-sm focus:select-primary lang-select w-full opacity-0 absolute inset-0 z-10 cursor-pointer">
                            <option value="">-- เลือกภาษา --</option>
                            @foreach ($languageOptions as $opt)
                                <option value="{{ $opt }}" {{ ($lang['language'] ?? '') === $opt ? 'selected' : '' }}>
                                    {{ $opt }}
                                </option>
                            @endforeach
                        </select>
                        <div class="select select-bordered select-sm w-full flex items-center pointer-events-none overflow-hidden">
                            @php $selLang = $lang['language'] ?? ''; @endphp
                            <span class="truncate text-sm lang-label {{ $selLang ? '' : 'opacity-60' }}">
                                {{ $selLang ?: '-- เลือกภาษา --' }}
                            </span>
                        </div>
                    </div>

                    <select name="languages[{{ $i }}][proficiency]"
                        class="select select-bordered select-sm w-full">
                        <option value="basic"          {{ ($lang['proficiency'] ?? '')=='basic'          ? 'selected' : '' }}>พื้นฐาน</option>
                        <option value="conversational" {{ ($lang['proficiency'] ?? '')=='conversational' ? 'selected' : '' }}>สนทนาได้</option>
                        <option value="fluent"         {{ ($lang['proficiency'] ?? '')=='fluent'         ? 'selected' : '' }}>คล่องแคล่ว</option>
                        <option value="native"         {{ ($lang['proficiency'] ?? '')=='native'         ? 'selected' : '' }}>เจ้าของภาษา</option>
                    </select>

                    <button type="button"
                        class="remove-language btn btn-xs shrink-0"
                        style="background-color:#ef4444; color:white; border:none; min-width:2rem">
                        ✕
                    </button>
                </div>
                @endforeach
            </div>

            <button type="button" id="add-language"
                class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                เพิ่มภาษา
            </button>

            <div class="mt-3">
                <label class="label py-0"><span class="label-text font-medium">คุณสมบัติทั่วไป</span></label>
                <textarea name="rc_requirements" rows="4"
                    class="w-full textarea textarea-bordered focus:textarea-primary border border-base-300 pl-2"
                    placeholder="ระบุข้อมูลเพิ่มเติมเกี่ยวกับคุณสมบัติ (ถ้ามี)">{{ old('rc_requirements') }}</textarea>
                @error('rc_requirements') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ================= SECTION: ช่วงเวลา ================= --}}
        <div class="bg-base-200 rounded-2xl px-5 py-4 mt-3">
            <h2 class="text-base font-semibold mb-3 flex items-center gap-1.5">
                <span class="w-1.5 h-5 bg-accent rounded-full inline-block"></span>
                ช่วงเวลา
            </h2>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label class="label py-0"><span class="label-text font-medium">วันปิดรับสมัคร</span></label>
                    <input type="date" name="rc_expire_at" id="rc_expire_at"
                        class="w-full input input-bordered focus:input-primary border border-base-300 pl-2"
                        value="{{ old('rc_expire_at') }}" min="{{ $tomorrow }}">
                    @error('rc_expire_at') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    <div class="mt-2">
                        <label class="inline-flex items-center gap-3 cursor-pointer px-3 py-2 rounded-lg border border-base-300 bg-base-100 transition hover:border-blue-300 hover:bg-blue-50">
                            <input type="checkbox" id="expire_no_limit" name="expire_no_limit" value="1"
                                @checked(old('expire_no_limit') === '1')
                                class="checkbox checkbox-sm border-2 border-slate-500 bg-white checked:bg-blue-600 checked:border-blue-600">
                            <span class="text-sm font-medium text-base-content">ไม่กำหนดวันปิด (เปิดรับตลอด)</span>
                        </label>
                        <p class="text-xs opacity-70 mt-1">หากไม่กำหนดวันปิด ประกาศจะเปิดรับสมัครจนกว่าจะปิดด้วยตนเอง</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= ACTIONS ================= --}}
        <div class="flex justify-end gap-2 mt-3 pb-4">
            <a href="{{ $isAdmin
                ? route('admin.providers.recruitments.index', $ownerId)
                : route('provider.recruitments.index') }}"
                class="btn btn-ghost">
                ยกเลิก
            </a>
            <button type="button" id="btn-submit" class="btn btn-primary px-8 gap-2"
                style="background-color:#2563eb; color:white; border:none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                บันทึก
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>

window.SKILLS_BY_GROUP = @json(
    $skillGroups->mapWithKeys(fn ($g) => [
        $g->id => $g->skills->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()
    ])
);

const LANGUAGE_OPTIONS = @json($languageOptions);

document.addEventListener('DOMContentLoaded', () => {

    // ===== Salary =====
    const salaryValues = [10000,15000,20000,25000,30000,35000,40000,45000,50000,60000,70000,80000,100000,120000,150000,200000];
    const salaryMinEl = document.getElementById('salary_min');
    const salaryMaxEl = document.getElementById('salary_max');
    const salaryNegotiableEl = document.getElementById('salary_negotiable');
    const salaryHiddenEl = document.getElementById('rc_salary');

    const formatSalary = n => Number(n).toLocaleString('en-US');

    const updateSalaryMaxOptions = () => {
        if (!salaryMinEl || !salaryMaxEl) return;
        const minVal = parseInt(salaryMinEl.value || '0', 10);
        Array.from(salaryMaxEl.options).forEach(opt => {
            if (!opt.value) return;
            opt.disabled = parseInt(opt.value, 10) <= minVal;
        });
        if (salaryMaxEl.value && parseInt(salaryMaxEl.value, 10) <= minVal) {
            salaryMaxEl.value = '';
        }
    };

    const syncSalaryHidden = () => {
        if (!salaryHiddenEl || !salaryMinEl || !salaryMaxEl || !salaryNegotiableEl) return;

        if (salaryNegotiableEl.checked) {
            salaryHiddenEl.value = 'ตามตกลง';
            salaryMinEl.disabled = true;
            salaryMaxEl.disabled = true;
            return;
        }

        salaryMinEl.disabled = false;
        salaryMaxEl.disabled = false;
        const minVal = salaryMinEl.value;
        const maxVal = salaryMaxEl.value;

        if (minVal && maxVal) {
            salaryHiddenEl.value = `${formatSalary(minVal)} - ${formatSalary(maxVal)} บาท`;
        } else if (minVal) {
            salaryHiddenEl.value = `${formatSalary(minVal)} บาทขึ้นไป`;
        } else if (maxVal) {
            salaryHiddenEl.value = `ไม่เกิน ${formatSalary(maxVal)} บาท`;
        } else {
            salaryHiddenEl.value = '';
        }
    };

    const initSalaryFromHidden = () => {
        if (!salaryHiddenEl || !salaryMinEl || !salaryMaxEl || !salaryNegotiableEl) return;
        const raw = (salaryHiddenEl.value || '').trim();
        if (!raw) return true;

        if (raw.includes('ตามตกลง')) {
            salaryNegotiableEl.checked = true;
            syncSalaryHidden();
            return true;
        }

        const nums = (raw.match(/\d[\d,]*/g) || [])
            .map(v => parseInt(v.replace(/,/g, ''), 10))
            .filter(v => salaryValues.includes(v));

        if (nums.length >= 2) {
            salaryMinEl.value = String(nums[0]);
            salaryMaxEl.value = String(nums[1]);
            return true;
        } else if (nums.length === 1) {
            if (raw.includes('ไม่เกิน')) {
                salaryMaxEl.value = String(nums[0]);
            } else {
                salaryMinEl.value = String(nums[0]);
            }
            return true;
        }

        return false;
    };

    const salaryParsed = initSalaryFromHidden();
    updateSalaryMaxOptions();
    if (salaryParsed) syncSalaryHidden();
    if (salaryMinEl) salaryMinEl.addEventListener('change', () => { updateSalaryMaxOptions(); syncSalaryHidden(); });
    if (salaryMaxEl) salaryMaxEl.addEventListener('change', syncSalaryHidden);
    if (salaryNegotiableEl) salaryNegotiableEl.addEventListener('change', syncSalaryHidden);

    // ===== Expire date toggle =====
    const expireNoLimitEl = document.getElementById('expire_no_limit');
    const expireDateEl = document.getElementById('rc_expire_at');

    const syncExpireDate = () => {
        if (!expireNoLimitEl || !expireDateEl) return;
        if (expireNoLimitEl.checked) {
            expireDateEl.value = '';
            expireDateEl.disabled = true;
            expireDateEl.classList.add('opacity-40');
        } else {
            expireDateEl.disabled = false;
            expireDateEl.classList.remove('opacity-40');
        }
    };

    if (expireNoLimitEl) expireNoLimitEl.addEventListener('change', syncExpireDate);
    syncExpireDate();

    const syncOverlayLabel = (select, labelEl) => {
        const selected = select.options[select.selectedIndex];
        if (selected && selected.value) {
            labelEl.textContent = selected.textContent;
            labelEl.classList.remove('opacity-60');
        } else {
            labelEl.textContent = select.options[0].textContent;
            labelEl.classList.add('opacity-60');
        }
    };

    // ===== Skills =====
    let skillIndex = document.querySelectorAll('.skill-row').length;
    let langIndex = document.querySelectorAll('.language-row').length;
    const workModeEls = document.getElementById('rc_work_mode');
    const locationFieldsEl = document.getElementById('location-fields');
    const locationTextEl = document.getElementById('rc_location_text');
    const locationLinkEl = document.getElementById('rc_location_link');

    const skillGroupOptions = @json($skillGroups->map(fn ($group) => ['id' => $group->id, 'name' => $group->name])->values());

    const buildSkillRowHtml = index => `
        <div class="relative min-w-0 overflow-hidden">
            <select name="skills[${index}][skill_group_id]" class="select select-bordered select-sm focus:select-primary skill-group w-full opacity-0 absolute inset-0 z-10 cursor-pointer">
                <option value="">-- กลุ่มทักษะ --</option>
                ${skillGroupOptions.map(group => `<option value="${group.id}">${group.name}</option>`).join('')}
            </select>
            <div class="select select-bordered select-sm w-full flex items-center pointer-events-none overflow-hidden">
                <span class="truncate text-sm skill-group-label opacity-60">-- กลุ่มทักษะ --</span>
            </div>
        </div>
        <div class="relative min-w-0 overflow-hidden">
            <select name="skills[${index}][skill_id]" class="select select-bordered select-sm focus:select-primary skill-select w-full opacity-0 absolute inset-0 z-10 cursor-pointer" disabled>
                <option value="">-- ทักษะ --</option>
            </select>
            <div class="select select-bordered select-sm w-full flex items-center pointer-events-none overflow-hidden">
                <span class="truncate text-sm skill-select-label opacity-60">-- ทักษะ --</span>
            </div>
        </div>
        <select name="skills[${index}][proficiency_level]" class="select select-bordered select-sm focus:select-primary">
            <option value="beginner">เริ่มต้น</option>
            <option value="intermediate">ปานกลาง</option>
            <option value="advanced">ขั้นสูง</option>
            <option value="expert">ผู้เชี่ยวชาญ</option>
        </select>
        <button type="button" class="remove-skill btn btn-xs shrink-0" style="background-color:#ef4444; color:white; border:none; min-width:2rem">✕</button>
    `;

    const toggleLocationFields = () => {
        const selectedWorkMode = workModeEls?.value || '';
        const requiresLocation = ['onsite', 'hybrid'].includes(selectedWorkMode);
        [locationTextEl, locationLinkEl].forEach(field => {
            if (!field) return;
            field.disabled = !requiresLocation;
        });

        if (locationTextEl) {
            if (requiresLocation) {
                locationTextEl.setAttribute('required', 'required');
            } else {
                locationTextEl.removeAttribute('required');
                locationTextEl.value = '';
            }
        }

        if (locationLinkEl) {
            if (requiresLocation) {
                locationLinkEl.setAttribute('required', 'required');
            } else {
                locationLinkEl.removeAttribute('required');
                locationLinkEl.value = '';
            }
        }

        if (locationFieldsEl) {
            locationFieldsEl.classList.toggle('hidden', !requiresLocation);
        }
    };

    document.addEventListener('change', e => {
        if (e.target.classList.contains('skill-group')) {
            const row = e.target.closest('.skill-row');
            syncOverlayLabel(e.target, row.querySelector('.skill-group-label'));
            const skillSelect = row.querySelector('.skill-select');
            const groupId = e.target.value;
            skillSelect.innerHTML = '<option value="">-- ทักษะ --</option>';
            skillSelect.disabled = true;
            syncOverlayLabel(skillSelect, row.querySelector('.skill-select-label'));
            if (!groupId) return;
            const skills = SKILLS_BY_GROUP[parseInt(groupId)] || SKILLS_BY_GROUP[groupId];
            if (!skills || skills.length === 0) return;
            [...skills]
                .sort((a, b) => (a.name || '').localeCompare((b.name || ''), undefined, { sensitivity: 'base' }))
                .forEach(skill => {
                    skillSelect.insertAdjacentHTML('beforeend',
                        `<option value="${skill.id}">${skill.name}</option>`);
                });
            skillSelect.disabled = false;
        }
        if (e.target.classList.contains('skill-select')) {
            const row = e.target.closest('.skill-row');
            syncOverlayLabel(e.target, row.querySelector('.skill-select-label'));
        }
        if (e.target.classList.contains('lang-select')) {
            const row = e.target.closest('.language-row');
            syncOverlayLabel(e.target, row.querySelector('.lang-label'));
        }
    });

    document.getElementById('add-skill').addEventListener('click', () => {
        const wrapper = document.getElementById('skills-wrapper');
        const row = document.createElement('div');
        row.className = 'skill-row grid items-center gap-2 p-3 bg-base-100 rounded-xl border border-base-300';
        row.style.gridTemplateColumns = '2fr 2fr 1.5fr 2rem';
        row.innerHTML = buildSkillRowHtml(skillIndex);
        wrapper.appendChild(row);
        skillIndex++;
    });

    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-skill') || e.target.closest('.remove-skill')) {
            e.target.closest('.skill-row').remove();
        }
    });

    // ===== Languages =====
    const buildLangOptions = () =>
        `<option value="">-- เลือกภาษา --</option>` +
        LANGUAGE_OPTIONS.map(o => `<option value="${o}">${o}</option>`).join('');

    document.getElementById('add-language').addEventListener('click', () => {
        const wrapper = document.getElementById('languages-wrapper');
        const div = document.createElement('div');
        div.className = 'language-row grid items-center gap-2 p-3 bg-base-100 rounded-xl border border-base-300';
        div.style.gridTemplateColumns = '2fr 2fr 2rem';
        div.innerHTML = `
            <div class="relative min-w-0 overflow-hidden">
                <select name="languages[${langIndex}][language]"
                    class="select select-bordered select-sm focus:select-primary lang-select w-full opacity-0 absolute inset-0 z-10 cursor-pointer">
                    ${buildLangOptions()}
                </select>
                <div class="select select-bordered select-sm w-full flex items-center pointer-events-none overflow-hidden">
                    <span class="truncate text-sm lang-label opacity-60">-- เลือกภาษา --</span>
                </div>
            </div>
            <select name="languages[${langIndex}][proficiency]" class="select select-bordered select-sm w-full">
                <option value="basic">พื้นฐาน</option>
                <option value="conversational">สนทนาได้</option>
                <option value="fluent">คล่องแคล่ว</option>
                <option value="native">เจ้าของภาษา</option>
            </select>
            <button type="button" class="remove-language btn btn-xs shrink-0"
                style="background-color:#ef4444; color:white; border:none; min-width:2rem">✕</button>
        `;
        wrapper.appendChild(div);
        langIndex++;
    });

    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-language') || e.target.closest('.remove-language')) {
            e.target.closest('.language-row').remove();
        }
    });

    document.addEventListener('change', e => {
        const skillRow = e.target.closest('.skill-row');
        if (skillRow) {
            skillRow.style.outline = '';
            const err = skillRow.querySelector('.skill-row-err');
            if (err) err.remove();
        }
        const langRow = e.target.closest('.language-row');
        if (langRow) {
            langRow.style.outline = '';
            const err = langRow.querySelector('.lang-row-err');
            if (err) err.remove();
        }
    });

    // ===== Validation helpers =====
    const showErr = (row, msg, cls) => {
        row.style.outline = '2px solid #ef4444';
        let p = row.querySelector('.' + cls);
        if (!p) {
            p = document.createElement('p');
            p.className = cls + ' text-xs mt-1';
            p.style.cssText = 'color:#ef4444; grid-column:1/-1;';
            row.appendChild(p);
        }
        p.textContent = msg;
    };

    const clearErr = (row, cls) => {
        row.style.outline = '';
        const p = row.querySelector('.' + cls);
        if (p) p.remove();
    };

    const validateForm = () => {
        let valid = true;

        document.querySelectorAll('.skill-row').forEach(r => clearErr(r, 'skill-row-err'));
        document.querySelectorAll('.language-row').forEach(r => clearErr(r, 'lang-row-err'));

        const form = document.getElementById('recruitment-form');

        form.querySelectorAll('[required]').forEach(field => {
            if (field.disabled) {
                return;
            }
            field.style.outline = '';
            if (!field.value.trim()) {
                field.style.outline = '2px solid #ef4444';
                valid = false;
                let p = field.parentElement.querySelector('.native-err');
                if (!p) {
                    p = document.createElement('p');
                    p.className = 'native-err text-xs mt-1';
                    p.style.color = '#ef4444';
                    field.insertAdjacentElement('afterend', p);
                }
                p.textContent = 'กรุณากรอกข้อมูล';
            } else {
                const p = field.parentElement.querySelector('.native-err');
                if (p) p.remove();
            }
        });

        document.querySelectorAll('.skill-row').forEach(row => {
            const groupVal = row.querySelector('.skill-group')?.value || '';
            const skillVal = row.querySelector('.skill-select')?.value || '';
            if (!groupVal) {
                showErr(row, 'กรุณาเลือกกลุ่มทักษะ', 'skill-row-err');
                valid = false;
            } else if (!skillVal) {
                showErr(row, 'กรุณาเลือกทักษะ', 'skill-row-err');
                valid = false;
            }
        });

        document.querySelectorAll('.language-row').forEach(row => {
            const langVal = row.querySelector('.lang-select')?.value || '';
            if (!langVal) {
                showErr(row, 'กรุณาเลือกภาษา', 'lang-row-err');
                valid = false;
            }
        });

        return valid;
    };

    // ===== ปุ่มบันทึก → validate → SweetAlert2 ยืนยัน → submit =====
    document.getElementById('btn-submit').addEventListener('click', function () {
        const form = document.getElementById('recruitment-form');

        if (!validateForm()) {
            const firstErr = document.querySelector('[style*="outline"]');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        Swal.fire({
            title: 'ยืนยันการบันทึก',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i> บันทึก',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
        }).then(result => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    workModeEls?.addEventListener('change', toggleLocationFields);
    toggleLocationFields();

}); // end DOMContentLoaded
</script>
@endpush
