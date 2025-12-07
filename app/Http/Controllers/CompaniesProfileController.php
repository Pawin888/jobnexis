<?php

namespace App\Http\Controllers;

use App\Models\CompaniesProfile;
use App\Models\User;
use App\Models\Recruitment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminEditedYourData;
use App\Mail\AccountStatusChanged;

class CompaniesProfileController extends Controller
{
    public function index(Request $request)
    {
        $providers = User::query()
            ->from('users')
            ->where('users.role', 'provider')
            // join ข้อมูลบริษัท
            ->leftJoin('companies_profiles as cp', 'cp.co_user_id', '=', 'users.id')
            // เลือกคอลัมน์ที่ต้องใช้ (เติม/ลดตามต้องการ)
            ->select([
                'users.id',
                'users.email',
                'users.is_banned',
                'users.email_verified_at',
                'cp.co_name',
                'cp.co_number',
                'cp.co_email as company_email',
                'cp.co_phone',
                'cp.co_profile_img',
                'cp.co_banner_img as co_banner_img',
                'cp.co_type',
                'cp.co_jobber_amount',
                'cp.co_province',
            ])
            // นับจำนวนใบประกาศที่ "เผยแพร่" = เปิดรับงาน
            ->withCount([
                'recruitments as open_recruitments_count' => function ($q) {
                    $q->where('rc_status', 'open');
                },
            ])
            // เรียง: สถานะแบน -> ไอดี
            ->orderBy('users.is_banned') // false ก่อน true (PostgreSQL ok)
            ->orderBy('users.id')        // id น้อยไปมาก
            ->paginate(7)
            ->withQueryString();

        return view('admin.provider', compact('providers'));
    }
    public function toggleBan(User $user)
    {
        // ปรับสถานะแบน
        $user->is_banned = ! $user->is_banned;
        $user->save();

        // notify user of status change
        try {
            $status = $user->is_banned ? 'แบน' : 'ใช้งานได้';
            Mail::to($user->email)->send(new AccountStatusChanged($status));
        } catch (\Throwable $e) {}

        return back()->with('success', $user->is_banned ? 'แบนผู้ใช้เรียบร้อย' : 'ปลดแบนผู้ใช้เรียบร้อย');
    }

    public function destroy(User $user)
    {
        // ถ้าตารางลูก (companies_profiles, recruitments, ฯลฯ) มี FK -> onDelete('cascade')
        // ลบผู้ใช้ได้ตรง ๆ; ถ้าไม่มีก็ควรลบตารางลูกก่อน
        DB::transaction(function () use ($user) {
            // ตัวอย่าง: ถ้าไม่มี cascade ก็ลบเองก่อน (ปล่อยคอมเมนต์ถ้ายังไม่ต้องใช้)
            // DB::table('companies_profiles')->where('co_user_id', $user->id)->delete();
            // DB::table('recruitments')->where('rc_u_id', $user->id)->delete();

            $user->delete();
        });

        return redirect()->route('admin.providers.index')->with('success', 'ลบผู้ใช้เรียบร้อย');
    }
    public function edit(Request $request, $userId = null)
    {
        $auth = Auth::user();

        // กำหนด user เป้าหมาย: admin = userId ที่ส่งมา, provider = ตัวเอง
        $targetUserId = $userId ?? $auth->id;

        // guard: ถ้าไม่ใช่ admin แต่กำลังแก้ที่ไม่ใช่ของตัวเอง -> 403
        if ($auth->role !== 'admin' && $userId) {
            return redirect()->route('provider.profile.edit');
        }
        $provinces = config('th_provinces', []);
        $profile = CompaniesProfile::firstOrNew(['co_user_id' => $targetUserId]);

        return view('admin.edit-provider', [
            'profile'        => $profile,
            'targetUserId'   => $targetUserId,
            'isAdminEditing' => $auth->role === 'admin' && $targetUserId !== $auth->id,
            'provinces' => $provinces,
        ]);
    }

    public function store(Request $request, $userId = null)
    {
        $auth = Auth::user();

        // ดึงให้ครบ: route param > hidden field > auth id
        $effectiveUserId = (int) ($userId ?? $request->input('target_user_id') ?? $auth->id);

        // ความปลอดภัย:
        // - ถ้าไม่ใช่ admin ห้ามแก้ของคนอื่น
        // - provider ต้องแก้ได้เฉพาะของตัวเองเท่านั้น
        if ($auth->role !== 'admin' && $effectiveUserId !== (int) $auth->id) {
            abort(403, 'Forbidden');
        }

        $data = $request->validate([
            'co_name'           => ['required', 'string', 'max:255'],
            'co_email'          => ['nullable', 'email', 'max:255'],
            'co_phone'          => ['nullable', 'string', 'max:50'],
            'co_birthday'       => ['nullable', 'date'],
            'co_type'           => ['nullable', 'string', 'max:100'],
            'co_number'         => ['nullable', 'string', 'max:100'],
            'co_jobber_amount'  => ['nullable', 'integer', 'min:0'],
            'co_address'        => ['nullable', 'string', 'max:1000'],
            'co_province'       => ['nullable', 'string', 'max:255'],
            'co_details'        => ['nullable', 'string', 'max:5000'],
            'co_profile_img'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'co_banner_img'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
        ]);

        $profile = CompaniesProfile::firstOrNew(['co_user_id' => $effectiveUserId]);
        $profile->fill($data);
        $profile->co_user_id = $effectiveUserId;

        if ($request->hasFile('co_profile_img')) {
            $path = $request->file('co_profile_img')->store('companies/profile', 'public');
            $profile->co_profile_img = $path;
        }
        if ($request->hasFile('co_banner_img')) {
            $path = $request->file('co_banner_img')->store('companies/banner', 'public');
            // ถ้า DB ใช้ co_img ให้เซต $profile->co_banner_img = $path; (มี alias ใน Model ตามที่เราทำไว้)
            $profile->co_banner_img = $path;
        }

        $profile->save();

        // If admin edited someone else's profile, notify that user
        if ($auth->role === 'admin' && $effectiveUserId !== (int) $auth->id) {
            try {
                $target = User::find($effectiveUserId);
                if ($target) {
                    Mail::to($target->email)->send(new AdminEditedYourData('โปรไฟล์ผู้ให้บริการ (Provider)', $auth->email));
                }
            } catch (\Throwable $e) {}
        }

        $routeParams = $auth->role === 'admin' ? ['userId' => $effectiveUserId] : [];
        return redirect()
            ->route('provider.profile.edit', $routeParams) // provider => /edit-provider | admin => /edit-provider/{userId}
            ->with('success', 'บันทึกโปรไฟล์เรียบร้อยแล้ว');
    }

    /** Directory: รายชื่อผู้ประกอบการ (สำหรับ Jobber) */
    public function publicIndex(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $province = trim((string) $request->query('province', ''));

        $providers = User::query()
            ->where('role', 'provider')
            ->leftJoin('companies_profiles as cp', 'cp.co_user_id', '=', 'users.id')
            ->select([
                'users.id', 'users.email', 'users.is_banned',
                'cp.co_name', 'cp.co_profile_img', 'cp.co_banner_img', 'cp.co_province', 'cp.co_type',
            ])
            ->withCount(['recruitments as open_jobs' => function ($q) {
                $q->where('rc_status', 'open')
                  ->where(function($w){ $w->whereNull('rc_expire_at')->orWhereDate('rc_expire_at', '>=', now()->toDateString()); });
            }])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('cp.co_name', 'ilike', "%{$q}%")
                      ->orWhere('users.email', 'ilike', "%{$q}%");
                });
            })
            ->when($province, fn($qq) => $qq->where('cp.co_province', $province))
            ->orderByDesc('open_jobs')
            ->orderBy('cp.co_name')
            ->paginate(12)
            ->withQueryString();

        $provinces = array_values(array_unique(array_filter(DB::table('companies_profiles')->pluck('co_province')->toArray())));

        return view('jobber.companies.index', compact('providers', 'q', 'province', 'provinces'));
    }

    /** รายละเอียดผู้ประกอบการ + งานที่เปิดรับ (สำหรับ Jobber) */
    public function publicShow($userId)
    {
        $user = User::where('id', $userId)->where('role', 'provider')->firstOrFail();
        $company = CompaniesProfile::where('co_user_id', $user->id)->first();

        $openJobs = Recruitment::open()->where('rc_u_id', $user->id)
            ->orderByDesc('rc_posted_at')
            ->paginate(6)
            ->withQueryString();

        return view('jobber.companies.show', compact('user', 'company', 'openJobs'));
    }

    /** Guest: รายชื่อผู้ประกอบการ (เปิดสำหรับผู้ที่ยังไม่ล็อกอิน) */
    public function guestIndex(Request $request)
    {
        // ใช้ logic เดียวกับ publicIndex แต่ปล่อย guest
        return $this->publicIndex($request);
    }

    /** Guest: รายละเอียดผู้ประกอบการ (เปิดสำหรับผู้ที่ยังไม่ล็อกอิน) */
    public function guestShow($userId)
    {
        // ใช้ logic เดียวกับ publicShow แต่ปล่อย guest
        return $this->publicShow($userId);
    }
}
