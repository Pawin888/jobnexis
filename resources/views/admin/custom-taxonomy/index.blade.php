@extends('layouts.app')

@section('title', 'Custom Job Taxonomy')

@section('content')
@php
    $taxonomyLabels = [
        'creating'     => 'ระดับการสร้างสรรค์ (Creating) [6]',
        'evaluating'   => 'ระดับการประเมินค่า (Evaluating) [5]',
        'analyzing'    => 'ระดับการวิเคราะห์ (Analyzing) [4]',
        'applying'     => 'ระดับการนำไปใช้ (Applying) [3]',
        'understanding'=> 'ระดับการเข้าใจ (Understanding) [2]',
        'remembering'  => 'ระดับการจดจำ (Remembering) [1]',
    ];
    $existingGroupNames = $groups->pluck('name')->values();
    $existingRoleNames  = $roles->pluck('name')->values();
    $existingSkillNames = $skills->pluck('name')->values();
@endphp

<style>
.detail-row { display: none; }
.detail-row.open { display: table-row; }
.detail-inner {
    padding: 10px 16px 14px;
    background: #f8fafc;
    border-top: 1px dashed #e2e8f0;
}
.detail-label {
    font-size: 11px;
    font-weight: 500;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 8px;
}
.detail-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 10px;
    border-radius: 99px;
    font-size: 12px;
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #bfdbfe;
    margin: 2px 3px 2px 0;
}
.detail-badge.taxonomy {
    background: #ede9fe;
    color: #5b21b6;
    border-color: #ddd6fe;
}
.detail-empty {
    font-size: 12px;
    color: #94a3b8;
    font-style: italic;
}
.detail-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    font-size: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: transparent;
    color: #6b7280;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}
.detail-btn:hover { background: #f3f4f6; color: #374151; }
.detail-btn .chevron { transition: transform 0.22s ease; display: block; flex-shrink: 0; }
.detail-btn.open .chevron { transform: rotate(180deg); }
</style>

<div class="mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Custom Taxonomy Workspace</h1>
                <p class="text-sm text-gray-500 mt-0.5">กำหนดโครงสร้างแบบ กลุ่มงาน &rsaquo; ตำแหน่งงาน &rsaquo; ทักษะ และเลือกระดับ Taxonomy ให้แต่ละทักษะ</p>
            </div>
            <div class="flex gap-3 text-center">
                <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                    <p class="text-xs text-gray-500">กลุ่มงาน</p>
                    <p class="text-lg font-bold text-gray-800">{{ $groupsAll->count() }}</p>
                </div>
                <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                    <p class="text-xs text-gray-500">ตำแหน่งงาน</p>
                    <p class="text-lg font-bold text-gray-800">{{ $rolesAll->count() }}</p>
                </div>
                <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                    <p class="text-xs text-gray-500">ทักษะ</p>
                    <p class="text-lg font-bold text-gray-800">{{ $skillsAll->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            <ul class="list-disc ml-4 space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Section A/B/C: Add forms --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- A: เพิ่มกลุ่มงาน --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">A</span>
                เพิ่มกลุ่มงาน
            </h2>
            <form method="POST" action="{{ route('admin.custom-taxonomy.groups.store') }}" class="space-y-3">
                @csrf
                <p class="text-sm font-medium text-gray-700">แท็กกลุ่มงาน <span class="text-gray-400 font-normal">(เพิ่มหลายรายการได้)</span></p>
                <div class="border border-gray-200 rounded-lg p-3 space-y-2 bg-gray-50" data-batch-tags data-existing='@json($existingGroupNames)'>
                    <div class="flex flex-wrap gap-1.5 min-h-8" data-tags-list></div>
                    <div class="flex gap-2">
                        <input type="text" class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" data-tags-input placeholder="พิมพ์แล้วกด Enter">
                        <button type="button" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium" data-tags-add>เพิ่ม</button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="px-2 py-1 text-xs border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 transition-colors" data-tags-clear>ล้างทั้งหมด</button>
                        <button type="button" class="px-2 py-1 text-xs border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 transition-colors" data-tags-sample="Engineering & Technology&#10;Business, Management & Consulting">วางตัวอย่าง</button>
                    </div>
                    <p class="text-xs text-gray-400">คั่นได้ทั้ง Enter และ comma | <span class="text-blue-500">สีฟ้า = ใหม่</span>, <span class="text-yellow-500">สีเหลือง = มีอยู่แล้ว</span></p>
                    <textarea class="sr-only" name="names" data-tags-target required>{{ old('names') }}</textarea>
                </div>
                <button class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors" type="submit">บันทึกกลุ่มงาน</button>
            </form>
        </div>

        {{-- B: เพิ่มตำแหน่งงาน --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">B</span>
                เพิ่มตำแหน่งงาน
            </h2>
            <form method="POST" action="{{ route('admin.custom-taxonomy.roles.store') }}" class="space-y-3">
                @csrf
                <p class="text-sm font-medium text-gray-700">เลือกกลุ่มงานปลายทาง</p>
                <select id="roleGroupId" name="custom_job_group_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" required>
                    <option value="">เลือกกลุ่มงาน</option>
                    @foreach($groupsAll as $group)
                        <option value="{{ $group->id }}" {{ old('custom_job_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
                <p class="text-sm font-medium text-gray-700">แท็กตำแหน่งงาน <span class="text-gray-400 font-normal">(เพิ่มหลายรายการได้)</span></p>
                <div class="border border-gray-200 rounded-lg p-3 space-y-2 bg-gray-50" data-batch-tags data-existing='@json($existingRoleNames)'>
                    <div class="flex flex-wrap gap-1.5 min-h-8" data-tags-list></div>
                    <div class="flex gap-2">
                        <input type="text" class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" data-tags-input placeholder="พิมพ์แล้วกด Enter">
                        <button type="button" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium" data-tags-add>เพิ่ม</button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="px-2 py-1 text-xs border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 transition-colors" data-tags-clear>ล้างทั้งหมด</button>
                        <button type="button" class="px-2 py-1 text-xs border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 transition-colors" data-tags-sample="Full Stack Developer&#10;Backend Developer, Frontend Developer">วางตัวอย่าง</button>
                    </div>
                    <p class="text-xs text-gray-400">คั่นได้ทั้ง Enter และ comma | <span class="text-blue-500">สีฟ้า = ใหม่</span>, <span class="text-yellow-500">สีเหลือง = มีอยู่แล้ว</span></p>
                    <textarea class="sr-only" name="names" data-tags-target required>{{ old('names') }}</textarea>
                </div>
                <button class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors" type="submit">บันทึกตำแหน่งงาน</button>
            </form>
        </div>

        {{-- C: เพิ่มทักษะ --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">C</span>
                เพิ่มทักษะ
            </h2>
            <form method="POST" action="{{ route('admin.custom-taxonomy.skills.store') }}" class="space-y-3">
                @csrf
                <p class="text-sm font-medium text-gray-700">แท็กทักษะ <span class="text-gray-400 font-normal">(เพิ่มหลายรายการได้)</span></p>
                <div class="border border-gray-200 rounded-lg p-3 space-y-2 bg-gray-50" data-batch-tags data-existing='@json($existingSkillNames)'>
                    <div class="flex flex-wrap gap-1.5 min-h-8" data-tags-list></div>
                    <div class="flex gap-2">
                        <input type="text" class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" data-tags-input placeholder="พิมพ์แล้วกด Enter">
                        <button type="button" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium" data-tags-add>เพิ่ม</button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="px-2 py-1 text-xs border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 transition-colors" data-tags-clear>ล้างทั้งหมด</button>
                        <button type="button" class="px-2 py-1 text-xs border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 transition-colors" data-tags-sample="JavaScript&#10;Node.js, Communication">วางตัวอย่าง</button>
                    </div>
                    <p class="text-xs text-gray-400">คั่นได้ทั้ง Enter และ comma | <span class="text-blue-500">สีฟ้า = ใหม่</span>, <span class="text-yellow-500">สีเหลือง = มีอยู่แล้ว</span></p>
                    <textarea class="sr-only" name="names" data-tags-target required>{{ old('names') }}</textarea>
                </div>
                <button class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors" type="submit">บันทึกทักษะ</button>
            </form>
        </div>
    </div>

    {{-- Taxonomy Panel --}}
    <div class="bg-white rounded-xl shadow p-6 space-y-5">
        <div>
            <h2 class="font-semibold text-gray-900">กำหนดระดับทักษะตาม Taxonomy</h2>
            <p class="text-sm text-gray-500 mt-0.5">ระบบจะผูกระดับความสามารถกับตำแหน่งงานที่เลือกโดยตรง เพื่อให้การประเมินสอดคล้องบริบทงานจริง</p>
            <details class="mt-3 border border-gray-200 rounded-lg px-4 py-2 text-sm text-gray-600 bg-gray-50">
                <summary class="cursor-pointer font-medium text-gray-700 select-none">ดูคำอธิบายระดับ Taxonomy</summary>
                <ul class="mt-2 space-y-1 ml-2">
                    @foreach($taxonomyLabels as $key => $label)
                        <li>{{ $label }}</li>
                    @endforeach
                </ul>
            </details>
        </div>

        {{-- Step 1-3 --}}
        <form method="GET" action="{{ route('admin.custom-taxonomy.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700">ขั้นที่ 1: เลือกกลุ่มงาน</label>
                <select name="group_id" id="groupSelect" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" required>
                    <option value="">เลือกกลุ่มงาน</option>
                    @foreach($groupsAll as $group)
                        <option value="{{ $group->id }}" {{ (int)$selectedGroupId === (int)$group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700">ขั้นที่ 2: เลือกตำแหน่งงาน</label>
                <select name="role_id" id="roleSelect" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" required>
                    <option value="">เลือกตำแหน่งงาน</option>
                    @foreach($rolesAll as $role)
                        <option value="{{ $role->id }}" data-group-id="{{ $role->custom_job_group_id }}" {{ (int)$selectedRoleId === (int)$role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700 select-none opacity-0">โหลด</label>
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">โหลดทักษะ</button>
            </div>
        </form>

        @if($selectedRole)
            {{-- Role info --}}
            <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-100 rounded-lg text-sm">
                <div>
                    <span class="text-gray-500">ตำแหน่งงานที่แก้ไข:</span>
                    <span class="font-semibold text-gray-900 ml-1">{{ $selectedRole->name }}</span>
                </div>
                <div class="text-gray-300">|</div>
                <div>
                    <span class="text-gray-500">กลุ่มงาน:</span>
                    <span class="font-medium text-gray-700 ml-1">{{ $selectedRole->group?->name }}</span>
                </div>
            </div>

            {{-- Add skill form --}}
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3">เพิ่มทักษะและกำหนดระดับ Taxonomy ให้ตำแหน่งงานนี้</h3>
                <form method="POST" action="{{ route('admin.custom-taxonomy.weights.store') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-3 items-end">
                    @csrf
                    <input type="hidden" name="custom_job_role_id" value="{{ $selectedRole->id }}">
                    <input type="hidden" name="weight" value="1">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700">เลือกทักษะ</label>
                        <select name="custom_skill_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" required>
                            <option value="">เลือกทักษะ</option>
                            @foreach($skillsAll as $skill)
                                <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700">ระดับ Taxonomy</label>
                        <select name="taxonomy_level" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" required>
                            @foreach($taxonomyLabels as $key => $label)
                                <option value="{{ $key }}" {{ $key === 'applying' ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">เพิ่มความสามารถ</button>
                    </div>
                </form>
            </div>

            {{-- Skills table --}}
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-700 w-2/5">ทักษะ</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-700 w-1/4">ระดับ Taxonomy</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-700 w-1/6">น้ำหนัก</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-700">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($roleWeights as $weight)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-2.5 font-medium text-gray-900">{{ $weight->skill?->name }}</td>
                                <td class="px-4 py-2.5">
                                    <form id="wf-{{ $weight->id }}" method="POST" action="{{ route('admin.custom-taxonomy.weights.update', $weight) }}">
                                        @csrf
                                        @method('PUT')
                                        <select name="taxonomy_level" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white" required>
                                            @foreach($taxonomyLabels as $key => $label)
                                                <option value="{{ $key }}" {{ $weight->taxonomy_level === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-2.5 font-semibold text-gray-700">{{ $weight->weight }}</td>
                                <td class="px-4 py-2.5">
                                    <div class="flex gap-1.5">
                                        <button form="wf-{{ $weight->id }}" type="submit" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">บันทึก</button>
                                        <form method="POST" action="{{ route('admin.custom-taxonomy.weights.destroy', $weight) }}" onsubmit="return confirm('ลบรายการทักษะนี้?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 text-xs bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition-colors font-medium">ลบ</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">ยังไม่มีทักษะที่ผูกกับตำแหน่งงานนี้</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-4 py-3 bg-blue-50 border border-blue-100 rounded-lg text-blue-700 text-sm">
                เลือกกลุ่มงานและตำแหน่งงานก่อน เพื่อกำหนดระดับทักษะตาม Taxonomy
            </div>
        @endif
    </div>

    {{-- Manage Groups --}}
    <div class="bg-white rounded-xl shadow p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">จัดการกลุ่มงาน</h3>
        <div>
            <input id="groupSearch" type="text" placeholder="ค้นหาชื่อกลุ่มงาน"
                class="w-full sm:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
        </div>
        <div class="overflow-x-auto border border-gray-200 rounded-lg max-h-96">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr class="border-b border-gray-200">
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700 w-1/2">ชื่อ</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700">จำนวนตำแหน่ง</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="groupsTableBody" class="divide-y divide-gray-100">
                    @foreach($groups as $group)
                        {{-- แถวข้อมูลหลัก --}}
                        <tr class="hover:bg-gray-50 transition-colors" data-search="{{ mb_strtolower($group->name) }}">
                            <td class="px-4 py-2.5">
                                <input form="group-update-{{ $group->id }}" name="name" value="{{ $group->name }}"
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                            </td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $group->roles_count }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex gap-1.5 flex-nowrap items-center">
                                    <form id="group-update-{{ $group->id }}" method="POST" action="{{ route('admin.custom-taxonomy.groups.update', $group) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">บันทึก</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.custom-taxonomy.groups.destroy', $group) }}" onsubmit="return confirm('ลบกลุ่มงานนี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-xs bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition-colors font-medium">ลบ</button>
                                    </form>
                                    <button type="button"
                                        class="detail-btn"
                                        onclick="toggleDetailRow('group-detail-row-{{ $group->id }}', this)">
                                        <svg class="chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2 4.5L6 8.5L10 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        รายละเอียด
                                    </button>
                                </div>
                            </td>
                        </tr>
                        {{-- แถว detail แยกต่างหาก ไม่กระทบแถวข้างบน --}}
                        <tr id="group-detail-row-{{ $group->id }}" class="detail-row" data-search="{{ mb_strtolower($group->name) }}">
                            <td colspan="3" class="p-0">
                                <div class="detail-inner">
                                    <div class="detail-label">ตำแหน่งงานในกลุ่มนี้</div>
                                    <div>
                                        @php $groupRoles = $rolesAll->where('custom_job_group_id', $group->id); @endphp
                                        @forelse($groupRoles as $role)
                                            <span class="detail-badge">{{ $role->name }}</span>
                                        @empty
                                            <span class="detail-empty">ไม่มีตำแหน่งงาน</span>
                                        @endforelse
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($groups->total() > 0)
            @include('admin.master-skills._esco-pagination', ['paginator' => $groups, 'perPageName' => 'groups_page'])
        @endif
    </div>

    {{-- Manage Roles --}}
    <div class="bg-white rounded-xl shadow p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">จัดการตำแหน่งงาน</h3>
        <div>
            <input id="roleSearch" type="text" placeholder="ค้นหาตำแหน่งงานหรือกลุ่มงาน"
                class="w-full sm:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
        </div>
        <div class="overflow-x-auto border border-gray-200 rounded-lg max-h-96">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr class="border-b border-gray-200">
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700">ตำแหน่งงาน</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700">กลุ่มงาน</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700">ทักษะ</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="rolesTableBody" class="divide-y divide-gray-100">
                    @foreach($roles->items() as $role)
                        {{-- แถวข้อมูลหลัก --}}
                        <tr class="hover:bg-gray-50 transition-colors" data-search="{{ mb_strtolower($role->name.' '.$role->group?->name) }}">
                            <td class="px-4 py-2.5">
                                <input form="role-update-{{ $role->id }}" name="name" value="{{ $role->name }}"
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none min-w-40" required>
                            </td>
                            <td class="px-4 py-2.5">
                                <select form="role-update-{{ $role->id }}" name="custom_job_group_id"
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none min-w-36" required>
                                    @foreach($groupsAll as $group)
                                        <option value="{{ $group->id }}" {{ (int)$role->custom_job_group_id === (int)$group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $role->skill_weights_count }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex gap-1.5 flex-nowrap items-center">
                                    <form id="role-update-{{ $role->id }}" method="POST" action="{{ route('admin.custom-taxonomy.roles.update', $role) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">บันทึก</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.custom-taxonomy.roles.destroy', $role) }}" onsubmit="return confirm('ลบตำแหน่งงานนี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-xs bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition-colors font-medium">ลบ</button>
                                    </form>
                                    <button type="button"
                                        class="detail-btn"
                                        onclick="toggleDetailRow('role-detail-row-{{ $role->id }}', this)">
                                        <svg class="chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2 4.5L6 8.5L10 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        รายละเอียด
                                    </button>
                                </div>
                            </td>
                        </tr>
                        {{-- แถว detail แยกต่างหาก --}}
                        <tr id="role-detail-row-{{ $role->id }}" class="detail-row" data-search="{{ mb_strtolower($role->name.' '.$role->group?->name) }}">
                            <td colspan="4" class="p-0">
                                <div class="detail-inner">
                                    <div class="detail-label">ทักษะที่ผูกกับตำแหน่งนี้</div>
                                    <div>
                                        @php $roleSkills = $role->skillWeights ?? collect(); @endphp
                                        @forelse($roleSkills as $w)
                                            <span class="detail-badge">{{ $w->skill?->name }}</span>
                                            <span class="detail-badge taxonomy">{{ $taxonomyLabels[$w->taxonomy_level] ?? $w->taxonomy_level }}</span>
                                        @empty
                                            <span class="detail-empty">ไม่มีทักษะ</span>
                                        @endforelse
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($roles->total() > 0)
            @include('admin.master-skills._esco-pagination', ['paginator' => $roles, 'perPageName' => 'roles_page'])
        @endif
    </div>

    {{-- Manage Skills --}}
    <div class="bg-white rounded-xl shadow p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">จัดการทักษะ</h3>
        <div>
            <input id="skillSearch" type="text" placeholder="ค้นหาทักษะ"
                class="w-full sm:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
        </div>
        <div class="overflow-x-auto border border-gray-200 rounded-lg max-h-96">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr class="border-b border-gray-200">
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700">ทักษะ</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700">ถูกใช้</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700">คะแนนทักษะ</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-700">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="skillsTableBody" class="divide-y divide-gray-100">
                    @foreach($skills->items() as $skill)
                        {{-- แถวข้อมูลหลัก --}}
                        <tr class="hover:bg-gray-50 transition-colors" data-search="{{ mb_strtolower($skill->name) }}">
                            <td class="px-4 py-2.5">
                                <input form="skill-update-{{ $skill->id }}" name="name" value="{{ $skill->name }}"
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none min-w-40" required>
                            </td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $skill->role_weights_count }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $skillScores[$skill->id] ?? '-' }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex gap-1.5 flex-nowrap items-center">
                                    <form id="skill-update-{{ $skill->id }}" method="POST" action="{{ route('admin.custom-taxonomy.skills.update', $skill) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">บันทึก</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.custom-taxonomy.skills.destroy', $skill) }}" onsubmit="return confirm('ลบทักษะนี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-xs bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition-colors font-medium">ลบ</button>
                                    </form>
                                    <button type="button"
                                        class="detail-btn"
                                        onclick="toggleDetailRow('skill-detail-row-{{ $skill->id }}', this)">
                                        <svg class="chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2 4.5L6 8.5L10 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        รายละเอียด
                                    </button>
                                </div>
                            </td>
                        </tr>
                        {{-- แถว detail แยกต่างหาก --}}
                        <tr id="skill-detail-row-{{ $skill->id }}" class="detail-row" data-search="{{ mb_strtolower($skill->name) }}">
                            <td colspan="4" class="p-0">
                                <div class="detail-inner">
                                    <div class="detail-label">ถูกใช้ในตำแหน่งงาน</div>
                                    <div>
                                        @php
                                            $skillRoles = $rolesAll->filter(fn($r) => $r->skillWeights && $r->skillWeights->where('custom_skill_id', $skill->id)->count() > 0);
                                        @endphp
                                        @forelse($skillRoles as $role)
                                            <span class="detail-badge">{{ $role->name }}</span>
                                        @empty
                                            <span class="detail-empty">ยังไม่มีตำแหน่งงานใช้ทักษะนี้</span>
                                        @endforelse
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($skills->total() > 0)
            @include('admin.master-skills._esco-pagination', ['paginator' => $skills, 'perPageName' => 'skills_page'])
        @endif
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const groupSelect = document.getElementById('groupSelect');
    const roleSelect  = document.getElementById('roleSelect');

    // --- Batch Tags ---
    function normalizeTagParts(input) {
        return (input || '').replace(/\r\n|\r/g, '\n').split(/[\n,]/).map(s => s.trim()).filter(s => s.length > 0);
    }

    function setupBatchTags(root) {
        const inputEl  = root.querySelector('[data-tags-input]');
        const addBtn   = root.querySelector('[data-tags-add]');
        const clearBtn = root.querySelector('[data-tags-clear]');
        const sampleBtn= root.querySelector('[data-tags-sample]');
        const listEl   = root.querySelector('[data-tags-list]');
        const targetEl = root.querySelector('[data-tags-target]');
        const formEl   = root.closest('form');
        const existingSet = new Set(JSON.parse(root.dataset.existing || '[]').map(n => String(n).trim().toLowerCase()));
        if (!inputEl || !listEl || !targetEl || !formEl) return;
        const tags = new Map();

        function renderTags() {
            listEl.innerHTML = '';
            if (tags.size === 0) {
                listEl.innerHTML = '<span class="text-xs text-gray-400">ยังไม่มีแท็ก</span>';
            }
            tags.forEach((tag) => {
                const exists = existingSet.has(tag.toLowerCase());
                const chip = document.createElement('span');
                chip.className = exists
                    ? 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-300'
                    : 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 border border-blue-300';
                chip.textContent = tag;
                const rm = document.createElement('button');
                rm.type = 'button'; rm.textContent = '×'; rm.className = 'font-bold hover:opacity-70';
                rm.onclick = () => { tags.delete(tag.toLowerCase()); renderTags(); };
                chip.appendChild(rm);
                listEl.appendChild(chip);
            });
            targetEl.value = Array.from(tags.values()).join('\n');
        }

        function addRaw(raw) {
            normalizeTagParts(raw).forEach(p => { const k = p.toLowerCase(); if (!tags.has(k)) tags.set(k, p); });
            renderTags();
        }
        function addFromInput() { addRaw(inputEl.value); inputEl.value = ''; inputEl.focus(); }

        addBtn?.addEventListener('click', addFromInput);
        clearBtn?.addEventListener('click', () => { tags.clear(); renderTags(); inputEl.focus(); });
        sampleBtn?.addEventListener('click', () => addRaw(sampleBtn.dataset.tagsSample || ''));
        inputEl.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addFromInput(); } });
        formEl.addEventListener('submit', e => { addFromInput(); if (!targetEl.value.trim()) { e.preventDefault(); inputEl.focus(); } });
        if ((targetEl.value || '').trim()) addRaw(targetEl.value);
        renderTags();
    }

    document.querySelectorAll('[data-batch-tags]').forEach(setupBatchTags);

    // --- Role filter by group ---
    function filterRolesByGroup() {
        const groupId = groupSelect?.value;
        const current = roleSelect?.value;
        Array.from(roleSelect?.options || []).forEach(opt => {
            if (!opt.value) { opt.hidden = false; return; }
            opt.hidden = !!(groupId && opt.dataset.groupId !== groupId);
        });
        const sel = roleSelect?.querySelector(`option[value="${current}"]`);
        if (!sel || sel.hidden) roleSelect.value = '';
    }

    groupSelect?.addEventListener('change', filterRolesByGroup);
    filterRolesByGroup();

    // --- Table search (รองรับแถว detail-row ด้วย) ---
    function bindSearch(inputId, tbodyId) {
        const input = document.getElementById(inputId);
        const tbody = document.getElementById(tbodyId);
        if (!input || !tbody) return;
        const rows = Array.from(tbody.querySelectorAll('tr'));
        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            rows.forEach(row => {
                const match = !q || (row.dataset.search || '').includes(q);
                // ถ้าเป็น detail-row ที่เปิดอยู่ ให้ซ่อนตาม filter แต่ไม่ reset class
                if (row.classList.contains('detail-row')) {
                    row.style.display = (match && row.classList.contains('open')) ? 'table-row' : 'none';
                } else {
                    row.style.display = match ? '' : 'none';
                }
            });
        });
    }

    bindSearch('groupSearch', 'groupsTableBody');
    bindSearch('roleSearch',  'rolesTableBody');
    bindSearch('skillSearch', 'skillsTableBody');
});

// Toggle detail row (แยกจากแถวข้อมูลหลัก ไม่ทำให้แถวอื่นขยับ)
function toggleDetailRow(rowId, btn) {
    const row = document.getElementById(rowId);
    if (!row) return;
    const isOpen = row.classList.contains('open');
    row.classList.toggle('open', !isOpen);
    if (btn) btn.classList.toggle('open', !isOpen);
}
</script>

@endsection
