<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255'
                ],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:users,email'
                ],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ],'role' => ['required', 'in:jobber,provider,education'],
            ], [
                // email
                'email.required' => 'กรุณากรอกอีเมล',
                'email.string' => 'อีเมลต้องเป็นข้อความ',
                'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
                'email.max' => 'อีเมลต้องไม่เกิน :max ตัวอักษร',
                'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',

                // password
                'password.required' => 'กรุณากรอกรหัสผ่าน',
                'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
                'password.min' => 'รหัสผ่านต้องมีอย่างน้อย :min ตัวอักษร',
                'password.letters' => 'รหัสผ่านต้องมีตัวอักษรอย่างน้อย 1 ตัว',
                'password.mixed' => 'รหัสผ่านต้องมีทั้งตัวพิมพ์ใหญ่และตัวพิมพ์เล็ก',
                'password.numbers' => 'รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว',
                'password.symbols' => 'รหัสผ่านต้องมีอักขระพิเศษอย่างน้อย 1 ตัว',
            ]);

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Create corresponding profile based on role
            switch ($request->role) {
                case 'jobber':
                    \App\Models\UserProfile::create([
                        'up_u_id' => $user->id,
                        'up_name' => $request->name,
                    ]);
                    break;
                case 'provider':
                    \App\Models\CompaniesProfile::create([
                        'co_user_id' => $user->id,
                        'co_name'    => $request->name,
                        'co_email'   => $request->email,
                    ]);
                    break;
                case 'education':
                    \App\Models\EducationProfile::create([
                        'e_u_id'  => $user->id,
                        'e_name'  => $request->name,
                        'e_email' => $request->email,
                    ]);
                    break;
            }

            event(new Registered($user));

            Auth::login($user);

            return redirect('/')->with([
                'showAuthModal' => true,
                'authForm' => 'verify'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // กลับไปหน้าเดิมพร้อม error และเปิด register modal
            return back()->withErrors($e->errors())->with([
                'showAuthModal' => true,
                'authForm' => 'register'
            ])->withInput();
        }
    }
}
