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

        // รับค่า search และ perPage จาก request
        $search = $request->get('search');
        $perPage = $request->get('perPage', 20);

        // Query skills พร้อมการค้นหา
        $skills = MasterSkill::with('skillGroups')
            ->where('is_active', true)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('esco_uri', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate($perPage);

        return view('admin.master-skills.index', compact(
            'skillGroups',
            'skills'
        ));
    }
}
