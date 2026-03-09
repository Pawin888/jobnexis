@extends('layouts.app')

@section('title', 'แก้ไขประกาศงาน')

@section('content')
<div class="flex flex-col gap-4 p-4 shadow bg-base-200 rounded-2xl">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between bg-base-200 rounded-2xl px-6 py-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight">แก้ไขประกาศงาน</h1>
            <p class="text-sm opacity-60 mt-0.5">
                {{ $rec->rc_title }}
            </p>
        </div>
    </div>

    {{-- ================= FORM ================= --}}
    <form method="POST"
        action="{{ $isAdmin
            ? route('admin.recruitments.update', $rec->rc_id)
            : route('provider.recruitments.update', $rec->rc_id) }}">
        @csrf @method('PATCH')

        <div class="space-y-4">

            {{-- ================= SECTION: ข้อมูลพื้นฐาน ================= --}}
            <div class="bg-base-200 rounded-2xl px-6 py-5">
                <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-primary rounded-full inline-block"></span>
                    ข้อมูลพื้นฐาน
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div class="md:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">ชื่องาน <span class="text-error">*</span></span></label>
                        <input type="text" name="rc_title"
                            class="w-full input input-bordered focus:input-primary border border-base-300"
                            placeholder="เช่น Senior Frontend Developer"
                            value="{{ old('rc_title', $rec->rc_title) }}" required>
                        @error('rc_title') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">รายละเอียด <span class="text-error">*</span></span></label>
                        <textarea name="rc_description" rows="5"
                            class="w-full textarea textarea-bordered focus:textarea-primary border border-base-300"
                            placeholder="อธิบายเกี่ยวกับตำแหน่งงานและความรับผิดชอบ..."
                            required>{{ old('rc_description', $rec->rc_description) }}</textarea>
                        @error('rc_description') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">คุณสมบัติ / ข้อกำหนด</span></label>
                        <textarea name="rc_requirements" rows="4"
                            class="w-full textarea textarea-bordered focus:textarea-primary border border-base-300"
                            placeholder="ระบุคุณสมบัติที่ต้องการ เช่น ประสบการณ์ วุฒิการศึกษา...">{{ old('rc_requirements', $rec->rc_requirements) }}</textarea>
                        @error('rc_requirements') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
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

                    <div class="lg:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">เงินเดือน</span></label>
                        <input type="text" name="rc_salary"
                            class="w-full input input-bordered focus:input-primary border border-base-300"
                            placeholder="เช่น 30,000 - 50,000 บาท"
                            value="{{ old('rc_salary', $rec->rc_salary) }}">
                        @error('rc_salary') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label py-1"><span class="label-text font-medium">โหมดการทำงาน</span></label>
                        <select name="rc_work_mode" class="w-full select select-bordered focus:select-primary border border-base-300" required>
                            @foreach (['onsite' => 'Onsite', 'remote' => 'Remote', 'hybrid' => 'Hybrid'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('rc_work_mode', $rec->rc_work_mode) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        @error('rc_work_mode') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label py-1"><span class="label-text font-medium">ประเภทงาน</span></label>
                        <select name="rc_type" class="w-full select select-bordered focus:select-primary border border-base-300" required>
                            @foreach (['full-time' => 'Full-time', 'part-time' => 'Part-time', 'intern' => 'Intern', 'freelance' => 'Freelance'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('rc_type', $rec->rc_type) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        @error('rc_type') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">สถานที่ (ข้อความ)</span></label>
                        <input type="text" name="rc_location_text"
                            class="w-full input input-bordered focus:input-primary border border-base-300"
                            placeholder="เช่น กรุงเทพฯ, อโศก"
                            value="{{ old('rc_location_text', $rec->rc_location_text) }}">
                        @error('rc_location_text') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label class="label py-1"><span class="label-text font-medium">ลิงก์สถานที่</span></label>
                        <input type="url" name="rc_location_link"
                            class="w-full input input-bordered focus:input-primary border border-base-300"
                            placeholder="https://maps.google.com/..."
                            value="{{ old('rc_location_link', $rec->rc_location_link) }}">
                        @error('rc_location_link') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SECTION: การสมัครและช่วงเวลา ================= --}}
            <div class="bg-base-200 rounded-2xl px-6 py-5">
                <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-accent rounded-full inline-block"></span>
                    การสมัครและช่วงเวลา
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">

                    <div class="md:col-span-2 lg:col-span-1">
                        <label class="label py-1"><span class="label-text font-medium">ลิงก์สมัครงาน</span></label>
                        <input type="url" name="rc_application_url"
                            class="w-full input input-bordered focus:input-primary border border-base-300"
                            placeholder="https://..."
                            value="{{ old('rc_application_url', $rec->rc_application_url) }}">
                        @error('rc_application_url') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label py-1"><span class="label-text font-medium">โพสต์เมื่อ</span></label>
                        @php $postedVal = optional($rec->rc_posted_at)->format('Y-m-d\TH:i'); @endphp
                        <input type="datetime-local" name="rc_posted_at"
                            class="w-full input input-bordered focus:input-primary border border-base-300"
                            value="{{ old('rc_posted_at', $postedVal) }}">
                        @error('rc_posted_at') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label py-1"><span class="label-text font-medium">หมดอายุ</span></label>
                        <input type="date" name="rc_expire_at"
                            class="w-full input input-bordered focus:input-primary border border-base-300"
                            value="{{ old('rc_expire_at', optional($rec->rc_expire_at)->format('Y-m-d')) }}">
                        @error('rc_expire_at') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
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
                                {{ old('rc_status', $rec->rc_status) === 'open' ? 'checked' : '' }}>
                            <input type="hidden" name="rc_status" id="rc_status_input"
                                value="{{ old('rc_status', $rec->rc_status) }}">
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
                    @forelse ($rec->skills as $i => $existingSkill)
                        <div class="skill-row grid items-center gap-2 p-3 bg-base-100 rounded-xl border border-base-300"
                             style="grid-template-columns: 2fr 2fr 1.5fr 2rem">

                            {{-- กลุ่มสกิล --}}
                            @php $selectedGroupId = old("skills.$i.skill_group_id", $existingSkill->master_skill_group_id); @endphp
                            <div class="relative min-w-0 overflow-hidden">
                                <select name="skills[{{ $i }}][skill_group_id]"
                                    class="select select-bordered select-sm focus:select-primary skill-group w-full opacity-0 absolute inset-0 z-10 cursor-pointer">
                                    <option value="">-- กลุ่มสกิล --</option>
                                    @foreach ($skillGroups as $group)
                                        <option value="{{ $group->id }}" @selected($selectedGroupId == $group->id)>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <div class="select select-bordered select-sm w-full flex items-center pointer-events-none overflow-hidden">
                                    @php $groupName = $skillGroups->firstWhere('id', $selectedGroupId)?->name ?? '-- กลุ่มสกิล --'; @endphp
                                    <span class="truncate text-sm skill-group-label {{ $selectedGroupId ? '' : 'opacity-60' }}">{{ $groupName }}</span>
                                </div>
                            </div>

                            {{-- สกิล --}}
                            @php $selectedSkillId = old("skills.$i.skill_id", $existingSkill->master_skill_id); @endphp
                            <div class="relative min-w-0 overflow-hidden">
                                <select name="skills[{{ $i }}][skill_id]"
                                    class="select select-bordered select-sm focus:select-primary skill-select w-full opacity-0 absolute inset-0 z-10 cursor-pointer">
                                    <option value="">-- สกิล --</option>
                                    @if ($existingSkill->skillGroup)
                                        @foreach ($existingSkill->skillGroup->skills->sortBy(fn ($s) => mb_strtolower($s->name)) as $skill)
                                            <option value="{{ $skill->id }}" @selected($selectedSkillId == $skill->id)>{{ $skill->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="select select-bordered select-sm w-full flex items-center pointer-events-none overflow-hidden">
                                    @php $skillName = optional($existingSkill->skillGroup?->skills->firstWhere('id', $selectedSkillId))->name ?? '-- สกิล --'; @endphp
                                    <span class="truncate text-sm skill-select-label {{ $selectedSkillId ? '' : 'opacity-60' }}">{{ $skillName }}</span>
                                </div>
                            </div>

                            {{-- ระดับ --}}
                            <select name="skills[{{ $i }}][proficiency_level]"
                                class="select select-bordered select-sm focus:select-primary">
                                @foreach (['beginner' => 'เริ่มต้น', 'intermediate' => 'ปานกลาง', 'advanced' => 'ขั้นสูง', 'expert' => 'ผู้เชี่ยวชาญ'] as $level => $label)
                                    <option value="{{ $level }}" @selected(old("skills.$i.proficiency_level", $existingSkill->proficiency_level) === $level)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- ปุ่มลบ --}}
                            <button type="button"
                                class="remove-skill btn btn-xs shrink-0 {{ $loop->first ? 'hidden' : '' }}"
                                style="background-color:#ef4444; color:white; border:none; min-width:2rem">
                                ✕
                            </button>
                        </div>
                    @empty
                        {{-- กรณีไม่มี skill เดิม --}}
                        <div class="skill-row grid items-center gap-2 p-3 bg-base-100 rounded-xl border border-base-300"
                             style="grid-template-columns: 2fr 2fr 1.5fr 2rem">

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

                            <select name="skills[0][proficiency_level]"
                                class="select select-bordered select-sm focus:select-primary">
                                <option value="beginner">เริ่มต้น</option>
                                <option value="intermediate">ปานกลาง</option>
                                <option value="advanced">ขั้นสูง</option>
                                <option value="expert">ผู้เชี่ยวชาญ</option>
                            </select>

                            <button type="button" class="remove-skill hidden btn btn-xs shrink-0"
                                style="background-color:#ef4444; color:white; border:none; min-width:2rem">✕</button>
                        </div>
                    @endforelse
                </div>

                <button type="button" id="add-skill"
                    class="mt-3 btn btn-sm btn-outline btn-primary gap-1">
                    + เพิ่มสกิล
                </button>
            </div>

        </div>{{-- end space-y-4 --}}

        {{-- ================= SECTION: LANGUAGES ================= --}}
        <div class="bg-base-200 rounded-2xl px-6 py-5">
            <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
                <span class="w-1.5 h-5 bg-info rounded-full inline-block"></span>
                ภาษาที่ต้องการ
            </h2>
            <div id="languages-wrapper" class="space-y-3">
                @php
                    $langs = old('languages', isset($rec) ? $rec->languages->toArray() : [['language'=>'','proficiency'=>'basic']]);
                @endphp
                @foreach ($langs as $i => $lang)
                <div class="language-row grid items-center gap-2 p-3 bg-base-100 rounded-xl border border-base-300"
                    style="grid-template-columns: 2fr 2fr 2rem">
                    <input type="text" name="languages[{{ $i }}][language]"
                        class="input input-bordered input-sm w-full"
                        placeholder="เช่น English, ไทย"
                        value="{{ $lang['language'] ?? '' }}">
                    <select name="languages[{{ $i }}][proficiency]"
                        class="select select-bordered select-sm w-full">
                        <option value="basic" {{ ($lang['proficiency'] ?? '')=='basic' ? 'selected' : '' }}>พื้นฐาน</option>
                        <option value="conversational" {{ ($lang['proficiency'] ?? '')=='conversational' ? 'selected' : '' }}>สนทนาได้</option>
                        <option value="fluent" {{ ($lang['proficiency'] ?? '')=='fluent' ? 'selected' : '' }}>คล่องแคล่ว</option>
                        <option value="native" {{ ($lang['proficiency'] ?? '')=='native' ? 'selected' : '' }}>เจ้าของภาษา</option>
                    </select>
                    <button type="button"
                        class="remove-language {{ $i == 0 && count($langs) == 1 ? 'hidden' : '' }} btn btn-xs shrink-0"
                        style="background-color:#ef4444; color:white; border:none; min-width:2rem">
                        ✕
                    </button>
                </div>
                @endforeach
            </div>
            <button type="button" id="add-language"
                class="mt-3 btn btn-sm btn-outline btn-info gap-1">
                + เพิ่มภาษา
            </button>
        </div>

        {{-- ================= ACTIONS ================= --}}
        <div class="flex justify-end gap-3 mt-4 pb-6">
            @if ($isAdmin)
                <a href="{{ route('admin.providers.recruitments.index', $rec->rc_u_id) }}" class="btn btn-ghost">ยกเลิก</a>
            @else
                <a href="{{ route('provider.recruitments.index') }}" class="btn btn-ghost">ยกเลิก</a>
            @endif
            <button type="submit" class="btn btn-primary px-8 gap-2"
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

document.addEventListener('DOMContentLoaded', () => {
    let index = {{ $rec->skills->count() ?: 1 }};

    const syncLabel = (select, labelClass) => {
        const row = select.closest('.skill-row');
        const label = row.querySelector(`.${labelClass}`);
        if (!label) return;
        const selected = select.options[select.selectedIndex];
        if (selected && selected.value) {
            label.textContent = selected.textContent;
            label.classList.remove('opacity-60');
        } else {
            label.textContent = select.options[0].textContent;
            label.classList.add('opacity-60');
        }
    };

    document.addEventListener('change', e => {
        if (e.target.classList.contains('skill-group')) {
            syncLabel(e.target, 'skill-group-label');

            const row = e.target.closest('.skill-row');
            const skillSelect = row.querySelector('.skill-select');
            const groupId = e.target.value;

            skillSelect.innerHTML = '<option value="">-- สกิล --</option>';
            skillSelect.disabled = true;
            syncLabel(skillSelect, 'skill-select-label');

            if (!groupId) return;

            const skills = SKILLS_BY_GROUP[parseInt(groupId)] || SKILLS_BY_GROUP[groupId];
            if (!skills || skills.length === 0) return;

            [...skills]
                .sort((a, b) => (a.name || '').localeCompare((b.name || ''), undefined, { sensitivity: 'base' }))
                .forEach(skill => {
                skillSelect.insertAdjacentHTML('beforeend',
                    `<option value="${skill.id}">${skill.name}</option>`
                );
                });
            skillSelect.disabled = false;
        }

        if (e.target.classList.contains('skill-select')) {
            syncLabel(e.target, 'skill-select-label');
        }
    });

    document.getElementById('add-skill').addEventListener('click', () => {
        const wrapper = document.getElementById('skills-wrapper');
        const template = wrapper.querySelector('.skill-row').cloneNode(true);

        template.querySelectorAll('select').forEach(select => {
            select.name = select.name.replace(/\d+/, index);
            select.value = '';
            select.disabled = select.classList.contains('skill-select');
            if (select.classList.contains('skill-select')) {
                select.innerHTML = '<option value="">-- สกิล --</option>';
            }
        });

        const groupLabel = template.querySelector('.skill-group-label');
        const skillLabel = template.querySelector('.skill-select-label');
        if (groupLabel) { groupLabel.textContent = '-- กลุ่มสกิล --'; groupLabel.classList.add('opacity-60'); }
        if (skillLabel) { skillLabel.textContent = '-- สกิล --'; skillLabel.classList.add('opacity-60'); }

        template.querySelector('.remove-skill').classList.remove('hidden');
        wrapper.appendChild(template);
        index++;
    });

    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-skill') || e.target.closest('.remove-skill')) {
            e.target.closest('.skill-row').remove();
        }
    });

    // ===== Languages =====
    let langIndex = document.querySelectorAll('.language-row').length;
    document.getElementById('add-language').addEventListener('click', () => {
        const wrapper = document.getElementById('languages-wrapper');
        const template = wrapper.querySelector('.language-row').cloneNode(true);
        template.querySelectorAll('input,select').forEach(el => {
            el.name = el.name.replace(/\d+/, langIndex);
            if (el.tagName === 'INPUT') el.value = '';
            if (el.tagName === 'SELECT') el.value = 'basic';
        });
        template.querySelector('.remove-language').classList.remove('hidden');
        wrapper.appendChild(template);
        langIndex++;
    });
    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-language') || e.target.closest('.remove-language')) {
            const rows = document.querySelectorAll('.language-row');
            if (rows.length > 1) e.target.closest('.language-row').remove();
        }
    });
});
</script>
@endsection