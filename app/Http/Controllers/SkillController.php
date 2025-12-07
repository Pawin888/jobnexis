<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    // แสดงรายการ Skills
    public function index(Request $request)
    {
        $query = Skill::query();
        
        // การค้นหา
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // การเรียงลำดับ
        $sortBy = $request->get('sort', 'id'); // ค่าเริ่มต้น
        $sortDirection = $request->get('direction', 'asc');
        
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortDirection);
                break;
            case 'courses_count':
                $query->withCount('courses')->orderBy('courses_count', $sortDirection);
                break;
            default:
                $query->orderBy('id', $sortDirection);
                break;
        }
        
        $skills = $query->paginate(5)->appends($request->query());
        
        return view('admin.management-skills', compact('skills'));
    }

    // บันทึก Skill ใหม่
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:skills,name'
        ]);

        Skill::create([
            'name' => $request->name
        ]);

        return redirect()->route('management.skills.index')
                        ->with('success', 'เพิ่มทักษะใหม่เรียบร้อยแล้ว');
    }

    // อัพเดท Skill
    public function update(Request $request, Skill $skill)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:skills,name,' . $skill->id
        ]);

        $skill->update([
            'name' => $request->name
        ]);

        return redirect()->route('management.skills.index')
                        ->with('success', 'แก้ไขทักษะเรียบร้อยแล้ว');
    }

    // ลบ Skill
    public function destroy(Skill $skill)
    {
        // ตรวจสอบว่ามีการใช้งานอยู่หรือไม่
        if ($skill->courses()->count() > 0) {
            return redirect()->route('management.skills.index')
                            ->with('error', 'ไม่สามารถลบทักษะนี้ได้ เนื่องจากมีคอร์สที่ใช้งานอยู่');
        }

        $skill->delete();
        
        return redirect()->route('management.skills.index')
                        ->with('success', 'ลบทักษะเรียบร้อยแล้ว');
    }
}