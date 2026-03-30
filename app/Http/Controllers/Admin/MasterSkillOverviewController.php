<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterSkill;
use App\Models\MasterSkillGroup;
use Illuminate\Http\Request;

class MasterSkillOverviewController extends Controller
{
    public function index(Request $request)
    {
        $skillGroups = MasterSkillGroup::withCount('skills')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $search      = $request->get('search');
        $perPage     = $request->get('perPage', 20);
        $sortBy      = $request->get('sort', 'name');
        $sortDir     = $request->get('dir', 'asc');
        $filterGroup = $request->get('group');
        $filterSkill = $request->get('skill');

        $allowedSorts = ['name', 'group', 'level'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        $activeGroup = $filterGroup
            ? MasterSkillGroup::find($filterGroup)
            : null;

        $activeSkill = $filterSkill
            ? MasterSkill::with('skillGroups')->find($filterSkill)
            : null;

        $maxSkillGroupCount = (int) (MasterSkill::query()
            ->where('is_active', true)
            ->withCount('skillGroups')
            ->get()
            ->max('skill_groups_count') ?? 0);

        $skills = MasterSkill::with('skillGroups')
            ->withMin('skillGroups', 'name')
            ->withCount('skillGroups')
            ->where('master_skills.is_active', true)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('master_skills.name', 'like', '%' . $search . '%')
                      ->orWhere('master_skills.esco_uri', 'like', '%' . $search . '%')
                      ->orWhereHas('skillGroups', function ($q2) use ($search) {
                          $q2->where('master_skill_groups.name', 'like', '%' . $search . '%');
                      });
                });
            })
            ->when($filterGroup, function ($query, $filterGroup) {
                $query->whereHas('skillGroups', function ($q) use ($filterGroup) {
                    $q->where('master_skill_groups.id', $filterGroup);
                });
            })
            ->when($filterSkill, function ($query, $filterSkill) {
                $query->where('master_skills.id', $filterSkill);
            })
            ->when($sortBy === 'group', function ($query) use ($sortDir) {
                $query->orderByRaw("COALESCE(skill_groups_min_name, '') {$sortDir}")
                      ->orderBy('master_skills.name', 'asc');
            })
            ->when($sortBy === 'name', function ($query) use ($sortDir) {
                $query->orderBy('master_skills.name', $sortDir);
            })
            ->when($sortBy === 'level', function ($query) use ($sortDir) {
                $query->orderBy('skill_groups_count', $sortDir)
                      ->orderBy('master_skills.name', 'asc');
            })
            ->paginate($perPage);

        return view('admin.master-skills.index', compact(
            'skillGroups',
            'skills',
            'sortBy',
            'sortDir',
            'activeGroup',
            'activeSkill',
            'maxSkillGroupCount'
        ));
    }
}
