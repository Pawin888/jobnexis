<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($this->redirectByRole($user) . '?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($this->redirectByRole($user) . '?verified=1');
    }

    private function redirectByRole($user)
    {
        return match ($user->role) {
            // หลังยืนยันอีเมล พาไปหน้าแก้ไขโปรไฟล์ตาม role
            'jobber'     => route('profile-jobber.edit'),
            'provider'   => route('provider.profile.edit'),
            'education'  => route('profile-education.edit.self'),
            // แอดมินพาไปหน้ารวมผู้ประกอบการ (ปรับได้ตามต้องการ)
            'admin'      => route('admin.userStats'),
            default      => route('profile-jobber.edit'),
        };
    }
}
