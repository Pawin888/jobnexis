@extends('layouts.app')

@section('title', 'แก้ไขประกาศงาน')

@section('content')
<div class="max-w-4xl p-4 mx-auto shadow bg-base-200 rounded-2xl">
    <h1 class="mb-4 text-xl font-semibold">แก้ไขประกาศงาน</h1>

    <form method="POST"
          action="{{ $isAdmin
              ? route('admin.recruitments.update', $rec->rc_id)
              : route('provider.recruitments.update', $rec->rc_id) }}">
        @csrf @method('PATCH')

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="text-sm opacity-70">ชื่องาน</label>
                <input type="text" name="rc_title" class="w-full input input-bordered"
                       value="{{ old('rc_title', $rec->rc_title) }}" required>
                @error('rc_title') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="text-sm opacity-70">รายละเอียด</label>
                <textarea name="rc_description" rows="5" class="w-full textarea textarea-bordered" required>{{ old('rc_description', $rec->rc_description) }}</textarea>
                @error('rc_description') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="text-sm opacity-70">คุณสมบัติ/ข้อกำหนด</label>
                <textarea name="rc_requirements" rows="4" class="w-full textarea textarea-bordered">{{ old('rc_requirements', $rec->rc_requirements) }}</textarea>
                @error('rc_requirements') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">เงินเดือน (ข้อความอิสระ)</label>
                <input type="text" name="rc_salary" class="w-full input input-bordered"
                       placeholder="30,000 / ตามตกลง"
                       value="{{ old('rc_salary', $rec->rc_salary) }}">
                @error('rc_salary') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">โหมดการทำงาน</label>
                <select name="rc_work_mode" class="w-full select select-bordered" required>
                    @foreach (['onsite'=>'Onsite','remote'=>'Remote','hybrid'=>'Hybrid'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('rc_work_mode', $rec->rc_work_mode)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
                @error('rc_work_mode') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">ประเภท</label>
                <select name="rc_type" class="w-full select select-bordered" required>
                    @foreach (['full-time'=>'Full-time','part-time'=>'Part-time','intern'=>'Intern','freelance'=>'Freelance'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('rc_type', $rec->rc_type)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
                @error('rc_type') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">สถานะ</label>
                <select name="rc_status" class="w-full select select-bordered" required>
                    @foreach (['open'=>'เปิดรับ','closed'=>'ปิดรับ','draft'=>'ฉบับร่าง'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('rc_status', $rec->rc_status)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
                @error('rc_status') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">สถานที่ (ข้อความ)</label>
                <input type="text" name="rc_location_text" class="w-full input input-bordered"
                       value="{{ old('rc_location_text', $rec->rc_location_text) }}">
                @error('rc_location_text') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">ลิงก์แผนที่/สถานที่</label>
                <input type="url" name="rc_location_link" class="w-full input input-bordered"
                       value="{{ old('rc_location_link', $rec->rc_location_link) }}">
                @error('rc_location_link') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">ลิงก์สมัครงาน</label>
                <input type="url" name="rc_application_url" class="w-full input input-bordered"
                       value="{{ old('rc_application_url', $rec->rc_application_url) }}">
                @error('rc_application_url') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">โพสต์เมื่อ</label>
                @php
                    $postedVal = optional($rec->rc_posted_at)->format('Y-m-d\TH:i');
                @endphp
                <input type="datetime-local" name="rc_posted_at" class="w-full input input-bordered"
                       value="{{ old('rc_posted_at', $postedVal) }}">
                @error('rc_posted_at') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm opacity-70">หมดอายุ</label>
                <input type="date" name="rc_expire_at" class="w-full input input-bordered"
                       value="{{ old('rc_expire_at', optional($rec->rc_expire_at)->format('Y-m-d')) }}">
                @error('rc_expire_at') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- ================= SKILLS ================= --}}
        <div class="mt-8">
            <h2 class="mb-3 text-lg font-semibold">ทักษะที่ต้องการ</h2>

            <div id="skills-wrapper" class="space-y-3">
                @forelse ($rec->skills as $index => $existingSkill)
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-4 skill-row">
                        <select name="skills[{{ $index }}][skill_group_id]" class="select select-bordered skill-group">
                            <option value="">-- เลือกกลุ่มสกิล --</option>
                            @foreach ($skillGroups as $group)
                                <option value="{{ $group->id }}" 
                                    @selected(old("skills.$index.skill_group_id", $existingSkill->master_skill_group_id) == $group->id)>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="skills[{{ $index }}][skill_id]" class="select select-bordered skill-select">
                            <option value="">-- เลือกสกิล --</option>
                            @if($existingSkill->skillGroup)
                                @foreach ($existingSkill->skillGroup->skills as $skill)
                                    <option value="{{ $skill->id }}"
                                        @selected(old("skills.$index.skill_id", $existingSkill->master_skill_id) == $skill->id)>
                                        {{ $skill->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>

                        <select name="skills[{{ $index }}][proficiency_level]" class="select select-bordered">
                            @foreach(['beginner','intermediate','advanced','expert'] as $level)
                                <option value="{{ $level }}"
                                    @selected(old("skills.$index.proficiency_level", $existingSkill->proficiency_level) === $level)>
                                    {{ ucfirst($level) }}
                                </option>
                            @endforeach
                        </select>

                        <button type="button" class="btn btn-outline btn-error remove-skill {{ $loop->first ? 'hidden' : '' }}">
                            ลบ
                        </button>
                    </div>
                @empty
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-4 skill-row">
                        <select name="skills[0][skill_group_id]" class="select select-bordered skill-group">
                            <option value="">-- เลือกกลุ่มสกิล --</option>
                            @foreach ($skillGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>

                        <select name="skills[0][skill_id]" class="select select-bordered skill-select" disabled>
                            <option value="">-- เลือกสกิล --</option>
                        </select>

                        <select name="skills[0][proficiency_level]" class="select select-bordered">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>

                        <button type="button" class="hidden btn btn-outline btn-error remove-skill">ลบ</button>
                    </div>
                @endforelse
            </div>

            <button type="button" id="add-skill" class="mt-3 btn btn-sm btn-outline">
                + เพิ่มสกิล
            </button>
        </div>

        <div class="flex justify-start gap-2 mt-6">
            <button class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">บันทึก</button>

            @if ($isAdmin)
                <a href="{{ route('admin.providers.recruitments.index', $rec->rc_u_id) }}" class="btn">ยกเลิก</a>
            @else
                <a href="{{ route('provider.recruitments.index') }}" class="btn">ยกเลิก</a>
            @endif
        </div>
    </form>
</div>

{{-- ================= SCRIPTS ================= --}}
<script>
window.SKILLS_BY_GROUP = @json(
    $skillGroups->mapWithKeys(fn ($g) => [
        $g->id => $g->skills->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
        ])->values()
    ])
);

document.addEventListener('DOMContentLoaded', () => {
    let index = {{ $rec->skills->count() }};

    // เปลี่ยนกลุ่ม → โหลดสกิล
    document.addEventListener('change', e => {
        if (!e.target.classList.contains('skill-group')) return;

        const row = e.target.closest('.skill-row');
        const skillSelect = row.querySelector('.skill-select');
        const groupId = e.target.value;

        skillSelect.innerHTML = '<option value="">-- เลือกสกิล --</option>';
        skillSelect.disabled = true;

        if (!groupId) return;

        const skills = SKILLS_BY_GROUP[parseInt(groupId)] || SKILLS_BY_GROUP[groupId];

        if (!skills || skills.length === 0) return;

        skills.forEach(skill => {
            skillSelect.insertAdjacentHTML('beforeend',
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