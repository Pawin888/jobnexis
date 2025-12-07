<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('welcome', [
            'showAuthModal' => true,
            'authForm' => 'reset',
            'passwordResetToken' => $request->token,
            'passwordResetEmail' => $request->email,
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => 'ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้อง',

            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.exists' => 'ไม่พบบัญชีผู้ใช้นี้ในระบบ',

            'password.required' => 'กรุณากรอกรหัสผ่านใหม่',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย :min ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect('/')->with([
                'showAuthModal' => true,
                'authForm' => 'login',
                'status' => 'password-updated'
            ]);
        }

        return back()->with([
            'showAuthModal' => true,
            'authForm' => 'reset',
            'passwordResetToken' => $request->token,
            'passwordResetEmail' => $request->email
        ])->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
        // return back()->withInput($request->only('email'))
        //     ->withErrors(['email' => __($status)])
        //     ->with([
        //         'showAuthModal' => true,
        //         'authForm' => 'reset'
        //     ])->withInput();
    }
}
