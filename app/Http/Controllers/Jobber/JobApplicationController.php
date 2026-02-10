<?php

namespace App\Http\Controllers\Jobber;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Resume;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function apply(Request $request, $rcId)
    {
        $jobber = auth()->user();
        
        // ตรวจสอบว่ามี resume หรือไม่
        $resume = Resume::where('user_id', $jobber->id)
            ->where('is_visible', true)
            ->first();
        
        if (!$resume) {
            return back()->with('error', 'กรุณาสร้างเรซูเม่ก่อนสมัครงาน');
        }
        
        // ตรวจสอบว่าเคยสมัครแล้วหรือไม่ (ไม่รวม withdrawn)
        $existingApplication = JobApplication::where('recruitment_id', $rcId)
            ->where('jobber_id', $jobber->id)
            ->whereNotIn('status', ['withdrawn'])
            ->first();
        
        if ($existingApplication) {
            return back()->with('error', 'คุณได้สมัครงานนี้แล้ว');
        }
        
        // สร้างใบสมัคร
        JobApplication::create([
            'recruitment_id' => $rcId,
            'jobber_id' => $jobber->id,
            'resume_id' => $resume->id,
            'status' => 'applied',
            'cover_letter' => $request->input('cover_letter'),
            'applied_at' => now(),
        ]);
        
        return back()->with('success', 'สมัครงานสำเร็จ!');
    }

    public function withdraw($rcId)
    {
        $jobber = auth()->user();
        
        $application = JobApplication::where('recruitment_id', $rcId)
            ->where('jobber_id', $jobber->id)
            ->whereNotIn('status', ['withdrawn'])
            ->first();
        
        if (!$application) {
            return back()->with('error', 'ไม่พบใบสมัครงานนี้');
        }
        
        // ลบเลย แทนการเปลี่ยนสถานะ
        $application->forceDelete();
        
        return back()->with('success', 'ถอนการสมัครงานเรียบร้อยแล้ว');
    }
}