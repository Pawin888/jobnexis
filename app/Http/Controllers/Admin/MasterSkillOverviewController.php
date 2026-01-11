<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterSkill;
use App\Models\MasterSkillGroup;

class MasterSkillOverviewController extends Controller
{
    public function index()
    {
        $skillGroups = MasterSkillGroup::withCount('skills')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $skills = MasterSkill::with('skillGroups')
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        return view('admin.master-skills.index', compact(
            'skillGroups',
            'skills'
        ));
    }
}
