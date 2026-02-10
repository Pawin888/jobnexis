@extends('layouts.app')

@section('title', 'เพิ่มประกาศงาน')

@section('content')
<div class="max-w-4xl p-4 mx-auto shadow bg-base-200 rounded-2xl">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">
            เพิ่มประกาศงาน
            <span class="opacity-70">
                สำหรับ {{ $company->co_name ?? ($provider->email ?? '-') }}
            </span>
        </h1>
    </div>

    {{-- ================= FORM ================= --}}
    <form method="POST"
        action="{{ $isAdmin
            ? route('admin.providers.recruitments.store', $ownerId)
            : route('provider.recruitments.store') }}">
        @csrf

        {{-- ================= BASIC INFO ================= --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

            {{-- ชื่องาน --}}
            <div class="md:col-span-2">
                <label class="text-sm opacity-70">ชื่องาน</label>
                <input type="text" name="rc_title"
                    class="w-full input input-bordered"
                    value="{{ old('rc_title') }}" required>
                @error('rc_title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- รายละเอียด --}}
            <div class="md:col-span-2">
                <label class="text-sm opacity-70">รายละเอียด</label>
                <textarea name="rc_description" rows="5"
                    class="w-full textarea textarea-bordered"
                    required>{{ old('rc_description') }}</textarea>
                @error('rc_description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- คุณสมบัติ --}}
            <div class="md:col-span-2">
                <label class="text-sm opacity-70">คุณสมบัติ / ข้อกำหนด</label>
                <textarea name="rc_requirements" rows="4"
                    class="w-full textarea textarea-bordered">{{ old('rc_requirements') }}</textarea>
            </div>

            {{-- เงินเดือน --}}
            <div>
                <label class="text-sm opacity-70">เงินเดือน</label>
                <input type="text" name="rc_salary"
                    class="w-full input input-bordered"
                    value="{{ old('rc_salary') }}">
            </div>

            {{-- โหมดการทำงาน --}}
            <div>
                <label class="text-sm opacity-70">โหมดการทำงาน</label>
                <select name="rc_work_mode" class="w-full select select-bordered" required>
                    @foreach (['onsite','remote','hybrid'] as $mode)
                        <option value="{{ $mode }}"
                            @selected(old('rc_work_mode','onsite') === $mode)>
                            {{ ucfirst($mode) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ประเภท --}}
            <div>
                <label class="text-sm opacity-70">ประเภทงาน</label>
                <select name="rc_type" class="w-full select select-bordered" required>
                    @foreach (['full-time','part-time','intern','freelance'] as $type)
                        <option value="{{ $type }}"
                            @selected(old('rc_type','full-time') === $type)>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- สถานที่ --}}
            <div>
                <label class="text-sm opacity-70">สถานที่ (ข้อความ)</label>
                <input type="text" name="rc_location_text"
                    class="w-full input input-bordered"
                    value="{{ old('rc_location_text') }}">
            </div>

            {{-- ลิงก์สถานที่ --}}
            <div>
                <label class="text-sm opacity-70">ลิงก์สถานที่</label>
                <input type="url" name="rc_location_link"
                    class="w-full input input-bordered"
                    value="{{ old('rc_location_link') }}">
            </div>

            {{-- ลิงก์สมัคร --}}
            <div>
                <label class="text-sm opacity-70">ลิงก์สมัครงาน</label>
                <input type="url" name="rc_application_url"
                    class="w-full input input-bordered"
                    value="{{ old('rc_application_url') }}">
            </div>

            {{-- วันที่ --}}
            <div>
                <label class="text-sm opacity-70">โพสต์เมื่อ</label>
                <input type="datetime-local" name="rc_posted_at"
                    class="w-full input input-bordered"
                    value="{{ old('rc_posted_at') }}">
            </div>

            <div>
                <label class="text-sm opacity-70">หมดอายุ</label>
                <input type="date" name="rc_expire_at"
                    class="w-full input input-bordered"
                    value="{{ old('rc_expire_at') }}">
            </div>

            {{-- สถานะ --}}
            <div class="md:col-span-2">
                <label class="text-sm opacity-70">สถานะ</label>
                <div class="flex items-center gap-3 p-3 bg-white border rounded-lg">
                    <span class="text-sm">เผยแพร่ทันที</span>
                    <input type="checkbox" id="toggle-status"
                        class="toggle toggle-primary"
                        {{ old('rc_status','draft') === 'open' ? 'checked' : '' }}>
                    <input type="hidden" name="rc_status" id="rc_status_input"
                        value="{{ old('rc_status','draft') }}">
                </div>
            </div>
        </div>

        {{-- ================= SKILLS ================= --}}
        <div class="mt-8">
            <h2 class="mb-3 text-lg font-semibold">ทักษะที่ต้องการ</h2>

            <div id="skills-wrapper" class="space-y-3">

                {{-- skill row --}}
                <div class="grid grid-cols-1 gap-3 md:grid-cols-4 skill-row">

                    {{-- skill group --}}
                    <select name="skills[0][skill_group_id]"
                        class="select select-bordered skill-group">
                        <option value="">-- เลือกกลุ่มสกิล --</option>
                        @foreach ($skillGroups as $group)
                            <option value="{{ $group->id }}">
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- skill --}}
                    <select name="skills[0][skill_id]"
                        class="select select-bordered skill-select" disabled>
                        <option value="">-- เลือกสกิล --</option>
                    </select>

                    {{-- level --}}
                    <select name="skills[0][proficiency_level]"
                        class="select select-bordered">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                        <option value="expert">Expert</option>
                    </select>

                    {{-- remove --}}
                    <button type="button"
                        class="btn btn-outline btn-error remove-skill hidden">
                        ลบ
                    </button>
                </div>
            </div>

            <button type="button" id="add-skill"
                class="mt-3 btn btn-sm btn-outline">
                + เพิ่มสกิล
            </button>
        </div>

        {{-- ================= ACTIONS ================= --}}
        <div class="flex gap-2 mt-6">
            <button class="px-4 py-2 text-white bg-blue-600 rounded-lg">
                บันทึก
            </button>

            <a href="{{ $isAdmin
                ? route('admin.providers.recruitments.index', $ownerId)
                : route('provider.recruitments.index') }}"
                class="btn">
                ยกเลิก
            </a>
        </div>
    </form>
</div>

{{-- ================= STATUS TOGGLE SCRIPT ================= --}}
<script>
(function () {
    const toggle = document.getElementById('toggle-status');
    const input  = document.getElementById('rc_status_input');
    const sync = () => input.value = toggle.checked ? 'open' : 'draft';
    toggle.addEventListener('change', sync);
    sync();
})();

window.SKILLS_BY_GROUP = @json(
    $skillGroups->mapWithKeys(fn ($g) => [
        $g->id => $g->skills->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
        ])
        ->values()
    ])
);

console.log('SKILLS_BY_GROUP:', window.SKILLS_BY_GROUP);

document.addEventListener('DOMContentLoaded', () => {
    let index = 1; // ⭐ ประกาศตัวแปร index ที่นี่

    // เปลี่ยนกลุ่ม → โหลดสกิล
    document.addEventListener('change', e => {
        if (!e.target.classList.contains('skill-group')) return;

        const row = e.target.closest('.skill-row');
        const skillSelect = row.querySelector('.skill-select');
        const groupId = e.target.value;

        skillSelect.innerHTML = '<option value="">-- เลือกสกิล --</option>';
        skillSelect.disabled = true;

        if (!groupId) return;
        
        // แปลง groupId เป็นตัวเลขเพื่อจับคู่กับ key ใน object
        const skills = SKILLS_BY_GROUP[parseInt(groupId)] || SKILLS_BY_GROUP[groupId];
        
        if (!skills || skills.length === 0) {
            console.log('No skills found for group:', groupId);
            return;
        }

        skills.forEach(skill => {
            skillSelect.insertAdjacentHTML(
                'beforeend',
                `<option value="${skill.id}">${skill.name}</option>`
            );
        });

        skillSelect.disabled = false;
    });

    // เพิ่ม row
    document.getElementById('add-skill').addEventListener('click', () => {
        const wrapper = document.getElementById('skills-wrapper');
        const template = wrapper.querySelector('.skill-row').cloneNode(true);

        template.querySelectorAll('select').forEach(select => {
            select.name = select.name.replace(/\d+/, index);
            select.value = '';
            select.disabled = select.classList.contains('skill-select');
        });

        template.querySelector('.remove-skill').classList.remove('hidden');

        wrapper.appendChild(template);
        index++;
    });

    // ลบ row
    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-skill')) {
            e.target.closest('.skill-row').remove();
        }
    });
});
</script>
@endsection
