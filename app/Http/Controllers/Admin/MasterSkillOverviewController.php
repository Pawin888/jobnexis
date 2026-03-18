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

        $allowedSorts = ['name', 'group'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        $activeGroup = $filterGroup
            ? MasterSkillGroup::find($filterGroup)
            : null;

        $skills = MasterSkill::with('skillGroups')
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
            ->when($sortBy === 'group', function ($query) use ($sortDir) {
                $query->leftJoin('master_skill_group_skill', 'master_skills.id', '=', 'master_skill_group_skill.master_skill_id')
                      ->leftJoin('master_skill_groups', 'master_skill_group_skill.master_skill_group_id', '=', 'master_skill_groups.id')
                      ->orderBy('master_skill_groups.name', $sortDir)
                      ->select('master_skills.*');
            })
            ->when($sortBy === 'name', function ($query) use ($sortDir) {
                $query->orderBy('master_skills.name', $sortDir);
            })
            ->paginate($perPage);

        return view('admin.master-skills.index', compact(
            'skillGroups',
            'skills',
            'sortBy',
            'sortDir',
            'activeGroup'
        ));
    }
}
