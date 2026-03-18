<?php

namespace App\Http\Controllers;

use App\Models\Recruitment;
use App\Models\RecruitmentSkill;
use App\Models\RecruitmentLanguage;
use App\Models\CompaniesProfile;
use App\Models\MasterSkill;
use App\Models\MasterSkillGroup;
use App\Models\JobApplication;
use App\Models\ProviderCandidateInvite;
use App\Models\Resume;
use App\Models\User;
use App\Services\JobMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class RecruitmentController extends Controller
{
    /** Jobber: ค้นหา/หางาน (เฉพาะประกาศเปิดรับ) */
    public function jobberIndex(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'jobber') abort(403);

        [$q, $type, $mode] = [
            $request->string('q')->toString(),
            $request->string('type')->toString(),
            $request->string('work_mode')->toString(),
        ];

        $query = Recruitment::query()->open()
            ->with([
                'recruitmentSkills.skillGroup',
                'recruitmentSkills.skill',
                'languages',
            ])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('rc_title', 'ilike', "%{$q}%")
                      ->orWhere('rc_description', 'ilike', "%{$q}%")
                      ->orWhere('rc_requirements', 'ilike', "%{$q}%");
                });
            })
            ->when($type, fn($qq) => $this->applyTypeFilter($qq, $type))
            ->when($mode, fn($qq) => $this->applyWorkModeFilter($qq, $mode))
            ->orderByDesc('rc_posted_at');

        $resume = Resume::query()
            ->with([
                'resumeSkills.skill',
                'languages',
                'workExperiences',
                'educations',
            ])
            ->where('user_id', Auth::id())
            ->first();

        $allRecs = $query->get();
        $matcher = app(JobMatchingService::class);
        $scoredRecs = $matcher->scoreRecruitmentsForResume($allRecs, $resume)
            ->sort(function ($a, $b) {
                $scoreA = (int) data_get($a, 'matching_meta.total_score', 0);
                $scoreB = (int) data_get($b, 'matching_meta.total_score', 0);

                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA;
                }

                $postedA = optional($a->rc_posted_at)->timestamp ?? 0;
                $postedB = optional($b->rc_posted_at)->timestamp ?? 0;
                return $postedB <=> $postedA;
            })
            ->values();

        $topMatches = $scoredRecs->take(5)->values();
        $recommendedJobs = $matcher->recommendRecruitments($scoredRecs, 6, $topMatches)
            ->filter(function ($rec) use ($topMatches) {
                return !$topMatches->contains('rc_id', $rec->rc_id);
            })
            ->values()
            ->take(6);

        $perPage = (int) $request->input('perPage', 12);
        if (!in_array($perPage, [12, 24, 36], true)) {
            $perPage = 12;
        }
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $scoredRecs
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $recs = new LengthAwarePaginator(
            $currentItems,
            $scoredRecs->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // ดึงข้อมูลบริษัทของเจ้าของประกาศ เพื่อแสดงชื่อ/โลโก้
        $ownerIds = $recs->getCollection()
            ->pluck('rc_u_id')
            ->merge($topMatches->pluck('rc_u_id'))
            ->merge($recommendedJobs->pluck('rc_u_id'))
            ->unique()
            ->values();
        $companies = \Illuminate\Support\Facades\DB::table('companies_profiles')
            ->whereIn('co_user_id', $ownerIds)
            ->get()
            ->keyBy('co_user_id');

        return view('jobber.recruitments.index', [
            'recs' => $recs,
            'companies' => $companies,
            'topMatches' => $topMatches,
            'recommendedJobs' => $recommendedJobs,
            'matchingEnabled' => (bool) $resume,
            'filters' => [
                'q' => $q,
                'type' => $type,
                'work_mode' => $mode,
                'perPage' => $perPage,
            ],
        ]);
    }

    /** Jobber API: คะแนน Matching สำหรับ dashboard/mobile */
    public function jobberMatchingScores(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'jobber') {
            abort(403);
        }

        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $resume = Resume::query()
            ->with([
                'resumeSkills.skill',
                'languages',
                'workExperiences',
                'educations',
            ])
            ->where('user_id', Auth::id())
            ->first();

        if (!$resume) {
            return response()->json([
                'matchingEnabled' => false,
                'message' => 'ยังไม่มีเรซูเม่สำหรับคำนวณคะแนน',
                'jobs' => [],
                'topMatches' => [],
                'recommended' => [],
            ]);
        }

        $query = Recruitment::query()->open()
            ->with([
                'recruitmentSkills.skill',
                'recruitmentSkills.skillGroup',
                'languages',
            ]);

        if ($ids->isNotEmpty()) {
            $query->whereIn('rc_id', $ids);
        }

        $jobs = $query->limit($ids->isNotEmpty() ? 100 : 50)->get();
        $matcher = app(JobMatchingService::class);
        $scored = $matcher->scoreRecruitmentsForResume($jobs, $resume)
            ->sortByDesc(fn ($rec) => (int) data_get($rec, 'matching_meta.total_score', 0))
            ->values();

        $topMatches = $scored->take(5)->values();
        $recommended = $matcher->recommendRecruitments($scored, 6, $topMatches)
            ->filter(function ($rec) use ($topMatches) {
                return !$topMatches->contains('rc_id', $rec->rc_id);
            })
            ->values()
            ->take(6);

        $toPayload = function ($rec) {
            $breakdown = data_get($rec, 'matching_meta.breakdown', []);
            $criteriaDefined = data_get($rec, 'matching_meta.criteria_defined', []);

            $factorMeta = [
                'skill_match' => 'ทักษะ',
                'skill_level' => 'ระดับทักษะ',
                'language' => 'ภาษา',
                'experience' => 'ประสบการณ์',
                'education' => 'การศึกษา',
                'location' => 'สถานที่',
                'gender' => 'เพศ',
            ];

            $breakdownThai = collect($factorMeta)->mapWithKeys(function ($label, $key) use ($breakdown, $criteriaDefined) {
                $defined = (bool) ($criteriaDefined[$key] ?? false);
                $value = (int) ($breakdown[$key] ?? 0);

                return [
                    $key => [
                        'label' => $label,
                        'defined' => $defined,
                        'value' => $value,
                        'display' => $defined ? ($value . '%') : 'ไม่ระบุ',
                    ],
                ];
            })->all();

            return [
                'id' => $rec->rc_id,
                'title' => $rec->rc_title,
                'type' => $rec->type_values,
                'type_labels' => $rec->type_labels,
                'work_mode' => $rec->work_mode_values,
                'work_mode_labels' => $rec->work_mode_labels,
                'score' => (int) data_get($rec, 'matching_meta.total_score', 0),
                'breakdown' => $breakdown,
                'criteria_defined' => $criteriaDefined,
                'breakdown_th' => $breakdownThai,
                'embedding' => (float) data_get($rec, 'matching_meta.embedding_similarity', 0),
                'required' => [
                    'label' => 'ทักษะสำคัญ',
                    'matched' => (int) data_get($rec, 'matching_meta.required_skills_matched', 0),
                    'total' => (int) data_get($rec, 'matching_meta.required_skills_total', 0),
                ],
                'optional' => [
                    'label' => 'ทักษะโบนัส',
                    'matched' => (int) data_get($rec, 'matching_meta.optional_skills_matched', 0),
                    'total' => (int) data_get($rec, 'matching_meta.optional_skills_total', 0),
                ],
            ];
        };

        return response()->json([
            'matchingEnabled' => true,
            'jobs' => $scored->map($toPayload)->values(),
            'topMatches' => $topMatches->map($toPayload)->values(),
            'recommended' => $recommended->map($toPayload)->values(),
        ]);
    }

    /** Public: ค้นหา/หางาน (เปิดสำหรับผู้ที่ยังไม่ล็อกอิน) */
    public function publicIndex(Request $request)
    {
        [$q, $type, $mode] = [
            $request->string('q')->toString(),
            $request->string('type')->toString(),
            $request->string('work_mode')->toString(),
        ];

        $recs = Recruitment::query()->open()
            ->with('skills')
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('rc_title', 'ilike', "%{$q}%")
                      ->orWhere('rc_description', 'ilike', "%{$q}%")
                      ->orWhere('rc_requirements', 'ilike', "%{$q}%");
                });
            })
            ->when($type, fn($qq) => $this->applyTypeFilter($qq, $type))
            ->when($mode, fn($qq) => $this->applyWorkModeFilter($qq, $mode))
            ->orderByDesc('rc_posted_at')
            ->paginate(12)
            ->withQueryString();

        $ownerIds = $recs->pluck('rc_u_id')->unique()->values();
        $companies = \Illuminate\Support\Facades\DB::table('companies_profiles')
            ->whereIn('co_user_id', $ownerIds)
            ->get()
            ->keyBy('co_user_id');

        return view('jobber.recruitments.index', [
            'recs' => $recs,
            'companies' => $companies,
            'topMatches' => collect(),
            'recommendedJobs' => collect(),
            'matchingEnabled' => false,
            'filters' => [
                'q' => $q,
                'type' => $type,
                'work_mode' => $mode,
                'perPage' => 12,
            ],
        ]);
    }
    
    /** Jobber: ดูรายละเอียดงาน (เปิดรับทั้งหมดสำหรับผู้ที่มีสิทธิ์เข้าถึง) */
    public function jobberShow($rcId)
    {
        if (!Auth::check() || Auth::user()->role !== 'jobber') abort(403);

        $rec = Recruitment::query()
            ->with([
                'recruitmentSkills.skillGroup',
                'recruitmentSkills.skill',
                'skills',
                'languages',
            ])
            ->findOrFail($rcId);

        $resume = Resume::query()
            ->with([
                'resumeSkills.skill',
                'languages',
                'workExperiences',
                'educations',
            ])
            ->where('user_id', Auth::id())
            ->first();

        $matching = app(JobMatchingService::class)->scoreSingleRecruitment($rec, $resume);

        $isOpen = $rec->rc_status === 'open'
            && (
                is_null($rec->rc_expire_at)
                || \Illuminate\Support\Carbon::parse($rec->rc_expire_at)->endOfDay()->isFuture()
            );

        if (!$isOpen) {
            $jobberId = Auth::id();

            $hasApplication = JobApplication::query()
                ->where('recruitment_id', $rec->rc_id)
                ->where('jobber_id', $jobberId)
                ->exists();

            $hasInvite = ProviderCandidateInvite::query()
                ->where('recruitment_id', $rec->rc_id)
                ->whereHas('resume', function ($query) use ($jobberId) {
                    $query->where('user_id', $jobberId);
                })
                ->exists();

            if (!$hasApplication && !$hasInvite) {
                abort(404);
            }
        }

        $company = DB::table('companies_profiles')->where('co_user_id', $rec->rc_u_id)->first();

        try { $rec->increment('rc_views'); } catch (\Throwable $e) {}

        return view('jobber.recruitments.show', compact('rec', 'company', 'matching'));
    }

    /** Public: ดูรายละเอียดงาน (เปิดสำหรับผู้ที่ยังไม่ล็อกอิน) */
    public function publicShow($rcId)
    {
        $rec = Recruitment::open()
            ->with([
                'recruitmentSkills.skillGroup',
                'recruitmentSkills.skill',
                'skills',
                'languages',
            ])
            ->findOrFail($rcId);

        $company = DB::table('companies_profiles')->where('co_user_id', $rec->rc_u_id)->first();

        $matching = app(JobMatchingService::class)->scoreSingleRecruitment($rec, null);

        try { $rec->increment('rc_views'); } catch (\Throwable $e) {}

        return view('jobber.recruitments.show', compact('rec', 'company', 'matching'));
    }
    
    /** Admin: รายการงานของ provider คนที่ระบุ */
    public function adminIndex(Request $request, $userId)
    {
        // guard ง่าย ๆ
        if (Auth::user()->role !== 'admin') abort(403);

        // ดึงข้อมูลบริษัท (ถ้ามี) เพื่อโชว์หัวเรื่อง
        $provider = User::with('skills')->find($userId);
        $company  = DB::table('companies_profiles')->where('co_user_id', $userId)->first();

        [$q, $status, $type, $mode] = [
            $request->string('q')->toString(),
            $request->string('status')->toString(),
            $request->string('type')->toString(),
            $request->string('work_mode')->toString(),
        ];

        $today = now()->toDateString();

        $recs = Recruitment::ownedBy($userId)
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('rc_title', 'ilike', "%{$q}%")
                      ->orWhere('rc_description', 'ilike', "%{$q}%")
                      ->orWhere('rc_requirements', 'ilike', "%{$q}%");
                });
            })
            ->when($status === 'open', function ($qq) use ($today) {
                $qq->where('rc_status', 'open')
                   ->where(function ($w) use ($today) {
                       $w->whereNull('rc_expire_at')
                         ->orWhereDate('rc_expire_at', '>=', $today);
                   });
            })
            ->when($status === 'inactive', function ($qq) use ($today) {
                $qq->where(function ($w) use ($today) {
                    $w->where('rc_status', 'closed')
                      ->orWhereDate('rc_expire_at', '<', $today);
                });
            })
            ->when($type, fn($qq) => $this->applyTypeFilter($qq, $type))
            ->when($mode, fn($qq) => $this->applyWorkModeFilter($qq, $mode))
            ->orderByDesc('rc_posted_at')
            ->paginate(10)
            ->withQueryString();

        // Auto-mark expired postings with closed status
        foreach ($recs->items() as $rec) {
            if ($rec->rc_expire_at && \Illuminate\Support\Carbon::parse($rec->rc_expire_at)->endOfDay()->isPast()) {
                if ($rec->rc_status !== 'closed') {
                    $rec->update(['rc_status' => 'closed']);
                }
            }
        }

        return view('admin.recruitments.index', [
            'isAdmin'  => true,
            'ownerId'  => (int) $userId,
            'provider' => $provider,
            'company'  => $company,
            'recs'     => $recs,
            'filters'  => [
                'q' => $q,
                'status' => $status,
                'type' => $type,
                'work_mode' => $mode,
            ],
        ]);
    }

    /** Provider: รายการงานของตัวเอง */
    public function providerIndex(Request $request)
    {
        if (Auth::user()->role !== 'provider') abort(403);

        $userId = Auth::id();
        $company = CompaniesProfile::with('user')->first();

        [$q, $status, $type, $mode] = [
            $request->string('q')->toString(),
            $request->string('status')->toString(),
            $request->string('type')->toString(),
            $request->string('work_mode')->toString(),
        ];

        $today = now()->toDateString();

        $recs = Recruitment::ownedBy($userId)
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('rc_title', 'ilike', "%{$q}%")
                      ->orWhere('rc_description', 'ilike', "%{$q}%")
                      ->orWhere('rc_requirements', 'ilike', "%{$q}%");
                });
            })
            ->when($status === 'open', function ($qq) use ($today) {
                $qq->where('rc_status', 'open')
                   ->where(function ($w) use ($today) {
                       $w->whereNull('rc_expire_at')
                         ->orWhereDate('rc_expire_at', '>=', $today);
                   });
            })
            ->when($status === 'inactive', function ($qq) use ($today) {
                $qq->where(function ($w) use ($today) {
                    $w->where('rc_status', 'closed')
                      ->orWhereDate('rc_expire_at', '<', $today);
                });
            })
            ->when($type, fn($qq) => $this->applyTypeFilter($qq, $type))
            ->when($mode, fn($qq) => $this->applyWorkModeFilter($qq, $mode))
            ->orderByDesc('rc_posted_at')
            ->paginate(10)
            ->withQueryString();

        // Auto-mark expired postings with closed status
        foreach ($recs->items() as $rec) {
            if ($rec->rc_expire_at && \Illuminate\Support\Carbon::parse($rec->rc_expire_at)->endOfDay()->isPast()) {
                if ($rec->rc_status !== 'closed') {
                    $rec->update(['rc_status' => 'closed']);
                }
            }
        }

        return view('admin.recruitments.index', [ // reuse view เดิม
            'isAdmin'  => false,
            'ownerId'  => $userId,
            'provider' => Auth::user(),
            'company'  => $company,
            'recs'     => $recs,
            'filters'  => [
                'q' => $q,
                'status' => $status,
                'type' => $type,
                'work_mode' => $mode,
            ],
        ]);
    }

    /** ทั้ง Admin/Provider ใช้ร่วมกัน */
    public function edit($rcId)
    {
        $rec = Recruitment::with('skills', 'languages')->findOrFail($rcId);

        $user = Auth::user();
        $isOwner = $rec->rc_u_id === $user->id;

        if (!($user->role === 'admin' || ($user->role === 'provider' && $isOwner))) {
            abort(403);
        }

        $skillGroups = MasterSkillGroup::query()
            ->whereHas('skills')
            ->with(['skills' => function ($query) {
                $query->orderByRaw('LOWER(name)');
            }])
            ->orderByRaw('LOWER(name)')
            ->get();

        return view('admin.recruitments.edit', [
            'rec' => $rec,
            'isAdmin' => $user->role === 'admin',
            'skillGroups' => $skillGroups,
        ]);
    }

    /** Admin/Provider: ดูรายละเอียดประกาศงานที่จัดการได้ (รวมหมดอายุ/ปิดรับ) */
    public function manageShow($rcId)
    {
        $rec = Recruitment::with([
            'recruitmentSkills.skillGroup',
            'recruitmentSkills.skill',
            'skills',
            'languages',
        ])->findOrFail($rcId);

        $user = Auth::user();
        $isOwner = $rec->rc_u_id === $user->id;
        if (!($user->role === 'admin' || ($user->role === 'provider' && $isOwner))) {
            abort(403);
        }

        $company = DB::table('companies_profiles')->where('co_user_id', $rec->rc_u_id)->first();

        return view('jobber.recruitments.show', compact('rec', 'company'));
    }

    public function update(Request $request, $rcId)
    {
        $rec = Recruitment::findOrFail($rcId);

        $user = Auth::user();
        $isOwner = $rec->rc_u_id === $user->id;
        if (!($user->role === 'admin' || ($user->role === 'provider' && $isOwner))) {
            abort(403);
        }

        $data = $this->validatedRecruitment($request);

        // ปรับวันที่เปิดรับสมัครอัตโนมัติทุกครั้งที่แก้ไข
        $data['rc_posted_at'] = now();

        // 🔴 ครอบทุกอย่างด้วย transaction
        DB::transaction(function () use ($rec, $data, $request) {

            $rec->fill($data)->save();

            $this->syncRecruitmentRelations($rec, $request);
        });

        // กลับไป list ให้ถูกฝั่ง
        if ($user->role === 'admin') {
            return redirect()
                ->route('admin.providers.recruitments.index', $rec->rc_u_id)
                ->with('swal_qr', ['rc_id' => $rec->rc_id, 'rc_title' => $rec->rc_title]);
        }

        return redirect()
            ->route('provider.recruitments.index')
            ->with('swal_qr', ['rc_id' => $rec->rc_id, 'rc_title' => $rec->rc_title]);
    }

    /** เปลี่ยนสถานะประกาศงานเป็น เปิดรับ(open) หรือ ปิดรับ(closed) */
    public function updateStatus(Request $request, $rcId)
    {
        $rec = Recruitment::findOrFail($rcId);

        $user = Auth::user();
        $isOwner = $rec->rc_u_id === $user->id;
        if (!($user->role === 'admin' || ($user->role === 'provider' && $isOwner))) {
            abort(403);
        }

        // ถ้ากำหนดวันปิดรับสมัครไว้ ห้ามเปลี่ยนสถานะ (เฉพาะเปิดรับตลอดเท่านั้นที่กดเปลี่ยนได้)
        if ($rec->rc_expire_at) {
            return back()->with('error', 'ไม่สามารถเปลี่ยนสถานะได้เนื่องจากประกาศงานมีกำหนดวันปิดรับสมัครแล้ว');
        }

        $to = $request->string('to')->toString();
        if (!in_array($to, ['open', 'closed'], true)) {
            return back()->with('error', 'ค่าสถานะไม่ถูกต้อง');
        }

        $rec->rc_status = $to;
        if ($to === 'open' && empty($rec->rc_posted_at)) {
            $rec->rc_posted_at = now();
        }
        $rec->save();

        return back()->with('status', $to === 'open' ? 'เปิดรับประกาศงานแล้ว' : 'ปิดรับประกาศงานแล้ว');
    }
    public function createForAdmin($userId)
{
    if (Auth::user()->role !== 'admin') abort(403);

    // ดึงข้อมูลเบื้องต้นไว้โชว์หัวเรื่อง
    $provider = DB::table('users')->where('id', $userId)->first();
    $company  = DB::table('companies_profiles')->where('co_user_id', $userId)->first();
    // ⭐ เพิ่มการกรองเฉพาะกลุ่มที่มี skills
    $skillGroups = MasterSkillGroup::query()
        ->whereHas('skills')
        ->with(['skills' => function ($query) {
            $query->orderByRaw('LOWER(name)');
        }])
        ->orderByRaw('LOWER(name)')
        ->get();

    return view('admin.recruitments.create', [
        'isAdmin'  => true,
        'ownerId'  => (int) $userId,
        'provider' => $provider,
        'company'  => $company,
        'skillGroups'  => $skillGroups,
    ]);
}

public function storeForAdmin(Request $request, $userId)
{
    if (Auth::user()->role !== 'admin') abort(403);

    $data = $this->validatedRecruitment($request);
    $data['rc_u_id'] = (int) $userId;

    $data['rc_posted_at'] = now();
    $data['rc_status'] = 'open';
    $data['rc_description'] = $data['rc_description'] ?? '';

    $rec = Recruitment::create($data);

    $this->syncRecruitmentRelations($rec, $request);

    return redirect()
        ->route('admin.providers.recruitments.index', $userId)
        ->with('swal_qr', ['rc_id' => $rec->rc_id, 'rc_title' => $rec->rc_title]);
}

public function createForProvider()
{
    if (Auth::user()->role !== 'provider') abort(403);

    $userId  = Auth::id();
    $company = DB::table('companies_profiles')->where('co_user_id', $userId)->first();
    // ⭐ เพิ่มการกรองเฉพาะกลุ่มที่มี skills
    $skillGroups = MasterSkillGroup::query()
        ->whereHas('skills')
        ->with(['skills' => function ($query) {
            $query->orderByRaw('LOWER(name)');
        }])
        ->orderByRaw('LOWER(name)')
        ->get();

    return view('admin.recruitments.create', [
        'isAdmin'  => false,
        'ownerId'  => $userId,
        'provider' => Auth::user(),
        'company'  => $company,
        'skillGroups'  => $skillGroups,
    ]);
}

    public function storeForProvider(Request $request)
    {
        if (Auth::user()->role !== 'provider') abort(403);

    $data = $this->validatedRecruitment($request);
    $data['rc_u_id'] = Auth::id();

    $data['rc_posted_at'] = now();
    $data['rc_status'] = 'open';
    $data['rc_description'] = $data['rc_description'] ?? '';

    $rec = Recruitment::create($data);

    $this->syncRecruitmentRelations($rec, $request);

        return redirect()
            ->route('provider.recruitments.index')
            ->with('swal_qr', ['rc_id' => $rec->rc_id, 'rc_title' => $rec->rc_title]);
    }

/** ----- แชร์ rules ระหว่าง create/update ----- */
    private function validatedRecruitment(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'rc_title'           => ['required', 'string', 'max:255'],
            'rc_description'     => ['nullable', 'string'],
            'rc_requirements'    => ['nullable', 'string'],
            'rc_gender'          => ['nullable', 'in:unspecified,any,male,female'],
            'rc_education_level' => ['nullable', 'in:unspecified,any,below_bachelor,bachelor,master,doctorate'],
            'rc_experience_level'=> ['nullable', 'in:unspecified,no_experience,0_1,1_3,3_5,more_5'],
            'rc_salary'          => ['nullable', 'string', 'max:255'],

            'rc_location_text'   => ['nullable', 'string', 'max:255'],
            'rc_location_link'   => ['nullable', 'url', 'max:2048'],

            'rc_type'            => ['nullable', 'in:full-time,part-time,intern,freelance'],
            'rc_work_mode'       => ['nullable', 'in:onsite,remote,hybrid,distributed'],
            'rc_expire_at'       => ['nullable', 'date', 'after:today'],
            'expire_no_limit'    => ['nullable', 'boolean'],

            'skills' => ['nullable', 'array'],
            'skills.*.skill_group_id' => ['required', 'exists:master_skill_groups,id'],
            'skills.*.skill_id' => ['required', 'exists:master_skills,id'],
            'skills.*.proficiency_level' => ['required', 'in:beginner,intermediate,advanced,expert'],
            'skills.*.is_required' => ['nullable', 'boolean'],

            'languages' => ['nullable', 'array'],
            'languages.*.language' => ['required', 'string', 'max:255'],
            'languages.*.proficiency' => ['required', 'in:basic,conversational,fluent,native'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $selectedMode = trim((string) $request->input('rc_work_mode', ''));
            $requiresLocation = in_array($selectedMode, ['onsite', 'hybrid'], true);
            $locationLink = trim((string) $request->input('rc_location_link', ''));

            if ($requiresLocation) {
                if (!filled($request->input('rc_location_text'))) {
                    $validator->errors()->add('rc_location_text', 'กรุณาระบุสถานที่เมื่อเลือกเข้าออฟฟิศหรือผสมผสาน');
                }

                if (!filled($request->input('rc_location_link'))) {
                    $validator->errors()->add('rc_location_link', 'กรุณาระบุลิงก์สถานที่เมื่อเลือกเข้าออฟฟิศหรือผสมผสาน');
                }
            }

        });

        $data = $validator->validate();

        $isNoLimitExpire = (string) $request->input('expire_no_limit', '0') === '1';
        if ($isNoLimitExpire) {
            $data['rc_expire_at'] = null;
        }

        $data['rc_type'] = filled($data['rc_type'] ?? null) ? trim((string) $data['rc_type']) : null;
        $data['rc_work_mode'] = filled($data['rc_work_mode'] ?? null) ? trim((string) $data['rc_work_mode']) : null;
        $data['rc_gender'] = filled($data['rc_gender'] ?? null) ? trim((string) $data['rc_gender']) : 'unspecified';
        $data['rc_education_level'] = filled($data['rc_education_level'] ?? null) ? trim((string) $data['rc_education_level']) : 'unspecified';
        $data['rc_experience_level'] = filled($data['rc_experience_level'] ?? null) ? trim((string) $data['rc_experience_level']) : 'unspecified';

        $requiresLocation = in_array($data['rc_work_mode'] ?? null, ['onsite', 'hybrid'], true);

        if (!$requiresLocation) {
            $data['rc_location_text'] = null;
            $data['rc_location_link'] = null;
        }

        return $data;
    }

    private function applyTypeFilter($query, string $type)
    {
        return $query->whereRaw("CONCAT(',', COALESCE(rc_type, ''), ',') LIKE ?", ["%,{$type},%"]);
    }

    private function applyWorkModeFilter($query, string $mode)
    {
        return $query->whereRaw("CONCAT(',', COALESCE(rc_work_mode, ''), ',') LIKE ?", ["%,{$mode},%"]);
    }

    private function syncRecruitmentRelations(Recruitment $rec, Request $request): void
    {
        $skills = collect($request->input('skills', []))
            ->filter(fn ($skill) => !empty($skill['skill_group_id'] ?? null) || !empty($skill['skill_id'] ?? null));

        RecruitmentSkill::where('rc_id', $rec->rc_id)->delete();

        foreach ($skills as $skill) {
            RecruitmentSkill::create([
                'rc_id' => $rec->rc_id,
                'master_skill_group_id' => $skill['skill_group_id'],
                'master_skill_id' => $skill['skill_id'],
                'proficiency_level' => $skill['proficiency_level'],
                'is_required' => (string) ($skill['is_required'] ?? '1') === '1',
            ]);
        }

        if (method_exists($rec, 'languages')) {
            $rec->languages()->delete();
        }

        $languages = collect($request->input('languages', []))
            ->filter(fn ($language) => !empty($language['language'] ?? null));

        foreach ($languages as $language) {
            $rec->languages()->create([
                'language' => $language['language'],
                'proficiency' => $language['proficiency'] ?? 'basic',
            ]);
        }
    }

    /** ลบประกาศงาน (ทั้ง Admin/Provider ใช้ร่วมกัน) */
    public function destroy($rcId)
    {
        $rec = Recruitment::findOrFail($rcId);

        $user = Auth::user();
        $isOwner = $rec->rc_u_id === $user->id;

        if (!($user->role === 'admin' || ($user->role === 'provider' && $isOwner))) {
            abort(403);
        }

        $ownerId = $rec->rc_u_id;
        $rec->delete();

        if ($user->role === 'admin') {
            return redirect()
                ->route('admin.providers.recruitments.index', $ownerId)
                ->with('status', 'ลบประกาศงานเรียบร้อย');
        }

        return redirect()
            ->route('provider.recruitments.index')
            ->with('status', 'ลบประกาศงานเรียบร้อย');
    }

}
