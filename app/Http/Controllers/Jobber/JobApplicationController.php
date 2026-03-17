<?php

namespace App\Http\Controllers\Jobber;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\ProviderCandidateInvite;
use App\Models\Resume;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index(Request $request)
    {
        $jobber = auth()->user();
        $perPage = (int) $request->integer('perPage', 20);
        $perPage = in_array($perPage, [10, 20, 30, 50], true) ? $perPage : 20;

        $applications = JobApplication::query()
            ->where('jobber_id', $jobber->id)
            ->whereIn('status', ['reviewing', 'accepted', 'rejected'])
            ->with(['recruitment', 'recruitment.owner', 'recruitment.owner.companyProfile'])
            ->orderByDesc('applied_at')
            ->paginate($perPage)
            ->withQueryString();

        $invites = ProviderCandidateInvite::query()
            ->whereHas('resume', fn ($q) => $q->where('user_id', $jobber->id))
            ->with(['recruitment', 'provider', 'provider.companyProfile'])
            ->orderByDesc('invited_at')
            ->get();

        return view('jobber.applications.index', [
            'applications' => $applications,
            'invites' => $invites,
        ]);
    }

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
            'status' => 'reviewing',
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
