<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Recruitment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{

    // รายการใบสมัครของประกาศทั้งหมด
    public function index(Request $request)
    {
        $provider = Auth::user();
        $status = $request->string('status')->toString();

        // ดึง recruitment ที่เป็นของ provider นี้
        $recruitmentIds = Recruitment::where('rc_u_id', $provider->id)
            ->pluck('rc_id')
            ->toArray();

        // ดึงใบสมัครทั้งหมด
        $applications = JobApplication::query()
            ->whereIn('recruitment_id', $recruitmentIds)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['recruitment', 'jobber', 'jobber.profile', 'resume'])
            ->recent()
            ->paginate(15);

        return view('provider.applications.index', [
            'applications' => $applications,
            'status' => $status,
            'statuses' => ['applied', 'reviewing', 'accepted', 'rejected', 'withdrawn'],
        ]);
    }

    // ดูรายละเอียดใบสมัครและรีซูเมต่อ recruitment
    public function show($applicationId)
    {
        $application = JobApplication::with([
            'recruitment',
            'jobber',
            'jobber.profile',
            'resume',
            'resume.workExperiences',
            'resume.educations',
            'resume.resumeSkills',
            'resume.certificates',
        ])
        ->findOrFail($applicationId);

        // ตรวจสอบว่า provider มีอำนาจเข้าถึง
        if ($application->recruitment->rc_u_id !== Auth::id()) {
            abort(403);
        }

        return view('provider.applications.show', [
            'application' => $application,
        ]);
    }

    // อัปเดตสถานะใบสมัคร
    public function updateStatus(Request $request, $applicationId)
    {
        $application = JobApplication::findOrFail($applicationId);

        // ตรวจสอบสิทธิ์
        if ($application->recruitment->rc_u_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:reviewing,accepted,rejected',
            'review_note' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'status' => $validated['status'],
            'review_note' => $validated['review_note'] ?? null,
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'อัปเดตสถานะเรียบร้อยแล้ว');
    }

    // ดูใบสมัครของประกาศป๋อหนึ่ง
    public function byRecruit($recruitmentId)
    {
        $recruitment = Recruitment::findOrFail($recruitmentId);

        // ตรวจสอบสิทธิ์
        if ($recruitment->rc_u_id !== Auth::id()) {
            abort(403);
        }

        $applications = JobApplication::byRecruit($recruitmentId)
            ->with(['jobber', 'jobber.profile', 'resume'])
            ->recent()
            ->paginate(20);

        return view('provider.applications.by-recruit', [
            'recruitment' => $recruitment,
            'applications' => $applications,
        ]);
    }
}