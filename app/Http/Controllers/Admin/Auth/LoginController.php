<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Facades\System;
use App\Http\Controllers\Controller;
use App\Services\AdminOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showForgotPasswordForm()
    {
        $title = 'Reset Password';

        return view('admin.auth.forgot-password', compact('title'));
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
        ]);

        $status = Password::broker('admins')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:admins,email',
            'password' => 'required|confirmed',
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin, $password) {
                $admin->password = Hash::make($password);
                $admin->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }


    public function showSetPasswordForm(Request $request)
    {
        return view('admin.auth.set-password', ['token' => $request->token, 'email' => $request->email]);
    }

    public function showLoginForm()
    {
        $title = 'Admin Login';

        return view('admin.auth.login', compact('title'));
    }

    public function login(Request $request, AdminOtpService $otpService)
    {
        $this->ensureIsNotRateLimited($request);

        $rules = [
            'username' => 'required|string',
            'password' => 'required|string',
        ];

        if (System::googleCaptchaEnabled()) {
            $rules['g-recaptcha-response'] = ['required', 'captcha'];
        }

        $credentials = $request->validate($rules);

        // validate() checks the credentials WITHOUT establishing a session —
        // the admin is only "password verified", not authenticated yet. A
        // second factor (OTP) must clear before Auth::guard('admin')->login()
        // is ever called, which is what actually grants dashboard access.
        if (
            Auth::guard('admin')->validate(
                ['username' => $credentials['username'], 'password' => $credentials['password']]
            )
        ) {
            RateLimiter::clear($this->throttleKey($request));

            $admin = Auth::guard('admin')->getLastAttempted();

            $result = $otpService->issueChallenge($admin, $request->ip());

            if (!$result['success']) {
                return back()->withErrors([
                    'username' => __('OTP পাঠানো যায়নি। কিছুক্ষণ পর আবার চেষ্টা করুন।'),
                ])->onlyInput('username');
            }

            $request->session()->put('admin_otp_challenge_id', $result['challenge']->id);
            $request->session()->put('admin_otp_remember', $request->boolean('remember'));

            return redirect()->route('admin.login.otp');
        }

        RateLimiter::hit($this->throttleKey($request), 60); // Lockout time in seconds

        throw ValidationException::withMessages([
            'username' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('username')) . '|' . $request->ip();
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        throw ValidationException::withMessages([
            'username' => __('Too many login attempts. Please try again in :seconds seconds.', [
                'seconds' => RateLimiter::availableIn($this->throttleKey($request)),
            ]),
        ]);
    }
}
