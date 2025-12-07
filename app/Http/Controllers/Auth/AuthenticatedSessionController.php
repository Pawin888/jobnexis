<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('home')->with([
            'showAuthModal' => true,
            'authForm' => 'login',
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user && $user->email_verified_at) {
                if ($user->role === 'admin') {
                    return redirect('/admin/dashboard');
                } elseif ($user->role === 'education') {
                    return redirect('/education/dashboard');
                } elseif ($user->role === 'provider') {
                    return redirect('/provider/dashboard');
                } else {
                    return redirect('/');
                }
            }

            return redirect('/')->with([
                'showAuthModal' => true,
                'authForm' => 'verify'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // กลับไปหน้าเดิมพร้อม error และเปิด login modal
            return back()->withErrors($e->errors())->with([
                'showAuthModal' => true,
                'authForm' => 'login'
            ])->onlyInput('email');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
