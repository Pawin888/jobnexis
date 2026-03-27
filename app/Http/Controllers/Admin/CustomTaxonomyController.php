<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomJobGroup;
use App\Models\CustomJobRole;
use App\Models\CustomRoleSkillWeight;
use App\Models\CustomSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomTaxonomyController extends Controller
{
    public function index(Request $request): View
    {
        $groups = CustomJobGroup::query()
            ->withCount('roles')
            ->orderBy('name')
            ->paginate(10, ['*'], 'groups_page');
        $groupsAll = CustomJobGroup::query()->orderBy('name')->get();

        $roles = CustomJobRole::query()
            ->with('group')
            ->withCount('skillWeights')
            ->orderBy('name')
            ->paginate(10, ['*'], 'roles_page');
        $rolesAll = CustomJobRole::query()->orderBy('name')->get();

        $skills = CustomSkill::query()
            ->withCount('roleWeights')
            ->orderBy('name')
            ->paginate(10, ['*'], 'skills_page');
        $skillsAll = CustomSkill::query()->orderBy('name')->get();

        $selectedGroupId = (int) $request->query('group_id', 0);
        $selectedRoleId = (int) $request->query('role_id', 0);

        $selectedRole = null;
        $roleWeights = collect();

        if ($selectedRoleId > 0) {
            $selectedRole = CustomJobRole::query()->with('group')->find($selectedRoleId);
            if ($selectedRole) {
                $selectedGroupId = (int) $selectedRole->custom_job_group_id;
                $roleWeights = CustomRoleSkillWeight::query()
                    ->where('custom_job_role_id', $selectedRole->id)
                    ->with('skill')
                    ->orderByRaw("CASE taxonomy_level
                        WHEN 'creating' THEN 6
                        WHEN 'evaluating' THEN 5
                        WHEN 'analyzing' THEN 4
                        WHEN 'applying' THEN 3
                        WHEN 'understanding' THEN 2
                        WHEN 'remembering' THEN 1
                        ELSE 0 END DESC")
                    ->get();
            }
        }

        return view('admin.custom-taxonomy.index', compact(
            'groups',
            'groupsAll',
            'roles',
            'rolesAll',
            'skills',
            'skillsAll',
            'selectedGroupId',
            'selectedRoleId',
            'selectedRole',
            'roleWeights'
        ));
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'names' => 'required|string',
        ]);

        $created = 0;
        $skipped = 0;

        foreach ($this->parseBatchNames((string) $validated['names']) as $name) {
            if (CustomJobGroup::query()->where('name', $name)->exists()) {
                $skipped++;
                continue;
            }

            CustomJobGroup::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => null,
            ]);

            $created++;
        }

        if ($created === 0) {
            return back()->with('error', 'ไม่พบรายการใหม่ที่สามารถเพิ่มได้');
        }

        return back()->with('success', "เพิ่มกลุ่มงานสำเร็จ {$created} รายการ" . ($skipped > 0 ? " (ข้ามซ้ำ {$skipped})" : ''));
    }

    public function updateGroup(Request $request, CustomJobGroup $group): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:custom_job_groups,name,' . $group->id,
        ]);

        $group->update([
            'name' => trim((string) $validated['name']),
            'slug' => Str::slug((string) $validated['name']),
        ]);

        return back()->with('success', 'อัปเดตกลุ่มงานสำเร็จ');
    }

    public function destroyGroup(CustomJobGroup $group): RedirectResponse
    {
        if ($group->roles()->exists()) {
            return back()->with('error', 'ลบกลุ่มงานไม่ได้ เนื่องจากยังมีตำแหน่งงานในกลุ่มนี้');
        }

        $group->delete();
        return back()->with('success', 'ลบกลุ่มงานสำเร็จ');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'custom_job_group_id' => 'required|exists:custom_job_groups,id',
            'names' => 'required|string',
        ]);

        $groupId = (int) $validated['custom_job_group_id'];
        $created = 0;
        $skipped = 0;

        foreach ($this->parseBatchNames((string) $validated['names']) as $name) {
            $slug = Str::slug($name);

            $exists = CustomJobRole::query()
                ->where('custom_job_group_id', $groupId)
                ->where('slug', $slug)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            CustomJobRole::create([
                'custom_job_group_id' => $groupId,
                'name' => $name,
                'slug' => $slug,
                'description' => null,
            ]);

            $created++;
        }

        if ($created === 0) {
            return back()->with('error', 'ไม่พบรายการใหม่ที่สามารถเพิ่มได้ในกลุ่มงานที่เลือก');
        }

        return back()->with('success', "เพิ่มตำแหน่งงานสำเร็จ {$created} รายการ" . ($skipped > 0 ? " (ข้ามซ้ำ {$skipped})" : ''));
    }

    public function updateRole(Request $request, CustomJobRole $role): RedirectResponse
    {
        $validated = $request->validate([
            'custom_job_group_id' => 'required|exists:custom_job_groups,id',
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug((string) $validated['name']);

        $exists = CustomJobRole::query()
            ->where('custom_job_group_id', $validated['custom_job_group_id'])
            ->where('slug', $slug)
            ->where('id', '!=', $role->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'ตำแหน่งงานนี้มีอยู่แล้วในกลุ่มงานที่เลือก');
        }

        $role->update([
            'custom_job_group_id' => (int) $validated['custom_job_group_id'],
            'name' => trim((string) $validated['name']),
            'slug' => $slug,
        ]);

        return back()->with('success', 'อัปเดตตำแหน่งงานสำเร็จ');
    }

    public function destroyRole(CustomJobRole $role): RedirectResponse
    {
        if ($role->skillWeights()->exists()) {
            return back()->with('error', 'ลบตำแหน่งงานไม่ได้ เนื่องจากยังมีทักษะผูกอยู่');
        }

        $role->delete();
        return back()->with('success', 'ลบตำแหน่งงานสำเร็จ');
    }

    public function storeSkill(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'names' => 'required|string',
        ]);

        $created = 0;
        $skipped = 0;

        foreach ($this->parseBatchNames((string) $validated['names']) as $name) {
            if (CustomSkill::query()->where('name', $name)->exists()) {
                $skipped++;
                continue;
            }

            CustomSkill::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => null,
            ]);

            $created++;
        }

        if ($created === 0) {
            return back()->with('error', 'ไม่พบรายการใหม่ที่สามารถเพิ่มได้');
        }

        return back()->with('success', "เพิ่มทักษะสำเร็จ {$created} รายการ" . ($skipped > 0 ? " (ข้ามซ้ำ {$skipped})" : ''));
    }

    public function updateSkill(Request $request, CustomSkill $skill): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:custom_skills,name,' . $skill->id,
        ]);

        $skill->update([
            'name' => trim((string) $validated['name']),
            'slug' => Str::slug((string) $validated['name']),
        ]);

        return back()->with('success', 'อัปเดตทักษะสำเร็จ');
    }

    public function destroySkill(CustomSkill $skill): RedirectResponse
    {
        if ($skill->roleWeights()->exists()) {
            return back()->with('error', 'ลบทักษะไม่ได้ เนื่องจากยังถูกใช้อยู่ในตำแหน่งงาน');
        }

        $skill->delete();
        return back()->with('success', 'ลบทักษะสำเร็จ');
    }

    public function storeWeight(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'custom_job_role_id' => 'required|exists:custom_job_roles,id',
            'custom_skill_id' => 'required|exists:custom_skills,id',
            'taxonomy_level' => 'required|in:remembering,understanding,applying,analyzing,evaluating,creating',
        ]);

        $taxonomyWeightMap = [
            'remembering' => 1,
            'understanding' => 2,
            'applying' => 3,
            'analyzing' => 4,
            'evaluating' => 5,
            'creating' => 6,
        ];
        $weightValue = $taxonomyWeightMap[$validated['taxonomy_level']] ?? 1;

        $record = CustomRoleSkillWeight::query()->firstOrNew([
            'custom_job_role_id' => (int) $validated['custom_job_role_id'],
            'custom_skill_id' => (int) $validated['custom_skill_id'],
        ]);

        $record->taxonomy_level = $validated['taxonomy_level'];
        $record->weight = $weightValue;
        $record->save();

        return $this->redirectToRoleWeight((int) $validated['custom_job_role_id'], 'บันทึกระดับทักษะตาม Taxonomy สำเร็จ');
    }

    public function updateWeight(Request $request, CustomRoleSkillWeight $weight): RedirectResponse
    {
        $validated = $request->validate([
            'taxonomy_level' => 'required|in:remembering,understanding,applying,analyzing,evaluating,creating',
        ]);

        $taxonomyWeightMap = [
            'remembering' => 1,
            'understanding' => 2,
            'applying' => 3,
            'analyzing' => 4,
            'evaluating' => 5,
            'creating' => 6,
        ];
        $weightValue = $taxonomyWeightMap[$validated['taxonomy_level']] ?? 1;

        $weight->update([
            'taxonomy_level' => $validated['taxonomy_level'],
            'weight' => $weightValue,
        ]);

        return $this->redirectToRoleWeight((int) $weight->custom_job_role_id, 'อัปเดตระดับทักษะตาม Taxonomy สำเร็จ');
    }

    public function destroyWeight(CustomRoleSkillWeight $weight): RedirectResponse
    {
        $roleId = (int) $weight->custom_job_role_id;
        $weight->delete();

        return $this->redirectToRoleWeight($roleId, 'ลบรายการทักษะสำเร็จ');
    }

    public function rolesByGroup(CustomJobGroup $group): JsonResponse
    {
        $roles = $group->roles()->orderBy('name')->get(['id', 'name', 'custom_job_group_id']);
        return response()->json($roles);
    }

    public function skillsByRole(CustomJobRole $role): JsonResponse
    {
        $skills = $role->skillWeights()
            ->with('skill:id,name')
            ->orderByRaw("CASE taxonomy_level
                WHEN 'creating' THEN 6
                WHEN 'evaluating' THEN 5
                WHEN 'analyzing' THEN 4
                WHEN 'applying' THEN 3
                WHEN 'understanding' THEN 2
                WHEN 'remembering' THEN 1
                ELSE 0 END DESC")
            ->get()
            ->map(fn(CustomRoleSkillWeight $item) => [
                'skill_id' => $item->custom_skill_id,
                'name' => $item->skill?->name,
                'taxonomy_level' => $item->taxonomy_level,
            ])
            ->values();

        return response()->json($skills);
    }

    private function redirectToRoleWeight(int $roleId, string $message): RedirectResponse
    {
        return redirect()
            ->to(route('admin.custom-taxonomy.index', ['role_id' => $roleId]) . '#taxonomy-panel')
            ->with('success', $message);
    }

    /**
     * @return array<int, string>
     */
    private function parseBatchNames(string $input): array
    {
        $normalized = str_replace(["\r\n", "\r", ','], "\n", $input);
        $parts = preg_split('/\n+/', $normalized) ?: [];

        $unique = [];
        foreach ($parts as $part) {
            $name = trim((string) $part);
            if ($name === '') {
                continue;
            }

            $key = mb_strtolower($name);
            if (!isset($unique[$key])) {
                $unique[$key] = $name;
            }
        }

        return array_values($unique);
    }
}
