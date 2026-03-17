<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderCandidateInvite;
use App\Models\ProviderSavedCandidate;
use App\Models\Recruitment;
use App\Models\Resume;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        $providerId = Auth::id();
        $q = trim((string) $request->string('q'));
        $perPage = (int) $request->integer('perPage', 20);
        $perPage = in_array($perPage, [10, 20, 30, 50], true) ? $perPage : 20;

        $savedResumeIds = ProviderSavedCandidate::query()
            ->where('provider_id', $providerId)
            ->pluck('resume_id')
            ->all();

        $resumes = Resume::query()
            ->where('is_visible', true)
            ->whereHas('user', fn ($uq) => $uq->where('role', 'jobber'))
            ->with(['user', 'resumeSkills.skill', 'resumeSkills.skillGroup'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhereHas('resumeSkills.skill', fn ($sq) => $sq->where('name', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();

        $openRecruitments = Recruitment::query()
            ->where('rc_u_id', $providerId)
            ->where('rc_status', 'open')
            ->where(function ($q) {
                $q->whereNull('rc_expire_at')
                    ->orWhereDate('rc_expire_at', '>=', now()->toDateString());
            })
            ->orderByDesc('rc_posted_at')
            ->get(['rc_id', 'rc_title']);

        return view('provider.candidates.index', [
            'resumes' => $resumes,
            'savedResumeIds' => $savedResumeIds,
            'openRecruitments' => $openRecruitments,
            'filters' => [
                'q' => $q,
            ],
        ]);
    }

    public function show($resumeId)
    {
        $providerId = Auth::id();

        $resume = Resume::query()
            ->where('id', $resumeId)
            ->where('is_visible', true)
            ->whereHas('user', fn ($uq) => $uq->where('role', 'jobber'))
            ->with([
                'user',
                'workExperiences',
                'educations',
                'resumeSkills.skill',
                'resumeSkills.skillGroup',
                'languages',
                'certificates',
            ])
            ->firstOrFail();

        $isSaved = ProviderSavedCandidate::query()
            ->where('provider_id', $providerId)
            ->where('resume_id', $resume->id)
            ->exists();

        $openRecruitments = Recruitment::query()
            ->where('rc_u_id', $providerId)
            ->where('rc_status', 'open')
            ->where(function ($q) {
                $q->whereNull('rc_expire_at')
                    ->orWhereDate('rc_expire_at', '>=', now()->toDateString());
            })
            ->orderByDesc('rc_posted_at')
            ->get(['rc_id', 'rc_title']);

        return view('provider.candidates.show', [
            'resume' => $resume,
            'isSaved' => $isSaved,
            'openRecruitments' => $openRecruitments,
        ]);
    }

    public function toggleSave($resumeId)
    {
        $providerId = Auth::id();

        $resume = Resume::query()
            ->where('id', $resumeId)
            ->where('is_visible', true)
            ->firstOrFail();

        $saved = ProviderSavedCandidate::query()
            ->where('provider_id', $providerId)
            ->where('resume_id', $resume->id)
            ->first();

        if ($saved) {
            $saved->delete();
            return redirect()->back()->with('swal_success', 'นำออกจากบันทึกผู้สมัครแล้ว');
        }

        ProviderSavedCandidate::create([
            'provider_id' => $providerId,
            'resume_id' => $resume->id,
        ]);

        return redirect()->back()->with('swal_success', 'บันทึกผู้สมัครเรียบร้อยแล้ว');
    }

    public function invite(Request $request, $resumeId)
    {
        $providerId = Auth::id();

        $resume = Resume::query()
            ->where('id', $resumeId)
            ->where('is_visible', true)
            ->firstOrFail();

        $validated = $request->validate([
            'recruitment_id' => 'required|integer|exists:recruitments,rc_id',
            'message' => 'nullable|string|max:1000',
        ]);

        $recruitment = Recruitment::query()
            ->where('rc_id', $validated['recruitment_id'])
            ->where('rc_u_id', $providerId)
            ->firstOrFail();

        $exists = ProviderCandidateInvite::query()
            ->where('provider_id', $providerId)
            ->where('resume_id', $resume->id)
            ->where('recruitment_id', $recruitment->rc_id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('swal_error', 'เคยเชิญผู้สมัครคนนี้กับประกาศนี้แล้ว');
        }

        ProviderCandidateInvite::create([
            'provider_id' => $providerId,
            'resume_id' => $resume->id,
            'recruitment_id' => $recruitment->rc_id,
            'message' => $validated['message'] ?? null,
            'invited_at' => now(),
        ]);

        return redirect()->back()->with('swal_success', 'เชิญสมัครงานเรียบร้อยแล้ว');
    }

    public function downloadResumePdf($resumeId)
    {
        $resume = Resume::query()
            ->where('id', $resumeId)
            ->where('is_visible', true)
            ->with(['user', 'workExperiences', 'educations', 'resumeSkills.skill', 'resumeSkills.skillGroup', 'languages', 'certificates'])
            ->firstOrFail();

        $pdf = Pdf::loadView('provider.candidates.resume-pdf', [
            'resume' => $resume,
        ])->setPaper('a4');

        return $pdf->download('resume-' . $resume->id . '.pdf');
    }
}
