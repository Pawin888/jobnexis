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

        <div class="space-y-4">

            {{-- ================= SECTION: ข้อมูลหลัก ================= --}}
            <div class="bg-base-200 rounded-2xl px-6 py-5">
                <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-primary rounded-full inline-block"></span>
                    ข้อมูลพื้นฐาน
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    {{-- ชื่องาน --}}
                    <div class="md:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">ชื่องาน <span class="text-error">*</span></span></label>
                        <input type="text" name="rc_title"
                            class="w-full input input-bordered focus:input-primary border border-base-300 pl-2"
                            placeholder="เช่น Senior Frontend Developer"
                            value="{{ old('rc_title') }}" required>
                        @error('rc_title') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- รายละเอียด --}}
                    <div class="md:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">รายละเอียด <span class="text-error">*</span></span></label>
                        <textarea name="rc_description" rows="5"
                            class="w-full textarea textarea-bordered focus:textarea-primary border border-base-300 pl-2"
                            placeholder="อธิบายเกี่ยวกับตำแหน่งงานและความรับผิดชอบ..."
                            required>{{ old('rc_description') }}</textarea>
                        @error('rc_description') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- คุณสมบัติ --}}
                    <div class="md:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">คุณสมบัติ / ข้อกำหนด</span></label>
                        <textarea name="rc_requirements" rows="4"
                            class="w-full textarea textarea-bordered focus:textarea-primary border border-base-300 pl-2"
                            placeholder="ระบุคุณสมบัติที่ต้องการ เช่น ประสบการณ์ วุฒิการศึกษา...">{{ old('rc_requirements') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ================= SECTION: รายละเอียดงาน ================= --}}
            <div class="bg-base-200 rounded-2xl px-6 py-5">
                <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-secondary rounded-full inline-block"></span>
                    รายละเอียดงาน
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

                    {{-- เงินเดือน --}}
                    <div class="lg:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">เงินเดือน</span></label>
                        <input type="text" name="rc_salary"
                            class="w-full input input-bordered focus:input-primary border border-base-300 pl-2"
                            placeholder="เช่น 30,000 - 50,000 บาท หรือ ตามที่ตกลง"
                            value="{{ old('rc_salary') }}">
                    </div>

                    {{-- โหมดการทำงาน --}}
                    <div>
                        <label class="label py-1"><span class="label-text font-medium">โหมดการทำงาน</span></label>
                        <select name="rc_work_mode" class="w-full select select-bordered focus:select-primary border border-base-300 pl-2" required>
                            @foreach (['onsite','remote','hybrid'] as $mode)
                                <option value="{{ $mode }}" @selected(old('rc_work_mode','onsite') === $mode)>
                                    {{ ucfirst($mode) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ประเภทงาน --}}
                    <div>
                        <label class="label py-1"><span class="label-text font-medium">ประเภทงาน</span></label>
                        <select name="rc_type" class="w-full select select-bordered focus:select-primary border border-base-300 pl-2" required>
                            @foreach (['full-time','part-time','intern','freelance'] as $type)
                                <option value="{{ $type }}" @selected(old('rc_type','full-time') === $type)>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- สถานที่ --}}
                    <div class="lg:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">สถานที่ (ข้อความ)</span></label>
                        <input type="text" name="rc_location_text"
                            class="w-full input input-bordered focus:input-primary border border-base-300 pl-2"
                            placeholder="เช่น กรุงเทพฯ, อโศก"
                            value="{{ old('rc_location_text') }}">
                    </div>

                    {{-- ลิงก์สถานที่ --}}
                    <div class="lg:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">ลิงก์สถานที่</span></label>
                        <input type="url" name="rc_location_link"
                            class="w-full input input-bordered focus:input-primary border border-base-300 pl-2"
                            placeholder="https://maps.google.com/..."
                            value="{{ old('rc_location_link') }}">
                    </div>
                </div>
            </div>

            {{-- ================= SECTION: การสมัครและเวลา ================= --}}
            <div class="bg-base-200 rounded-2xl px-6 py-5">
                <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-accent rounded-full inline-block"></span>
                    การสมัครและช่วงเวลา
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">

                    {{-- ลิงก์สมัคร --}}
                    <div class="lg:col-span-1 md:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">ลิงก์สมัครงาน</span></label>
                        <input type="url" name="rc_application_url"
                            class="w-full input input-bordered focus:input-primary border border-base-300 pl-2"
                            placeholder="https://..."
                            value="{{ old('rc_application_url') }}">
                    </div>

                    {{-- วันที่โพสต์ --}}
                    <div>
                        <label class="label py-1"><span class="label-text font-medium">วันที่เปิดรับสมัคร</span></label>
                        <input type="date" name="rc_posted_at"
                            class="w-full input input-bordered focus:input-primary border border-base-300 pl-2"
                            value="{{ old('rc_posted_at') }}">
                    </div>

                    {{-- วันหมดอายุ --}}
                    <div>
                        <label class="label py-1"><span class="label-text font-medium">วันหมดอายุ</span></label>
                        <input type="date" name="rc_expire_at"
                            class="w-full input input-bordered focus:input-primary border border-base-300 pl-2"
                            value="{{ old('rc_expire_at') }}">
                    </div>

                    {{-- สถานะ --}}
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="label py-1"><span class="label-text font-medium">สถานะการเผยแพร่</span></label>
                        <div class="flex items-center gap-4 p-4 bg-base-100 border border-base-300 rounded-xl w-full md:w-fit">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium" id="status-label">ฉบับร่าง</span>
                                <span class="text-xs opacity-50">แตะเพื่อเปลี่ยนสถานะ</span>
                            </div>
                            <input type="checkbox" id="toggle-status"
                                class="toggle toggle-primary toggle-lg border-2 border-base-300 checked:border-primary"
                                {{ old('rc_status','draft') === 'open' ? 'checked' : '' }}>
                            <input type="hidden" name="rc_status" id="rc_status_input"
                                value="{{ old('rc_status', 'draft') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= SECTION: SKILLS ================= --}}
            <div class="bg-base-200 rounded-2xl px-6 py-5">
                <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-warning rounded-full inline-block"></span>
                    ทักษะที่ต้องการ
                </h2>

                <div id="skills-wrapper" class="space-y-3">
                    <div class="skill-row grid items-center gap-2 p-3 bg-base-100 rounded-xl border border-base-300"
                         style="grid-template-columns: 2fr 2fr 1.5fr 2rem">

                        {{-- กลุ่มสกิล --}}
                        <div class="relative min-w-0 overflow-hidden">
                            <select name="skills[0][skill_group_id]"
                                class="select select-bordered select-sm focus:select-primary skill-group w-full opacity-0 absolute inset-0 z-10 cursor-pointer">
                                <option value="">-- กลุ่มสกิล --</option>
                                @foreach ($skillGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            <div class="select select-bordered select-sm w-full flex items-center pointer-events-none overflow-hidden">
                                <span class="truncate text-sm skill-group-label opacity-60">-- กลุ่มสกิล --</span>
                            </div>
                        </div>

                        {{-- สกิล --}}
                        <div class="relative min-w-0 overflow-hidden">
                            <select name="skills[0][skill_id]"
                                class="select select-bordered select-sm focus:select-primary skill-select w-full opacity-0 absolute inset-0 z-10 cursor-pointer"
                                disabled>
                                <option value="">-- สกิล --</option>
                            </select>
                            <div class="select select-bordered select-sm w-full flex items-center pointer-events-none overflow-hidden">
                                <span class="truncate text-sm skill-select-label opacity-60">-- สกิล --</span>
                            </div>
                        </div>

                        {{-- ระดับ --}}
                        <select name="skills[0][proficiency_level]"
                            class="select select-bordered select-sm focus:select-primary">
                            <option value="beginner">เริ่มต้น</option>
                            <option value="intermediate">ปานกลาง</option>
                            <option value="advanced">ขั้นสูง</option>
                            <option value="expert">ผู้เชี่ยวชาญ</option>
                        </select>

                        {{-- ปุ่มลบ --}}
                        <button type="button"
                            class="remove-skill hidden btn btn-xs shrink-0"
                            style="background-color:#ef4444; color:white; border:none; min-width:2rem">
                            ✕
                        </button>
                    </div>
                </div>

                <button type="button" id="add-skill"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    เพิ่มสกิล
                </button>
            </div>

        </div>{{-- end space-y-4 --}}

        {{-- ================= SECTION: LANGUAGES ================= --}}
        @php
            $langs = old('languages', isset($rec) ? $rec->languages->toArray() : [['language'=>'','proficiency'=>'basic']]);
            $languageOptions = [
                // ASEAN
                'ภาษาไทย (Thai)', 'မြန်မာဘာသာ (Burmese)', 'ភាសាខ្មែរ (Khmer)', 'ພາສາລາວ (Lao)',
                'Tiếng Việt (Vietnamese)', 'Bahasa Melayu (Malay)', 'Bahasa Indonesia (Indonesian)', 'Filipino (Tagalog)',
                // East Asia
                'English', '中文 普通话 (Mandarin)', '粵語 (Cantonese)',
                '日本語 (Japanese)', '한국어 (Korean)', 'Монгол (Mongolian)',
                // South Asia
                'हिन्दी (Hindi)', 'اردو (Urdu)', 'বাংলা (Bengali)', 'தமிழ் (Tamil)',
                'తెలుగు (Telugu)', 'සිංහල (Sinhala)', 'नेपाली (Nepali)',
                // Middle East
                'العربية (Arabic)', 'فارسی (Persian)', 'עברית (Hebrew)',
                'Türkçe (Turkish)', 'Қазақша (Kazakh)', 'Oʻzbek (Uzbek)',
                // Western Europe
                'Français (French)', 'Deutsch (German)', 'Español (Spanish)', 'Italiano (Italian)',
                'Português (Portuguese)', 'Nederlands (Dutch)', 'Svenska (Swedish)', 'Norsk (Norwegian)',
                'Dansk (Danish)', 'Suomi (Finnish)', 'Ελληνικά (Greek)',
                // Eastern Europe
                'Русский (Russian)', 'Українська (Ukrainian)', 'Polski (Polish)', 'Čeština (Czech)',
                'Magyar (Hungarian)', 'Română (Romanian)', 'Български (Bulgarian)',
                'Српски (Serbian)', 'Hrvatski (Croatian)',
                // Africa
                'Kiswahili (Swahili)', 'አማርኛ (Amharic)', 'Hausa', 'Yorùbá (Yoruba)', 'Afrikaans',
            ];
        @endphp

        <div class="bg-base-200 rounded-2xl px-6 py-5 mt-4">
            <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
                <span class="w-1.5 h-5 bg-info rounded-full inline-block"></span>
                ภาษาที่ต้องการ
            </h2>

            <div id="languages-wrapper" class="space-y-3">
                @foreach ($langs as $i => $lang)
                <div class="language-row grid items-center gap-2 p-3 bg-base-100 rounded-xl border border-base-300"
                    style="grid-template-columns: 2fr 2fr 2rem">

                    {{-- ภาษา (custom overlay เหมือน skill) --}}
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

                    {{-- ระดับภาษา --}}
                    <select name="languages[{{ $i }}][proficiency]"
                        class="select select-bordered select-sm w-full">
                        <option value="basic"          {{ ($lang['proficiency'] ?? '')=='basic'          ? 'selected' : '' }}>พื้นฐาน</option>
                        <option value="conversational" {{ ($lang['proficiency'] ?? '')=='conversational' ? 'selected' : '' }}>สนทนาได้</option>
                        <option value="fluent"         {{ ($lang['proficiency'] ?? '')=='fluent'         ? 'selected' : '' }}>คล่องแคล่ว</option>
                        <option value="native"         {{ ($lang['proficiency'] ?? '')=='native'         ? 'selected' : '' }}>เจ้าของภาษา</option>
                    </select>

                    {{-- ปุ่มลบ --}}
                    <button type="button"
                        class="remove-language {{ $i == 0 && count($langs) == 1 ? 'hidden' : '' }} btn btn-xs shrink-0"
                        style="background-color:#ef4444; color:white; border:none; min-width:2rem">
                        ✕
                    </button>
                </div>
                @endforeach
            </div>

            <button type="button" id="add-language"
                class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                เพิ่มภาษา
            </button>
        </div>

        {{-- ================= ACTIONS ================= --}}
        <div class="flex justify-end gap-3 mt-4 pb-6">
            <a href="{{ $isAdmin
                ? route('admin.providers.recruitments.index', $ownerId)
                : route('provider.recruitments.index') }}"
                class="btn btn-ghost">
                ยกเลิก
            </a>
            <button type="submit" id="btn-submit" class="btn btn-primary px-8 gap-2"
                style="background-color:#2563eb; color:white; border:none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                บันทึก
            </button>
        </div>
    </form>
</div>

{{-- ================= SCRIPTS ================= --}}
<script>
(function () {
    const toggle = document.getElementById('toggle-status');
    const input  = document.getElementById('rc_status_input');
    const label  = document.getElementById('status-label');

    const sync = () => {
        const isOpen = toggle.checked;
        input.value = isOpen ? 'open' : 'draft';
        label.textContent = isOpen ? 'เผยแพร่แล้ว' : 'ฉบับร่าง';
        label.className = isOpen
            ? 'text-sm font-medium text-success'
            : 'text-sm font-medium';
    };
    toggle.addEventListener('change', sync);
    sync();
})();

window.SKILLS_BY_GROUP = @json(
    $skillGroups->mapWithKeys(fn ($g) => [
        $g->id => $g->skills->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()
    ])
);

const LANGUAGE_OPTIONS = @json($languageOptions);

document.addEventListener('DOMContentLoaded', () => {

    // ===== helper: sync label ของ custom overlay select =====
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
    let skillIndex = 1;

    document.addEventListener('change', e => {

        // skill-group
        if (e.target.classList.contains('skill-group')) {
            const row = e.target.closest('.skill-row');
            syncOverlayLabel(e.target, row.querySelector('.skill-group-label'));

            const skillSelect = row.querySelector('.skill-select');
            const groupId = e.target.value;

            skillSelect.innerHTML = '<option value="">-- สกิล --</option>';
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

        // skill-select
        if (e.target.classList.contains('skill-select')) {
            const row = e.target.closest('.skill-row');
            syncOverlayLabel(e.target, row.querySelector('.skill-select-label'));
        }

        // lang-select
        if (e.target.classList.contains('lang-select')) {
            const row = e.target.closest('.language-row');
            syncOverlayLabel(e.target, row.querySelector('.lang-label'));
        }
    });

    document.getElementById('add-skill').addEventListener('click', () => {
        const wrapper = document.getElementById('skills-wrapper');
        const template = wrapper.querySelector('.skill-row').cloneNode(true);

        template.querySelectorAll('select').forEach(select => {
            select.name = select.name.replace(/\d+/, skillIndex);
            select.disabled = select.classList.contains('skill-select');
            if (select.classList.contains('skill-select')) {
                select.innerHTML = '<option value="">-- สกิล --</option>';
            }
            if (!select.classList.contains('skill-group') && !select.classList.contains('skill-select')) {
                select.value = 'beginner';
            } else {
                select.value = '';
            }
        });

        const groupLabel = template.querySelector('.skill-group-label');
        const skillLabel = template.querySelector('.skill-select-label');
        if (groupLabel) { groupLabel.textContent = '-- กลุ่มสกิล --'; groupLabel.classList.add('opacity-60'); }
        if (skillLabel) { skillLabel.textContent = '-- สกิล --'; skillLabel.classList.add('opacity-60'); }

        template.querySelector('.remove-skill').classList.remove('hidden');
        wrapper.appendChild(template);
        skillIndex++;
    });

    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-skill') || e.target.closest('.remove-skill')) {
            e.target.closest('.skill-row').remove();
        }
    });

    // ===== Languages =====
    let langIndex = document.querySelectorAll('.language-row').length;

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
            const rows = document.querySelectorAll('.language-row');
            if (rows.length > 1) e.target.closest('.language-row').remove();
        }
    });

    // เคลียร์ error เมื่อเปลี่ยนค่า
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

}); // end DOMContentLoaded

// ===== Validation (นอก DOMContentLoaded เพื่อให้แน่ใจว่า bind ได้) =====
(function attachFormValidation() {
    const form = document.getElementById('recruitment-form');
    if (!form) return;

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

    form.addEventListener('submit', function(e) {
        let valid = true;

        // เคลียร์ error เก่า
        document.querySelectorAll('.skill-row').forEach(r => clearErr(r, 'skill-row-err'));
        document.querySelectorAll('.language-row').forEach(r => clearErr(r, 'lang-row-err'));

        // validate native required fields ด้วยมือ (เพราะ novalidate)
        form.querySelectorAll('[required]').forEach(field => {
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

        // validate skills
        document.querySelectorAll('.skill-row').forEach(row => {
            const groupVal = row.querySelector('.skill-group')?.value || '';
            const skillVal = row.querySelector('.skill-select')?.value || '';
            if (!groupVal) {
                showErr(row, 'กรุณาเลือกกลุ่มสกิล', 'skill-row-err');
                valid = false;
            } else if (!skillVal) {
                showErr(row, 'กรุณาเลือกสกิล', 'skill-row-err');
                valid = false;
            }
        });

        // validate languages
        document.querySelectorAll('.language-row').forEach(row => {
            const langVal = row.querySelector('.lang-select')?.value || '';
            if (!langVal) {
                showErr(row, 'กรุณาเลือกภาษา', 'lang-row-err');
                valid = false;
            }
        });

        if (!valid) {
            e.preventDefault();
            const firstErr = document.querySelector('[style*="outline"]');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
})();
</script>
@endsection
