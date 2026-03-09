<?php

namespace App\Http\Controllers;

use App\Models\Recruitment;
use App\Models\RecruitmentSkill;
use App\Models\RecruitmentLanguage;
use App\Models\CompaniesProfile;
use App\Models\MasterSkill;
use App\Models\MasterSkillGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $recs = Recruitment::query()->open()
            ->with('skills')
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('rc_title', 'ilike', "%{$q}%")
                      ->orWhere('rc_description', 'ilike', "%{$q}%")
                      ->orWhere('rc_requirements', 'ilike', "%{$q}%");
                });
            })
            ->when($type, fn($qq) => $qq->where('rc_type', $type))
            ->when($mode, fn($qq) => $qq->where('rc_work_mode', $mode))
            ->orderByDesc('rc_posted_at')
            ->paginate(12)
            ->withQueryString();

        // ดึงข้อมูลบริษัทของเจ้าของประกาศ เพื่อแสดงชื่อ/โลโก้
        $ownerIds = $recs->pluck('rc_u_id')->unique()->values();
        $companies = \Illuminate\Support\Facades\DB::table('companies_profiles')
            ->whereIn('co_user_id', $ownerIds)
            ->get()
            ->keyBy('co_user_id');

        return view('jobber.recruitments.index', [
            'recs' => $recs,
            'companies' => $companies,
            'filters' => [
                'q' => $q,
                'type' => $type,
                'work_mode' => $mode,
            ],
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
            ->when($type, fn($qq) => $qq->where('rc_type', $type))
            ->when($mode, fn($qq) => $qq->where('rc_work_mode', $mode))
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
            'filters' => [
                'q' => $q,
                'type' => $type,
                'work_mode' => $mode,
            ],
        ]);
    }
    
    /** Jobber: ดูรายละเอียดงาน (เฉพาะประกาศเปิดรับ) */
    public function jobberShow($rcId)
    {
        if (!Auth::check() || Auth::user()->role !== 'jobber') abort(403);

        $rec = Recruitment::open()
            ->with([
                'recruitmentSkills.skillGroup',
                'recruitmentSkills.skill',
                'skills',
                'languages',
            ])
            ->findOrFail($rcId);

        $company = DB::table('companies_profiles')->where('co_user_id', $rec->rc_u_id)->first();

        try { $rec->increment('rc_views'); } catch (\Throwable $e) {}

        return view('jobber.recruitments.show', compact('rec', 'company'));
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

        try { $rec->increment('rc_views'); } catch (\Throwable $e) {}

        return view('jobber.recruitments.show', compact('rec', 'company'));
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

        $recs = Recruitment::ownedBy($userId)
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('rc_title', 'ilike', "%{$q}%")
                      ->orWhere('rc_description', 'ilike', "%{$q}%")
                      ->orWhere('rc_requirements', 'ilike', "%{$q}%");
                });
            })
            ->when($status, fn($qq) => $qq->where('rc_status', $status))
            ->when($type, fn($qq) => $qq->where('rc_type', $type))
            ->when($mode, fn($qq) => $qq->where('rc_work_mode', $mode))
            ->orderByDesc('rc_posted_at')
            ->paginate(10)
            ->withQueryString();

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

        $recs = Recruitment::ownedBy($userId)
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('rc_title', 'ilike', "%{$q}%")
                      ->orWhere('rc_description', 'ilike', "%{$q}%")
                      ->orWhere('rc_requirements', 'ilike', "%{$q}%");
                });
            })
            ->when($status, fn($qq) => $qq->where('rc_status', $status))
            ->when($type, fn($qq) => $qq->where('rc_type', $type))
            ->when($mode, fn($qq) => $qq->where('rc_work_mode', $mode))
            ->orderByDesc('rc_posted_at')
            ->paginate(10)
            ->withQueryString();

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

    public function update(Request $request, $rcId)
    {
        $rec = Recruitment::findOrFail($rcId);

        $user = Auth::user();
        $isOwner = $rec->rc_u_id === $user->id;
        if (!($user->role === 'admin' || ($user->role === 'provider' && $isOwner))) {
            abort(403);
        }

        $data = $request->validate([
            'rc_title'           => ['required','string','max:255'],
            'rc_description'     => ['required','string'],
            'rc_requirements'    => ['nullable','string'],
            'rc_salary'          => ['nullable','string','max:255'],
            'rc_location_text'   => ['nullable','string','max:255'],
            'rc_location_link'   => ['nullable','url','max:2048'],
            'rc_type'            => ['required','in:full-time,part-time,intern,freelance'],
            'rc_work_mode'       => ['required','in:onsite,remote,hybrid'],
            'rc_status'          => ['required','in:open,closed,draft'],
            'rc_posted_at'       => ['nullable','date'],
            'rc_expire_at'       => ['nullable','date','after_or_equal:rc_posted_at'],
            'rc_application_url' => ['nullable','url','max:2048'],

            // 🔴 เพิ่ม validation ของ skill
            'skills' => ['sometimes','array'],
            'skills.*.skill_group_id' => ['required','exists:master_skill_groups,id'],
            'skills.*.skill_id' => ['required','exists:master_skills,id'],
            'skills.*.proficiency_level' => ['required','in:beginner,intermediate,advanced,expert'],

            'languages' => ['sometimes','array'],
            'languages.*.language' => ['required','string','max:255'],
            'languages.*.proficiency' => ['required','in:basic,conversational,fluent,native'],
        ]);

        // ถ้าไม่ส่ง posted_at มา ให้คงค่าของเดิม
        if (empty($data['rc_posted_at'])) {
            unset($data['rc_posted_at']);
        }

        // 🔴 ครอบทุกอย่างด้วย transaction
        DB::transaction(function () use ($rec, $data, $request) {

            // อัปเดตข้อมูลประกาศ
            $rec->fill($data)->save();

            // ถ้ามีการส่ง skill มา
            if ($request->filled('skills')) {

                // ลบ skill เดิม
                RecruitmentSkill::where('rc_id', $rec->rc_id)->delete();

                // เพิ่ม skill ใหม่
                foreach ($request->skills as $skill) {
                    RecruitmentSkill::create([
                        'rc_id' => $rec->rc_id,
                        'master_skill_group_id' => $skill['skill_group_id'],
                        'master_skill_id' => $skill['skill_id'],
                        'proficiency_level' => $skill['proficiency_level'],
                    ]);
                }
            }
            
            if ($request->filled('languages')) {
                // ลบภาษาเดิม (เฉพาะ update)
                if (method_exists($rec, 'languages')) {
                    $rec->languages()->delete();
                }
                foreach ($request->languages as $lang) {
                    if (!empty($lang['language'])) {
                        $rec->languages()->create([
                            'language' => $lang['language'],
                            'proficiency' => $lang['proficiency'] ?? 'basic',
                        ]);
                    }
                }
            }
        });

        // กลับไป list ให้ถูกฝั่ง
        if ($user->role === 'admin') {
            return redirect()
                ->route('admin.providers.recruitments.index', $rec->rc_u_id)
                ->with('status', 'อัปเดตประกาศงานเรียบร้อย');
        }

        return redirect()
            ->route('provider.recruitments.index')
            ->with('status', 'อัปเดตประกาศงานเรียบร้อย');
    }

    /** เปลี่ยนสถานะประกาศงานเป็น เผยแพร่(open) หรือ ฉบับร่าง(draft) */
    public function updateStatus(Request $request, $rcId)
    {
        $rec = Recruitment::findOrFail($rcId);

        $user = Auth::user();
        $isOwner = $rec->rc_u_id === $user->id;
        if (!($user->role === 'admin' || ($user->role === 'provider' && $isOwner))) {
            abort(403);
        }

        $to = $request->string('to')->toString();
        if (!in_array($to, ['open', 'draft'], true)) {
            return back()->with('error', 'ค่าสถานะไม่ถูกต้อง');
        }

        $rec->rc_status = $to;
        if ($to === 'open' && empty($rec->rc_posted_at)) {
            $rec->rc_posted_at = now();
        }
        $rec->save();

        return back()->with('status', $to === 'open' ? 'เผยแพร่ประกาศงานแล้ว' : 'บันทึกเป็นฉบับร่างแล้ว');
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
    if (auth::user()->role !== 'admin') abort(403);

    $data = $this->validatedRecruitment($request);
    $data['rc_u_id'] = (int) $userId;

    // ตั้งค่า posted_at เป็นตอนนี้ ถ้าไม่ส่งมา
    if (empty($data['rc_posted_at'])) {
        $data['rc_posted_at'] = now();
    }

    // Override status by quick action buttons
    $quick = $request->string('publish_action')->toString();
    if (in_array($quick, ['open','draft'], true)) {
        $data['rc_status'] = $quick;
    }
    if (($data['rc_status'] ?? null) === 'open' && empty($data['rc_posted_at'])) {
        $data['rc_posted_at'] = now();
    }

    $rec = Recruitment::create($data);

    if ($request->filled('skills')) {
        foreach ($request->skills as $skill) {
            RecruitmentSkill::create([
                'rc_id' => $rec->rc_id,
                'master_skill_group_id' => $skill['skill_group_id'],
                'master_skill_id' => $skill['skill_id'],
                'proficiency_level' => $skill['proficiency_level'],
            ]);
        }
    }

    if ($request->filled('languages')) {
        // ลบภาษาเดิม (เฉพาะ update)
        if (method_exists($rec, 'languages')) {
            $rec->languages()->delete();
        }
        foreach ($request->languages as $lang) {
            if (!empty($lang['language'])) {
                $rec->languages()->create([
                    'language' => $lang['language'],
                    'proficiency' => $lang['proficiency'] ?? 'basic',
                ]);
            }
        }
    }

    return redirect()
        ->route('admin.providers.recruitments.index', $userId)
        ->with('status', 'สร้างประกาศงานเรียบร้อย');
}

public function createForProvider()
{
    if (auth::user()->role !== 'provider') abort(403);

    $userId  = auth::id();
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
        'provider' => auth::user(),
        'company'  => $company,
        'skillGroups'  => $skillGroups,
    ]);
}

    public function storeForProvider(Request $request)
    {
        if (auth::user()->role !== 'provider') abort(403);

    $data = $this->validatedRecruitment($request);
    $data['rc_u_id'] = auth::id();

    if (empty($data['rc_posted_at'])) {
        $data['rc_posted_at'] = now();
    }

    // Override status by quick action buttons
    $quick = $request->string('publish_action')->toString();
    if (in_array($quick, ['open','draft'], true)) {
        $data['rc_status'] = $quick;
    }
    if (($data['rc_status'] ?? null) === 'open' && empty($data['rc_posted_at'])) {
        $data['rc_posted_at'] = now();
    }

    $rec = Recruitment::create($data);

    if ($request->filled('skills')) {
        foreach ($request->skills as $skill) {
            RecruitmentSkill::create([
                'rc_id' => $rec->rc_id,
                'master_skill_group_id' => $skill['skill_group_id'],
                'master_skill_id' => $skill['skill_id'],
                'proficiency_level' => $skill['proficiency_level'],
            ]);
        }
    }

    if ($request->filled('languages')) {
        // ลบภาษาเดิม (เฉพาะ update)
        if (method_exists($rec, 'languages')) {
            $rec->languages()->delete();
        }
        foreach ($request->languages as $lang) {
            if (!empty($lang['language'])) {
                $rec->languages()->create([
                    'language' => $lang['language'],
                    'proficiency' => $lang['proficiency'] ?? 'basic',
                ]);
            }
        }
    }

        return redirect()
            ->route('provider.recruitments.index')
            ->with('status', 'สร้างประกาศงานเรียบร้อย');
    }

/** ----- แชร์ rules ระหว่าง create/update ----- */
    private function validatedRecruitment(Request $request): array
    {
        return $request->validate([
        'rc_title'           => ['required','string','max:255'],
        'rc_description'     => ['required','string'],
        'rc_requirements'    => ['nullable','string'],
        'rc_salary'          => ['nullable','string','max:255'],

        'rc_location_text'   => ['nullable','string','max:255'],
        'rc_location_link'   => ['nullable','url','max:2048'],

        'rc_type'            => ['required','in:full-time,part-time,intern,freelance'],
        'rc_work_mode'       => ['required','in:onsite,remote,hybrid'],
        'rc_status'          => ['required','in:open,closed,draft'],

        // note: datetime-local => 'Y-m-d\TH:i'
        'rc_posted_at'       => ['nullable','date'],
        'rc_expire_at'       => ['nullable','date','after_or_equal:rc_posted_at'],

        'rc_application_url' => ['nullable','url','max:2048'],

        'skills' => ['sometimes','array'],
        'skills.*.skill_group_id' => ['required','exists:master_skill_groups,id'],
        'skills.*.skill_id' => ['required','exists:master_skills,id'],
        'skills.*.proficiency_level' => [
            'required',
            'in:beginner,intermediate,advanced,expert'],
        'skills' => ['sometimes','array', 'distinct:skill_id'],
        
        'languages' => ['sometimes','array'],
        'languages.*.language' => ['required','string','max:255'],
        'languages.*.proficiency' => ['required','in:basic,conversational,fluent,native'],
        
    ]);
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
