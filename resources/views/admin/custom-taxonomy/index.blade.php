@extends('layouts.app')

@section('title', 'Custom Job Taxonomy')

@section('content')
@php
    $taxonomyLabels = [
        'creating' => 'ระดับการสร้างสรรค์ (Creating) [6]',
        'evaluating' => 'ระดับการประเมินค่า (Evaluating) [5]',
        'analyzing' => 'ระดับการวิเคราะห์ (Analyzing) [4]',
        'applying' => 'ระดับการนำไปใช้ (Applying) [3]',
        'understanding' => 'ระดับการเข้าใจ (Understanding) [2]',
        'remembering' => 'ระดับการจดจำ (Remembering) [1]',
    ];
    $existingGroupNames = $groups->pluck('name')->values();
    $existingRoleNames = $roles->pluck('name')->values();
    $existingSkillNames = $skills->pluck('name')->values();
@endphp

<style>
    .taxonomy-surface {
        --ui-bg: #f0f9ff; /* sky-100 */
        --ui-card: #ffffffee;
        --ui-border: #cbd5e1; /* slate-300 */
        --ui-border-strong: #a5b4fc; /* indigo-200 */
        --ui-text: #0f172a;
        --ui-muted: #64748b;
        --ui-primary: #2563eb; /* blue-600 */
        --ui-primary-hover: #1d4ed8; /* blue-700 */
        --ui-accent: #06b6d4; /* cyan-500 */
        --ui-danger: #ef4444; /* red-500 */
        --ui-success: #22c55e; /* green-500 */
        --ui-warning: #facc15; /* yellow-400 */
    }

    .taxonomy-surface {
        color: var(--ui-text);
    }

    .taxonomy-surface .ui-hero {
        background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
        color: white;
        border: none;
        box-shadow: 0 4px 24px rgba(37,99,235,0.10);
    }

    .taxonomy-surface .ui-stat {
        border: 1.5px solid var(--ui-border);
        background: var(--ui-bg);
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(37,99,235,0.04);
    }

    .taxonomy-surface .ui-card {
        backdrop-filter: blur(16px);
        background: var(--ui-card);
        border: 1.5px solid var(--ui-border-strong);
        box-shadow: 0 8px 32px rgba(37,99,235,0.07);
        transition: box-shadow .25s, transform .25s;
        border-radius: 1.25rem;
    }

    .ui-card:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 16px 48px rgba(99,102,241,0.12);
    }

    /* Input modern */
    .input, .select, .textarea {
        border-radius: 0.9rem !important;
        border: 1.5px solid #e2e8f0 !important;
        background: #fff !important;
        transition: border-color .2s, box-shadow .2s;
        font-size: 1rem;
        min-height: 2.25rem;
    }

    .taxonomy-surface .input,
    .taxonomy-surface .select,
    .taxonomy-surface .textarea {
        border-width: 2px !important;
        border-color: var(--ui-primary) !important;
        background-color: #ffffff !important;
        color: var(--ui-text) !important;
    }

    .taxonomy-surface .input:focus,
    .taxonomy-surface .select:focus,
    .taxonomy-surface .textarea:focus {
        outline: none;
        border-color: var(--ui-primary-hover) !important;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.13);
    }

    .taxonomy-surface .btn {
        border-width: 1.5px !important;
        border-color: var(--ui-primary) !important;
        border-radius: 1rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        font-size: 1rem;
        padding: 0.5rem 1.2rem;
        transition: background .18s, border-color .18s, color .18s, box-shadow .18s;
        box-shadow: 0 2px 8px rgba(37,99,235,0.04);
    }

    .taxonomy-surface .btn-neutral,
    .taxonomy-surface .btn-primary {
        background: linear-gradient(90deg, var(--ui-primary), var(--ui-accent));
        color: #ffffff !important;
        border-color: var(--ui-primary) !important;
    }

    .taxonomy-surface .btn-neutral:hover,
    .taxonomy-surface .btn-primary:hover {
        background: linear-gradient(90deg, var(--ui-primary-hover), var(--ui-accent));
        border-color: var(--ui-primary-hover) !important;
        box-shadow: 0 4px 16px rgba(37,99,235,0.13);
    }

    .taxonomy-surface .btn-outline {
        background: #ffffff !important;
        color: var(--ui-primary) !important;
        border-color: var(--ui-primary) !important;
    }
    .taxonomy-surface .btn-outline:hover {
        background: var(--ui-primary) !important;
        color: #fff !important;
    }

    .taxonomy-surface .badge {
        border-color: var(--ui-border-strong) !important;
        color: var(--ui-primary) !important;
        background: #e0e7ff !important; /* indigo-100 */
        font-weight: 600;
        font-size: 0.95em;
        border-radius: 0.7em;
        padding: 0.3em 0.9em;
        letter-spacing: 0.01em;
        box-shadow: 0 1px 4px rgba(37,99,235,0.04);
    }
    .taxonomy-surface .badge-warning {
        color: var(--ui-warning) !important;
        background: #fef9c3 !important;
        border-color: #fde047 !important;
    }
    .taxonomy-surface .badge-info {
        color: var(--ui-accent) !important;
        background: #cffafe !important;
        border-color: #22d3ee !important;
    }

    .taxonomy-surface .section-subtle {
        border: 1.5px solid var(--ui-border);
        background: #f0f9ff;
        border-radius: 1rem;
        box-shadow: 0 1px 6px rgba(37,99,235,0.03);
    }

    .taxonomy-surface .hint {
        color: var(--ui-muted);
    }

    /* Table modern */
    .taxonomy-surface table {
        border-radius: 1rem;
        overflow: hidden;
        background: #fff;
    }
    .taxonomy-surface th, .taxonomy-surface td {
        padding: 0.55em 0.7em;
        font-size: 0.98em;
    }
    .taxonomy-surface thead {
        background: #e0e7ff;
        color: var(--ui-primary);
        font-weight: 700;
    }
    .taxonomy-surface tr {
        transition: background .15s;
    }
    .taxonomy-surface tbody tr:hover {
        background: #f0f9ff;
    }

    /* Alert modern */
    .taxonomy-surface .alert {
        border-radius: 0.9rem;
        padding: 0.8em 1.2em;
        font-size: 1em;
        margin-bottom: 0.7em;
        box-shadow: 0 2px 8px rgba(99,102,241,0.04);
    }
    .taxonomy-surface .alert-success {
        background: #dcfce7;
        color: var(--ui-success);
        border: 1.5px solid #bbf7d0;
    }
    .taxonomy-surface .alert-error {
        background: #fee2e2;
        color: var(--ui-danger);
        border: 1.5px solid #fecaca;
    }
    .taxonomy-surface .alert-info {
        background: #e0e7ff;
        color: var(--ui-primary);
        border: 1.5px solid #a5b4fc;
    }
</style>

<div class="container mx-auto px-4 py-6 space-y-8 taxonomy-surface">
    <div class="ui-hero rounded-3xl p-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h1 class="text-3xl font-black text-White tracking-tight">Custom Taxonomy Workspace</h1>
                <p class="text-sm text-White mt-1">กำหนดโครงสร้างแบบ กลุ่มงาน > ตำแหน่งงาน > ทักษะ และเลือกระดับ Taxonomy ให้แต่ละทักษะ</p>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-slate-700">
                <div class="ui-stat rounded-xl px-3 py-2">
                    <p class="text-xs">กลุ่มงาน</p>
                    <p class="text-xl font-bold">{{ $groupsAll->count() }}</p>
                </div>
                <div class="ui-stat rounded-xl px-3 py-2">
                    <p class="text-xs">ตำแหน่งงาน</p>
                    <p class="text-xl font-bold">{{ $rolesAll->count() }}</p>
                </div>
                <div class="ui-stat rounded-xl px-3 py-2">
                    <p class="text-xs">ทักษะ</p>
                    <p class="text-xl font-bold">{{ $skillsAll->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error shadow-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error shadow-sm">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card ui-card">
            <div class="card-body">
                <h2 class="card-title text-lg flex items-center gap-2">
                    <span class="badge badge-neutral badge-outline">A</span>
                    เพิ่มกลุ่มงาน
                </h2>
                <form method="POST" action="{{ route('admin.custom-taxonomy.groups.store') }}" class="space-y-3">
                    @csrf
                    <label class="label pb-0"><span class="label-text font-semibold">แท็กกลุ่มงาน (เพิ่มหลายรายการได้)</span></label>
                    <div class="section-subtle rounded-xl p-3 space-y-2" data-batch-tags data-existing='@json($existingGroupNames)'>
                        <div class="flex flex-wrap gap-2 min-h-9" data-tags-list></div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input type="text" class="input input-bordered input-sm w-full" data-tags-input placeholder="พิมพ์ชื่อกลุ่มงานแล้วกด Enter">
                            <button type="button" class="btn btn-sm btn-neutral" data-tags-add>เพิ่ม</button>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="btn btn-xs btn-outline btn-neutral" data-tags-clear>ล้างทั้งหมด</button>
                            <button type="button" class="btn btn-xs btn-outline" data-tags-sample="Engineering & Technology&#10;Business, Management & Consulting">วางตัวอย่าง</button>
                        </div>
                        <p class="text-xs hint">คั่นได้ทั้ง Enter และ comma | สีฟ้า = ใหม่, สีเหลือง = มีอยู่แล้ว</p>
                        <textarea class="sr-only" name="names" data-tags-target required>{{ old('names') }}</textarea>
                    </div>
                    <button class="btn btn-neutral w-full" type="submit">บันทึกกลุ่มงาน</button>
                </form>
            </div>
        </div>

        <div class="card ui-card">
            <div class="card-body">
                <h2 class="card-title text-lg flex items-center gap-2">
                    <span class="badge badge-neutral badge-outline">B</span>
                    เพิ่มตำแหน่งงาน
                </h2>
                <form method="POST" action="{{ route('admin.custom-taxonomy.roles.store') }}" class="space-y-3">
                    @csrf
                    <label class="label pb-0"><span class="label-text font-semibold">เลือกกลุ่มงานปลายทาง</span></label>
                    <select id="roleGroupId" name="custom_job_group_id" class="select select-bordered w-full" required>
                        <option value="">เลือกกลุ่มงาน</option>
                        @foreach($groupsAll as $group)
                            <option value="{{ $group->id }}" {{ old('custom_job_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                    <label class="label pb-0"><span class="label-text font-semibold">แท็กตำแหน่งงาน (เพิ่มหลายรายการได้)</span></label>
                    <div class="section-subtle rounded-xl p-3 space-y-2" data-batch-tags data-existing='@json($existingRoleNames)'>
                        <div class="flex flex-wrap gap-2 min-h-9" data-tags-list></div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input type="text" class="input input-bordered input-sm w-full" data-tags-input placeholder="พิมพ์ชื่อตำแหน่งงานแล้วกด Enter">
                            <button type="button" class="btn btn-sm btn-neutral" data-tags-add>เพิ่ม</button>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="btn btn-xs btn-outline btn-neutral" data-tags-clear>ล้างทั้งหมด</button>
                            <button type="button" class="btn btn-xs btn-outline" data-tags-sample="Full Stack Developer&#10;Backend Developer, Frontend Developer">วางตัวอย่าง</button>
                        </div>
                        <p class="text-xs hint">คั่นได้ทั้ง Enter และ comma | สีฟ้า = ใหม่, สีเหลือง = มีอยู่แล้ว</p>
                        <textarea class="sr-only" name="names" data-tags-target required>{{ old('names') }}</textarea>
                    </div>
                    <button class="btn btn-neutral w-full" type="submit">บันทึกตำแหน่งงาน</button>
                </form>
            </div>
        </div>

        <div class="card ui-card">
            <div class="card-body">
                <h2 class="card-title text-lg flex items-center gap-2">
                    <span class="badge badge-neutral badge-outline">C</span>
                    เพิ่มทักษะ
                </h2>
                <form method="POST" action="{{ route('admin.custom-taxonomy.skills.store') }}" class="space-y-3">
                    @csrf
                    <label class="label pb-0"><span class="label-text font-semibold">แท็กทักษะ (เพิ่มหลายรายการได้)</span></label>
                    <div class="section-subtle rounded-xl p-3 space-y-2" data-batch-tags data-existing='@json($existingSkillNames)'>
                        <div class="flex flex-wrap gap-2 min-h-9" data-tags-list></div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input type="text" class="input input-bordered input-sm w-full" data-tags-input placeholder="พิมพ์ชื่อทักษะแล้วกด Enter">
                            <button type="button" class="btn btn-sm btn-neutral" data-tags-add>เพิ่ม</button>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="btn btn-xs btn-outline btn-neutral" data-tags-clear>ล้างทั้งหมด</button>
                            <button type="button" class="btn btn-xs btn-outline" data-tags-sample="JavaScript&#10;Node.js, Communication">วางตัวอย่าง</button>
                        </div>
                        <p class="text-xs hint">คั่นได้ทั้ง Enter และ comma | สีฟ้า = ใหม่, สีเหลือง = มีอยู่แล้ว</p>
                        <textarea class="sr-only" name="names" data-tags-target required>{{ old('names') }}</textarea>
                    </div>
                    <button class="btn btn-neutral w-full" type="submit">บันทึกทักษะ</button>
                </form>
            </div>
        </div>
    </section>

    <section id="taxonomy-panel" class="card ui-card">
        <div class="card-body space-y-5">
            <div>
                <h2 class="card-title text-xl">กำหนดระดับทักษะตาม Taxonomy</h2>
                <p class="text-xs text-slate-600">ระบบจะผูกระดับความสามารถกับตำแหน่งงานที่เลือกโดยตรง เพื่อให้การประเมินสอดคล้องบริบทงานจริง</p>
                <details class="mt-2 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                    <summary class="cursor-pointer font-semibold text-slate-700">ดูคำอธิบายระดับ Taxonomy</summary>
                    <ul class="mt-2 space-y-1">
                        @foreach($taxonomyLabels as $taxonomyKey => $taxonomyText)
                            <li><span class="font-semibold">{{ $taxonomyText }}</span></li>
                        @endforeach
                    </ul>
                </details>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 items-stretch">
                <form method="GET" action="{{ route('admin.custom-taxonomy.index') }}" class="contents">
                    <div class="flex flex-col gap-2 h-full justify-end lg:col-span-1">
                        <label for="groupSelect" class="font-semibold text-sm text-slate-700 mb-1">ขั้นที่ 1: เลือกกลุ่มงาน</label>
                        <select name="group_id" id="groupSelect" class="select select-bordered w-full" required>
                            <option value="">เลือกกลุ่มงาน</option>
                            @foreach($groupsAll as $group)
                                <option value="{{ $group->id }}" {{ (int) $selectedGroupId === (int) $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-2 h-full justify-end lg:col-span-1">
                        <label for="roleSelect" class="font-semibold text-sm text-slate-700 mb-1">ขั้นที่ 2: เลือกตำแหน่งงาน</label>
                        <select name="role_id" id="roleSelect" class="select select-bordered w-full" required>
                            <option value="">เลือกตำแหน่งงาน</option>
                            @foreach($rolesAll as $role)
                                <option value="{{ $role->id }}" data-group-id="{{ $role->custom_job_group_id }}" {{ (int) $selectedRoleId === (int) $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-2 h-full justify-end lg:col-span-1">
                        <label class="font-semibold text-sm text-slate-700 mb-1">ขั้นที่ 3: โหลดทักษะ</label>
                        <button type="submit" class="btn btn-primary w-full">โหลดทักษะ</button>
                    </div>
                </form>
            </div>

            @if($selectedRole)
                <div class="mb-2">
                    <h3 class="font-semibold text-base text-slate-700 mb-2">เพิ่มทักษะและกำหนดระดับ Taxonomy ให้ตำแหน่งงานนี้</h3>
                    <form method="POST" action="{{ route('admin.custom-taxonomy.weights.store') }}" class="grid grid-cols-1 gap-3 mb-2 lg:grid-cols-3 items-end">
                        @csrf
                        <input type="hidden" name="custom_job_role_id" value="{{ $selectedRole->id }}">
                        <div class="flex flex-col gap-1">
                            <label for="custom_skill_id" class="text-sm font-medium text-slate-700">เลือกทักษะ</label>
                            <select name="custom_skill_id" id="custom_skill_id" class="select select-bordered w-full" required>
                                <option value="">เลือกทักษะที่ต้องการเพิ่มเข้าตำแหน่งงานนี้</option>
                                @foreach($skillsAll as $skill)
                                    <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="taxonomy_level" class="text-sm font-medium text-slate-700">ระดับ Taxonomy</label>
                            <select name="taxonomy_level" id="taxonomy_level" class="select select-bordered w-full" required>
                                @foreach($taxonomyLabels as $taxonomyKey => $taxonomyText)
                                    <option value="{{ $taxonomyKey }}" {{ $taxonomyKey === 'applying' ? 'selected' : '' }}>{{ $taxonomyText }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- น้ำหนักจะถูกกำหนดอัตโนมัติตาม taxonomy level -->
                        <input type="hidden" name="weight" id="weight" value="1">
                        <div class="flex flex-col gap-1">
                            <label class="invisible select-none">เพิ่ม</label>
                            <button type="submit" class="btn btn-primary w-full">เพิ่มความสามารถ</button>
                        </div>
                    </form>
                    <div class="section-subtle rounded-xl p-4 flex flex-col justify-center gap-2 lg:col-span-1">
                    @if($selectedRole)
                        <p class="font-semibold text-slate-800">ตำแหน่งงานที่กำลังแก้ไข: <span class="break-words">{{ $selectedRole->name }}</span></p>
                        <p class="text-xs text-slate-600">กลุ่มงาน: <span class="break-words">{{ $selectedRole->group?->name }}</span></p>
                    @else
                        <p class="font-semibold text-slate-800">ตำแหน่งงานที่กำลังแก้ไข: <span class="break-words text-slate-400">-</span></p>
                        <p class="text-xs text-slate-600">กลุ่มงาน: <span class="break-words text-slate-400">-</span></p>
                    @endif
                </div>
            </div>

                <div class="overflow-x-auto rounded-xl border border-base-200">
                    <table class="table table-zebra table-sm">
                        <thead>
                            <tr>
                                <th class="w-2/5">ทักษะ</th>
                                <th class="w-1/5">ระดับ Taxonomy</th>
                                <th class="w-1/5">น้ำหนัก</th>
                                <th class="w-1/5">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roleWeights as $weight)
                                <tr>
                                    <td class="font-medium">{{ $weight->skill?->name }}</td>
                                    <td>
                                        <form id="w-{{ $weight->id }}" method="POST" action="{{ route('admin.custom-taxonomy.weights.update', $weight) }}" class="flex gap-1 items-center">
                                            @csrf
                                            @method('PUT')
                                            <select name="taxonomy_level" class="select select-bordered select-sm w-full" required>
                                                @foreach($taxonomyLabels as $taxonomyKey => $taxonomyText)
                                                    <option value="{{ $taxonomyKey }}" {{ $weight->taxonomy_level === $taxonomyKey ? 'selected' : '' }}>{{ $taxonomyText }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td class="font-bold">{{ $weight->weight }}</td>
                                    <td>
                                        <div class="flex gap-1">
                                            <form id="w-{{ $weight->id }}" method="POST" action="{{ route('admin.custom-taxonomy.weights.update', $weight) }}" class="flex gap-1 items-center">
                                                @csrf
                                                @method('PUT')
                                                <button class="btn btn-xs btn-primary ml-2" type="submit">บันทึก</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.custom-taxonomy.weights.destroy', $weight) }}" onsubmit="return confirm('ลบรายการทักษะนี้?')" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-error" type="submit">ลบ</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-slate-500 py-6">ยังไม่มีทักษะที่ผูกกับตำแหน่งงานนี้</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <span>เลือกกลุ่มงานและตำแหน่งงานก่อน เพื่อกำหนดระดับทักษะตาม Taxonomy</span>
                </div>
            @endif
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6">
        <div class="card ui-card">
            <div class="card-body">
                <h3 class="card-title text-lg">จัดการกลุ่มงาน</h3>
                <label class="input input-bordered input-sm flex items-center gap-2">
                    <span class="text-slate-500">ค้นหา</span>
                    <input id="groupSearch" type="text" class="grow" placeholder="ค้นหาชื่อกลุ่มงาน">
                </label>
                <div class="overflow-x-auto max-h-96 rounded-lg border border-base-200">
                    <table class="table table-xs">
                        <thead>
                            <tr>
                                <th>ชื่อ</th>
                                <th>จำนวนตำแหน่ง</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="groupsTableBody">
                            @foreach($groups as $group)
                                <tr data-search="{{ mb_strtolower($group->name) }}">
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <input form="group-update-{{ $group->id }}" name="name" value="{{ $group->name }}" class="input input-bordered input-xs w-full min-w-44" required>
                                        </div>
                                        <div id="group-detail-{{ $group->id }}" class="hidden mt-2 bg-slate-50 border border-slate-200 rounded p-2 text-xs" style="min-width:220px;">
                                            <div class="font-semibold mb-1">ตำแหน่งงานในกลุ่มนี้:</div>
                                            <ul class="list-disc ml-4">
                                                @php $groupRoles = $rolesAll->where('custom_job_group_id', $group->id); @endphp
                                                @forelse($groupRoles as $role)
                                                    <li>{{ $role->name }}</li>
                                                @empty
                                                    <li class="text-slate-400">ไม่มีตำแหน่งงาน</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </td>
                                    <td>{{ $group->roles_count }}</td>
                                    <td>
                                        <div class="flex gap-1">
                                            <form id="group-update-{{ $group->id }}" method="POST" action="{{ route('admin.custom-taxonomy.groups.update', $group) }}">
                                                @csrf
                                                @method('PUT')
                                                <button class="btn btn-xs btn-primary" type="submit">บันทึก</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.custom-taxonomy.groups.destroy', $group) }}" onsubmit="return confirm('ลบกลุ่มงานนี้?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-error" type="submit">ลบ</button>
                                            </form>
                                            <button type="button" class="btn btn-xs btn-outline" onclick="toggleDetail('group-detail-{{ $group->id }}')">ดูรายละเอียด</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                @if($groups->total() > 0)
                        @include('admin.master-skills._esco-pagination', [
                            'paginator' => $groups,
                            'perPageName' => 'groups_page',
                        ])
                    @endif
            </div>
        </div>

        <div class="card ui-card">
            <div class="card-body">
                <h3 class="card-title text-lg">จัดการตำแหน่งงาน</h3>
                <label class="input input-bordered input-sm flex items-center gap-2">
                    <span class="text-slate-500">ค้นหา</span>
                    <input id="roleSearch" type="text" class="grow" placeholder="ค้นหาตำแหน่งงานหรือกลุ่มงาน">
                </label>
                <div class="overflow-x-auto max-h-96 rounded-lg border border-base-200">
                    <table class="table table-xs">
                        <thead>
                            <tr>
                                <th>ตำแหน่งงาน</th>
                                <th>กลุ่มงาน</th>
                                <th>ทักษะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="rolesTableBody">
                            @foreach($roles->items() as $role)
                                <tr data-search="{{ mb_strtolower($role->name.' '.$role->group?->name) }}">
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <input form="role-update-{{ $role->id }}" name="name" value="{{ $role->name }}" class="input input-bordered input-xs w-full min-w-44" required>
                                        </div>
                                        <div id="role-detail-{{ $role->id }}" class="hidden mt-2 bg-slate-50 border border-slate-200 rounded p-2 text-xs" style="min-width:220px;">
                                            <div class="font-semibold mb-1">ทักษะที่ผูกกับตำแหน่งนี้:</div>
                                            <ul class="list-disc ml-4">
                                                @php $roleSkills = $role->skillWeights ?? collect(); @endphp
                                                @forelse($roleSkills as $weight)
                                                    <li>{{ $weight->skill?->name }} <span class="text-slate-400">({{ $taxonomyLabels[$weight->taxonomy_level] ?? $weight->taxonomy_level }})</span></li>
                                                @empty
                                                    <li class="text-slate-400">ไม่มีทักษะ</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <select form="role-update-{{ $role->id }}" name="custom_job_group_id" class="select select-bordered select-xs w-full min-w-40" required>
                                            @foreach($groupsAll as $group)
                                                <option value="{{ $group->id }}" {{ (int) $role->custom_job_group_id === (int) $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>{{ $role->skill_weights_count }}</td>
                                    <td>
                                        <div class="flex gap-1">
                                            <form id="role-update-{{ $role->id }}" method="POST" action="{{ route('admin.custom-taxonomy.roles.update', $role) }}">
                                                @csrf
                                                @method('PUT')
                                                <button class="btn btn-xs btn-primary" type="submit">บันทึก</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.custom-taxonomy.roles.destroy', $role) }}" onsubmit="return confirm('ลบตำแหน่งงานนี้?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-error" type="submit">ลบ</button>
                                            </form>
                                            <button type="button" class="btn btn-xs btn-outline rounded-lg flex items-center gap-1" style="border-radius:0.7rem;" onclick="toggleDetail('role-detail-{{ $role->id }}')">
                                                <span>รายละเอียด</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                @if($roles->total() > 0)
                        @include('admin.master-skills._esco-pagination', [
                            'paginator' => $roles,
                            'perPageName' => 'roles_page',
                        ])
                    @endif
            </div>
        </div>

        <div class="card ui-card">
            <div class="card-body">
                <h3 class="card-title text-lg">จัดการทักษะ</h3>
                <label class="input input-bordered input-sm flex items-center gap-2">
                    <span class="text-slate-500">ค้นหา</span>
                    <input id="skillSearch" type="text" class="grow" placeholder="ค้นหาทักษะ">
                </label>
                <div class="overflow-x-auto max-h-96 rounded-lg border border-base-200">
                    <table class="table table-xs">
                        <thead>
                            <tr>
                                <th>ทักษะ</th>
                                <th>ถูกใช้</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="skillsTableBody">
                            @foreach($skills->items() as $skill)
                                <tr data-search="{{ mb_strtolower($skill->name) }}">
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <input form="skill-update-{{ $skill->id }}" name="name" value="{{ $skill->name }}" class="input input-bordered input-xs w-full min-w-44" required>
                                        </div>
                                        <div id="skill-detail-{{ $skill->id }}" class="hidden mt-2 bg-slate-50 border border-slate-200 rounded p-2 text-xs" style="min-width:220px;">
                                            <div class="font-semibold mb-1">ถูกใช้ในตำแหน่งงาน:</div>
                                            <ul class="list-disc ml-4">
                                                @php $skillRoles = $rolesAll->filter(function($role) use ($skill) {
                                                    return $role->skillWeights && $role->skillWeights->where('custom_skill_id', $skill->id)->count() > 0;
                                                }); @endphp
                                                @forelse($skillRoles as $role)
                                                    <li>{{ $role->name }}</li>
                                                @empty
                                                    <li class="text-slate-400">ยังไม่มีตำแหน่งงานใช้ทักษะนี้</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </td>
                                    <td>{{ $skill->role_weights_count }}</td>
                                    <td>
                                        <div class="flex gap-1">
                                            <form id="skill-update-{{ $skill->id }}" method="POST" action="{{ route('admin.custom-taxonomy.skills.update', $skill) }}">
                                                @csrf
                                                @method('PUT')
                                                <button class="btn btn-xs btn-primary" type="submit">บันทึก</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.custom-taxonomy.skills.destroy', $skill) }}" onsubmit="return confirm('ลบทักษะนี้?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-error" type="submit">ลบ</button>
                                            </form>
                                            <button type="button" class="btn btn-xs btn-outline rounded-lg flex items-center gap-1" style="border-radius:0.7rem;" onclick="toggleDetail('skill-detail-{{ $skill->id }}')">
                                                <span>รายละเอียด</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($skills->total() > 0)
                        @include('admin.master-skills._esco-pagination', [
                            'paginator' => $skills,
                            'perPageName' => 'skills_page',
                        ])
                    @endif
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const taxonomyLabels = @json($taxonomyLabels);
    const groupSelect = document.getElementById('groupSelect');
    const roleSelect = document.getElementById('roleSelect');
    const skillPreview = document.getElementById('skillByRolePreview');
    const groupSearch = document.getElementById('groupSearch');
    const roleSearch = document.getElementById('roleSearch');
    const skillSearch = document.getElementById('skillSearch');
    const roleGroupPicker = document.getElementById('roleGroupPicker');
    const roleGroupId = document.getElementById('roleGroupId');

    function normalizeTagParts(input) {
        return (input || '')
            .replace(/\r\n|\r/g, '\n')
            .split(/[\n,]/)
            .map((part) => part.trim())
            .filter((part) => part.length > 0);
    }

    function setupBatchTags(root) {
        const inputEl = root.querySelector('[data-tags-input]');
        const addBtn = root.querySelector('[data-tags-add]');
        const clearBtn = root.querySelector('[data-tags-clear]');
        const sampleBtn = root.querySelector('[data-tags-sample]');
        const listEl = root.querySelector('[data-tags-list]');
        const targetEl = root.querySelector('[data-tags-target]');
        const formEl = root.closest('form');
        const existingNames = JSON.parse(root.dataset.existing || '[]');
        const existingSet = new Set(existingNames.map((name) => String(name).trim().toLowerCase()));

        if (!inputEl || !addBtn || !listEl || !targetEl || !formEl) {
            return;
        }

        const tags = new Map();

        function renderTags() {
            listEl.innerHTML = '';

            if (tags.size === 0) {
                const empty = document.createElement('span');
                empty.className = 'text-xs text-slate-400';
                empty.textContent = 'ยังไม่มีแท็ก';
                listEl.appendChild(empty);
            }

            Array.from(tags.values()).forEach((tag) => {
                const chip = document.createElement('span');
                const exists = existingSet.has(tag.toLowerCase());
                chip.className = exists
                    ? 'badge badge-warning badge-outline gap-1 px-2 py-3'
                    : 'badge badge-info badge-outline gap-1 px-2 py-3';
                chip.textContent = tag;

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'font-bold';
                removeBtn.textContent = 'x';
                removeBtn.setAttribute('aria-label', 'ลบแท็ก ' + tag);
                removeBtn.addEventListener('click', function () {
                    tags.delete(tag.toLowerCase());
                    renderTags();
                });

                chip.appendChild(removeBtn);
                listEl.appendChild(chip);
            });

            targetEl.value = Array.from(tags.values()).join('\n');
        }

        function addRaw(rawText) {
            normalizeTagParts(rawText).forEach((part) => {
                const key = part.toLowerCase();
                if (!tags.has(key)) {
                    tags.set(key, part);
                }
            });
            renderTags();
        }

        function addFromInput() {
            addRaw(inputEl.value);
            inputEl.value = '';
            inputEl.focus();
        }

        addBtn.addEventListener('click', addFromInput);

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                tags.clear();
                renderTags();
                inputEl.focus();
            });
        }

        inputEl.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                addFromInput();
            }
        });

        if (sampleBtn) {
            sampleBtn.addEventListener('click', function () {
                addRaw(sampleBtn.dataset.tagsSample || '');
            });
        }

        formEl.addEventListener('submit', function (event) {
            addFromInput();
            if (!targetEl.value.trim()) {
                event.preventDefault();
                inputEl.focus();
                return;
            }
        });

        if ((targetEl.value || '').trim()) {
            addRaw(targetEl.value);
        }
    }

    function filterRolesByGroup() {
        const groupId = groupSelect.value;
        const currentRole = roleSelect.value;

        Array.from(roleSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = groupId && option.dataset.groupId !== groupId;
        });

        const selectedOption = roleSelect.querySelector('option[value="' + currentRole + '"]');
        if (!selectedOption || selectedOption.hidden) {
            roleSelect.value = '';
        }
    }

    async function loadSkillsByRole() {
        const roleId = roleSelect.value;
        skillPreview.innerHTML = '<option value="">กำลังโหลด...</option>';

        if (!roleId) {
            skillPreview.innerHTML = '<option value="">ยังไม่ได้เลือกตำแหน่งงาน</option>';
            return;
        }

        try {
            const response = await fetch('{{ url('admin/custom-taxonomy/api/roles') }}/' + roleId + '/skills');
            const data = await response.json();

            if (!Array.isArray(data) || data.length === 0) {
                skillPreview.innerHTML = '<option value="">ตำแหน่งงานนี้ยังไม่มีทักษะผูกไว้</option>';
                return;
            }

            skillPreview.innerHTML = data.map((item) => {
                const taxonomyText = taxonomyLabels[item.taxonomy_level] || item.taxonomy_level;
                return '<option>' + item.name + ' (' + taxonomyText + ')</option>';
            }).join('');
        } catch (error) {
            skillPreview.innerHTML = '<option value="">ไม่สามารถโหลดข้อมูลทักษะได้</option>';
        }
    }

    function bindTableSearch(inputEl, tbodyId) {
        if (!inputEl) {
            return;
        }

        const tbody = document.getElementById(tbodyId);
        if (!tbody) {
            return;
        }

        const rows = Array.from(tbody.querySelectorAll('tr'));
        inputEl.addEventListener('input', function () {
            const q = (inputEl.value || '').trim().toLowerCase();
            rows.forEach((row) => {
                const haystack = (row.dataset.search || '').toLowerCase();
                row.style.display = !q || haystack.includes(q) ? '' : 'none';
            });
        });
    }

    function bindRoleGroupPicker(inputEl, hiddenEl) {
        if (!inputEl || !hiddenEl) {
            return;
        }

        const options = Array.from(document.querySelectorAll('#roleGroupOptions option'));
        const nameToId = new Map(
            options.map((option) => [
                (option.value || '').trim().toLowerCase(),
                option.getAttribute('data-group-id') || '',
            ])
        );

        function syncGroupId() {
            const key = (inputEl.value || '').trim().toLowerCase();
            hiddenEl.value = nameToId.get(key) || '';
        }

        inputEl.addEventListener('input', syncGroupId);
        inputEl.addEventListener('change', syncGroupId);

        const formEl = inputEl.closest('form');
        if (formEl) {
            formEl.addEventListener('submit', function (event) {
                syncGroupId();
                if (!hiddenEl.value) {
                    event.preventDefault();
                    inputEl.focus();
                }
            });
        }

        if (hiddenEl.value && !inputEl.value) {
            const matched = options.find((option) => option.getAttribute('data-group-id') === hiddenEl.value);
            if (matched) {
                inputEl.value = matched.value || '';
            }
        }
    }

    groupSelect.addEventListener('change', function () {
        filterRolesByGroup();
        loadSkillsByRole();
    });

    roleSelect.addEventListener('change', loadSkillsByRole);

    bindTableSearch(groupSearch, 'groupsTableBody');
    bindTableSearch(roleSearch, 'rolesTableBody');
    bindTableSearch(skillSearch, 'skillsTableBody');
    bindRoleGroupPicker(roleGroupPicker, roleGroupId);
    document.querySelectorAll('[data-batch-tags]').forEach(setupBatchTags);

    filterRolesByGroup();
    loadSkillsByRole();
});
</script>
<script>
function toggleDetail(id) {
    var el = document.getElementById(id);
    if (el) {
        el.classList.toggle('hidden');
    }
}
</script>
@endsection
