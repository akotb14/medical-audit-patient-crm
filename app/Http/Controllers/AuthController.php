<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {

            RateLimiter::clear($request->throttleKey());

            $request->session()->regenerate();

            $user = Auth::user();

            return $user->hasRole('Admin')
                ? redirect()->route('dashboard.index')
                : redirect()->route('clinics.index');
        }

        RateLimiter::hit($request->throttleKey(), 60);
        RateLimiter::hit($request->ipThrottleKey(), 60);

        return back()
            ->withErrors([
                'username' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
            ])
            ->onlyInput('username');
    }
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'تم تسجيل الخروج بنجاح.');
    }
}
