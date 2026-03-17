<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Recruitment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ApplicationController extends Controller
{
    private function findOwnedApplicationOrFail(int $applicationId): JobApplication
    {
        $application = JobApplication::with(['recruitment'])->findOrFail($applicationId);

        if ($application->recruitment->rc_u_id !== Auth::id()) {
            abort(403);
        }

        return $application;
    }

    // รายการใบสมัครของประกาศทั้งหมด
    public function index(Request $request)
    {
        $provider = Auth::user();
        $status = $request->string('status')->toString();
        $perPage = (int) $request->integer('perPage', 20);
        $perPage = in_array($perPage, [10, 20, 30, 50], true) ? $perPage : 20;

        // ดึง recruitment ที่เป็นของ provider นี้
        $recruitmentIds = Recruitment::where('rc_u_id', $provider->id)
            ->pluck('rc_id')
            ->toArray();

        // ดึงใบสมัครทั้งหมด
        $applications = JobApplication::query()
            ->whereIn('recruitment_id', $recruitmentIds)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['recruitment', 'jobber', 'jobber.profile', 'resume'])
            ->orderByDesc('is_shortlisted')
            ->orderBy('applied_at', 'asc')
            ->paginate($perPage)
            ->withQueryString();

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
            'resume.resumeSkills.skill',
            'resume.resumeSkills.skillGroup',
            'resume.certificates',
            'resume.languages',
        ])
            ->findOrFail($applicationId);

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
        $application = $this->findOwnedApplicationOrFail((int) $applicationId);

        $validated = $request->validate([
            'status' => 'required|in:reviewing,accepted,rejected',
            'review_note' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'status' => $validated['status'],
            'review_note' => $validated['review_note'] ?? null,
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('swal_success', 'อัปเดตสถานะเรียบร้อยแล้ว');
    }

    // โน้ตภายในสำหรับ HR/ผู้ประกาศ
    public function updateInternalNote(Request $request, $applicationId)
    {
        $application = $this->findOwnedApplicationOrFail((int) $applicationId);

        $validated = $request->validate([
            'internal_note' => 'nullable|string|max:5000',
        ]);

        $application->update([
            'internal_note' => $validated['internal_note'] ?? null,
        ]);

        return redirect()->back()->with('swal_success', 'บันทึกโน้ตภายในเรียบร้อยแล้ว');
    }

    // สลับสถานะตัวเต็ง (Shortlist)
    public function toggleShortlist($applicationId)
    {
        $application = $this->findOwnedApplicationOrFail((int) $applicationId);

        if ($application->status === 'rejected') {
            return redirect()->back()->with('swal_error', 'ผู้สมัครที่ถูกปฏิเสธไม่สามารถบันทึกเป็นตัวเต็งได้');
        }

        $isShortlisted = !((bool) $application->is_shortlisted);

        $application->update([
            'is_shortlisted' => $isShortlisted,
            'shortlisted_at' => $isShortlisted ? now() : null,
        ]);

        return redirect()->back()->with(
            'swal_success',
            $isShortlisted ? 'บันทึกเป็นตัวเต็งแล้ว' : 'นำออกจากตัวเต็งแล้ว'
        );
    }

    // ดาวน์โหลด Resume เป็น PDF
    public function downloadResumePdf($applicationId)
    {
        $application = JobApplication::with([
            'recruitment',
            'jobber',
            'resume',
            'resume.workExperiences',
            'resume.educations',
            'resume.resumeSkills.skill',
            'resume.resumeSkills.skillGroup',
            'resume.certificates',
            'resume.languages',
        ])->findOrFail($applicationId);

        if ($application->recruitment->rc_u_id !== Auth::id()) {
            abort(403);
        }

        $pdf = Pdf::loadView('provider.applications.resume-pdf', [
            'application' => $application,
        ])->setPaper('a4');

        $fileName = 'resume-application-' . $application->id . '.pdf';

        return $pdf->download($fileName);
    }

    // ลบใบสมัคร
    public function destroy($applicationId)
    {
        $application = $this->findOwnedApplicationOrFail((int) $applicationId);

        $application->delete();

        return redirect()->route('provider.applications.index')->with('swal_success', 'ลบใบสมัครเรียบร้อยแล้ว');
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
