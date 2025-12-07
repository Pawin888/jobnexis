<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserProfile;
use App\Models\Education;
use App\Models\WorkExperience;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminEditedYourData;
use App\Mail\AccountStatusChanged;
use Throwable;

class ProfileDetailController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        // ดึงข้อมูลจาก DB (เฉพาะ role = jobber)
        $pagedData = User::with('profile')
            ->where('role', 'jobber')
            ->paginate(7); // ยังคงใช้ paginate

        return view('admin.jobber', compact('pagedData'));
    }

    public function edit($userId = null)
    {
        try {
            $user = Auth::user();

            // admin ต้องระบุ userId เสมอ
            if ($user->role === 'admin') {
                if (empty($userId)) {
                    abort(400, 'ต้องระบุ userId สำหรับผู้ดูแลระบบ');
                }
                $targetUserId = $userId;
            } else {
                $targetUserId = $user->id; // jobber = ตัวเองเท่านั้น
            }
            $provinces = config('th_provinces', []);
            $profile      = UserProfile::where('up_u_id', $targetUserId)->first();
            $educations   = Education::where('ed_u_id', $targetUserId)->get();
            $works        = WorkExperience::where('we_u_id', $targetUserId)->get();
            $certificates = Certificate::where('cer_u_id', $targetUserId)->get();

            return view('admin.edit-jobber', compact('profile', 'educations', 'works', 'targetUserId', 'certificates', 'provinces'));
        } catch (Throwable $e) {
            Log::error('Edit profile failed', [
                'action' => 'edit',
                'targetUserId' => $userId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'provinces' => $provinces,
            ]);

            return back()->withErrors(['edit' => 'ไม่สามารถโหลดข้อมูลได้ โปรดลองใหม่อีกครั้ง'])->withInput();
        }
    }


    public function store(Request $request, $userId = null)
    {
        try {
            $user = Auth::user();

            // admin ต้องระบุ userId เสมอ
            if ($user->role === 'admin') {
                if (empty($userId)) {
                    abort(400, 'ต้องระบุ userId สำหรับผู้ดูแลระบบ');
                }
                $targetUserId = $userId;
            } else {
                $targetUserId = $user->id; // jobber = ตัวเองเท่านั้น
            }

            // Validate date-related fields before processing
            $request->validate([
                'up_birth_date' => 'nullable|date|before_or_equal:today',

                'educations' => 'sometimes|array',
                'educations.*.ed_name' => 'nullable|string|max:255',
                'educations.*.ed_start_date' => 'nullable|date',
                'educations.*.ed_end_date' => 'nullable|date|after_or_equal:educations.*.ed_start_date',

                'work_experiences' => 'sometimes|array',
                'work_experiences.*.we_company_name' => 'nullable|string|max:255',
                'work_experiences.*.we_start_date' => 'nullable|date',
                'work_experiences.*.we_end_date' => 'nullable|date|after_or_equal:work_experiences.*.we_start_date',
            ], [
                'up_birth_date.before_or_equal' => 'วันเกิดต้องไม่เกินวันที่ปัจจุบัน',
                'educations.*.ed_end_date.after_or_equal' => 'วันที่สิ้นสุดการศึกษาต้องไม่ก่อนวันที่เริ่ม',
                'work_experiences.*.we_end_date.after_or_equal' => 'วันที่สิ้นสุดการทำงานต้องไม่ก่อนวันที่เริ่ม',
            ]);

            DB::beginTransaction();

            // ========== 1) เก็บข้อมูลโปรไฟล์ ==========
            UserProfile::updateOrCreate(
                ['up_u_id' => $targetUserId],
                [
                    'up_prefix'     => $request->input('up_prefix'),
                    'up_name'       => $request->input('up_name'),
                    'up_phone'      => $request->input('up_phone'),
                    'up_birth_date' => $request->input('up_birth_date'),
                    'up_gender'     => $request->input('up_gender'),
                    'up_city'       => $request->input('up_city'),
                ]
            );

            // ========== 2) เก็บการศึกษา ==========
            $keepEduIds = [];
            if ($request->has('educations')) {
                foreach ($request->input('educations', []) as $edu) {
                    // ป้องกัน index ที่ว่าง/ไม่มีชื่อ
                    if (empty($edu['ed_name']) || empty($edu['ed_start_date'])) {
                        continue;
                    }

                    $education = Education::updateOrCreate(
                        ['ed_id' => $edu['ed_id'] ?? null],
                        [
                            'ed_name'       => $edu['ed_name'],
                            'ed_start_date' => $edu['ed_start_date'],
                            'ed_end_date'   => $edu['ed_end_date'] ?? null,
                            'ed_u_id'       => $targetUserId,
                        ]
                    );

                    $keepEduIds[] = $education->ed_id;
                }
            }

            Education::where('ed_u_id', $targetUserId)
                ->when(!empty($keepEduIds), fn($q) => $q->whereNotIn('ed_id', $keepEduIds))
                ->when(empty($keepEduIds), fn($q) => $q) // ลบทั้งหมดถ้าไม่มีเหลือ
                ->delete();

            // ========== 3) เก็บประสบการณ์ทำงาน ==========
            $keepWorkIds = [];
            if ($request->has('work_experiences')) {
                foreach ($request->input('work_experiences', []) as $work) {
                    if (empty($work['we_company_name']) || empty($work['we_start_date'])) {
                        continue;
                    }

                    $experience = WorkExperience::updateOrCreate(
                        ['we_id' => $work['we_id'] ?? null],
                        [
                            'we_company_name' => $work['we_company_name'],
                            'we_start_date'   => $work['we_start_date'],
                            'we_end_date'     => $work['we_end_date'] ?? null,
                            'we_u_id'         => $targetUserId,
                        ]
                    );

                    $keepWorkIds[] = $experience->we_id;
                }
            }

            WorkExperience::where('we_u_id', $targetUserId)
                ->when(!empty($keepWorkIds), fn($q) => $q->whereNotIn('we_id', $keepWorkIds))
                ->when(empty($keepWorkIds), fn($q) => $q) // ลบทั้งหมดถ้าไม่มีเหลือ
                ->delete();

            DB::commit();

            // If admin edited someone else's profile, notify that user
            if ($user->role === 'admin' && (int)$targetUserId !== (int)$user->id) {
                try {
                    $target = User::find($targetUserId);
                    if ($target) {
                        Mail::to($target->email)->send(new AdminEditedYourData('โปรไฟล์ผู้สมัครงาน (Jobber)', $user->email));
                    }
                } catch (Throwable $e) {}
            }

            return redirect()->back()->with('success', 'บันทึกข้อมูลเรียบร้อย');
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Store profile failed', [
                'action' => 'store',
                'targetUserId' => $userId,
                'request' => $request->except(['password', 'password_confirmation']),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->withErrors([
                'store' => 'บันทึกไม่สำเร็จ กรุณาลองใหม่อีกครั้ง (' . $e->getMessage() . ')'
            ])->withInput();
        }
    }

    public function destroyEducation($id)
    {
        try {
            $education = Education::findOrFail($id);
            $user = Auth::user();

            // เช็คสิทธิ์
            if ($user->role !== 'admin' && (int)$education->ed_u_id !== (int)$user->id) {
                return response()->json(['error' => 'ไม่สามารถลบข้อมูลนี้ได้'], 403);
            }

            $education->delete();
            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            Log::error('Delete education failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function destroyWork($id)
    {
        try {
            $work = WorkExperience::findOrFail($id);
            $user = Auth::user();

            if ($user->role !== 'admin' && (int)$work->we_u_id !== (int)$user->id) {
                return response()->json(['error' => 'ไม่สามารถลบข้อมูลนี้ได้'], 403);
            }

            $work->delete();
            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            Log::error('Delete work failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    public function toggleBan(User $user)
    {
        $auth = Auth::user();
        if ($auth->role !== 'admin') abort(403);
        if ($user->role !== 'jobber') abort(404);
        $user->is_banned = !$user->is_banned;
        $user->save();

        // notify user of status change
        try {
            $status = $user->is_banned ? 'แบน' : 'ใช้งานได้';
            Mail::to($user->email)
                ->send(new AccountStatusChanged($status));
        } catch (Throwable $e) {}

        return back()->with('success', $user->is_banned ? 'แบนผู้ใช้แล้ว' : 'ปลดแบนผู้ใช้แล้ว');
    }

    public function destroy(User $user)
    {
        $auth = Auth::user();
        if ($auth->role !== 'admin') abort(403);
        if ($user->role !== 'jobber') abort(404);


        // จะ Soft Delete หรือ Hard Delete ขึ้นกับ Model User ของคุณ
        // ถ้าไม่ได้ใช้ SoftDeletes นี่จะเป็นการลบถาวร และ FK ที่ onDelete('cascade') จะจัดการโปรไฟล์ให้
        $user->delete();

        return redirect()->route('admin.jobber.index')->with('success', 'ลบผู้ใช้เรียบร้อย');
    }
}
