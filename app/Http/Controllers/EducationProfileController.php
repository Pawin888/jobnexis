<?php

namespace App\Http\Controllers;

use App\Models\EducationProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationProfileController extends Controller
{
    public function index(Request $request)
    {
        $auth = Auth::user();
        if ($auth->role !== 'admin') {
            abort(403);
        }


        $search = trim((string) $request->query('q', ''));


        $query = User::query()
            ->where('role', 'education')
            ->leftJoin('education_profiles as ep', 'ep.e_u_id', '=', 'users.id')
            ->select([
                'users.id',
                'users.email as user_email',
                'users.is_banned',
                'users.email_verified_at',
                'ep.e_name',
                'ep.e_number',
                'ep.e_phone',
                'ep.e_email as institute_email',
            ])
            ->withCount(['courses as courses_count']);

        $pagedData = $query
            ->orderBy('users.id', 'asc')
            ->orderBy('users.is_banned', 'asc')
            ->paginate(7)
            ->appends($request->only('q'));


        return view('admin.education', compact('pagedData'));
    }
    public function edit(Request $request, $userId = null)
    {
        $auth = Auth::user();
        $targetUserId = $userId ?? $auth->id;


        // Basic authorization: admin can edit anyone, education can edit self only
        if ($auth->role !== 'admin' && $targetUserId != $auth->id) {
            abort(403);
        }


        $profile = EducationProfile::firstOrNew(['e_u_id' => $targetUserId]);


        // Provinces from config (fallback to empty array)
        $provinces = config('th_provinces', []);


        return view('admin.edit-education', [
            'profile' => $profile,
            'targetUserId' => $targetUserId,
            'isAdmin' => $auth->role === 'admin',
            'provinces' => $provinces,
        ]);
    }


    public function store(Request $request, $userId = null)
    {
        $auth = Auth::user();
        $targetUserId = $userId ?? $auth->id;


        if ($auth->role !== 'admin' && $targetUserId != $auth->id) {
            abort(403);
        }


        $data = $request->validate([
            'e_name' => ['required', 'string', 'max:255'],
            'e_phone' => ['nullable', 'string', 'max:30'],
            'e_email' => ['required', 'email', 'max:255'],
            'e_website' => ['nullable', 'url', 'max:255'],
            'e_birthday' => ['nullable', 'date'],
            'e_number' => ['nullable', 'string', 'max:100'],
            'e_address' => ['nullable', 'string', 'max:500'],
            'e_province' => ['nullable', 'string', 'max:100'],
            'e_detail' => ['nullable', 'string', 'max:2000'],
        ]);


        $profile = EducationProfile::firstOrNew(['e_u_id' => $targetUserId]);
        $profile->fill($data);
        $profile->e_u_id = $targetUserId;
        $profile->save();

        // If admin edited someone else's profile, notify that user
        if ($auth->role === 'admin' && (int)$targetUserId !== (int)$auth->id) {
            try {
                $target = \App\Models\User::find($targetUserId);
                if ($target) {
                    \Illuminate\Support\Facades\Mail::to($target->email)
                        ->send(new \App\Mail\AdminEditedYourData('โปรไฟล์สถาบันการศึกษา (Education)', $auth->email));
                }
            } catch (\Throwable $e) {}
        }


        // Redirect back to the correct edit page
        if ($auth->role === 'admin') {
            return redirect()->route('admin.profile-education.edit', ['userId' => $targetUserId])
                ->with('success', 'บันทึกโปรไฟล์ (Education) สำเร็จ');
        }


        return redirect()->route('profile-education.edit.self')
            ->with('success', 'บันทึกโปรไฟล์ (Education) สำเร็จ');
    }
    public function toggleBan(User $user)
    {
        $auth = Auth::user();
        if ($auth->role !== 'admin') abort(403);
        if ($user->role !== 'education') abort(404);

        $user->is_banned = !$user->is_banned;
        $user->save();

        // notify user of status change
        try {
            $status = $user->is_banned ? 'แบน' : 'ใช้งานได้';
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\AccountStatusChanged($status));
        } catch (\Throwable $e) {}

        return back()->with('success', $user->is_banned ? 'แบนผู้ใช้แล้ว' : 'ปลดแบนผู้ใช้แล้ว');
    }

    public function destroy(User $user)
    {
        $auth = Auth::user();
        if ($auth->role !== 'admin') abort(403);
        if ($user->role !== 'education') abort(404);

        // จะ Soft Delete หรือ Hard Delete ขึ้นกับ Model User ของคุณ
        // ถ้าไม่ได้ใช้ SoftDeletes นี่จะเป็นการลบถาวร และ FK ที่ onDelete('cascade') จะจัดการโปรไฟล์ให้
        $user->delete();

        return redirect()->route('admin.educations.index')->with('success', 'ลบผู้ใช้เรียบร้อย');
    }
}
