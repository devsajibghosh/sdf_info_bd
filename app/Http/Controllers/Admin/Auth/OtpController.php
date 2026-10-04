<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminOtpChallenge;
use App\Services\AdminOtpService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class OtpController extends Controller
{
    protected const SESSION_KEY = 'admin_otp_challenge_id';
    protected const SESSION_REMEMBER_KEY = 'admin_otp_remember';

    public function __construct(protected AdminOtpService $otpService)
    {
    }

    public function show(Request $request): View|RedirectResponse
    {
        $challenge = $this->pendingChallenge($request);

        if (!$challenge) {
            return $this->expiredSessionRedirect();
        }

        return view('admin.auth.otp', [
            'title'       => 'Verify OTP',
            'maskedPhone' => $this->otpService->maskPhone($challenge->admin?->phone_number),
            'expiresAt'   => $challenge->expires_at->timestamp,
            'serverNow'   => now()->timestamp,
            'isExpired'   => $challenge->isExpired(),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code' => ['required', 'digits:4'],
        ]);

        $challenge = $this->pendingChallenge($request);

        if (!$challenge) {
            return $this->expiredSessionRedirect();
        }

        return DB::transaction(function () use ($request, $challenge) {
            /** @var AdminOtpChallenge|null $locked */
            $locked = AdminOtpChallenge::whereKey($challenge->id)->lockForUpdate()->first();

            if (!$locked || $locked->isVerified()) {
                return $this->expiredSessionRedirect();
            }

            if ($locked->hasExceededAttempts()) {
                return back()->withErrors([
                    'otp_code' => __('অনেকবার ভুল OTP দেওয়া হয়েছে। নতুন OTP প্রয়োজন।'),
                ]);
            }

            if ($locked->isExpired()) {
                return back()->withErrors([
                    'otp_code' => __('OTP-এর সময় শেষ হয়েছে। নতুন OTP পাঠান।'),
                ]);
            }

            if (!Hash::check($request->input('otp_code'), $locked->otp_hash)) {
                $locked->increment('attempts');

                Log::warning('Admin OTP verification failed', [
                    'admin_id'     => $locked->admin_id,
                    'challenge_id' => $locked->id,
                    'attempts'     => $locked->attempts,
                ]);

                if ($locked->hasExceededAttempts()) {
                    return back()->withErrors([
                        'otp_code' => __('অনেকবার ভুল OTP দেওয়া হয়েছে। নতুন OTP প্রয়োজন।'),
                    ]);
                }

                return back()->withErrors([
                    'otp_code' => __('ভুল OTP। অনুগ্রহ করে আবার চেষ্টা করুন।'),
                ]);
            }

            $locked->update(['verified_at' => now()]);

            $admin = $locked->admin;

            if (!$admin) {
                return $this->expiredSessionRedirect();
            }

            $remember = (bool) $request->session()->pull(self::SESSION_REMEMBER_KEY, false);

            Auth::guard('admin')->login($admin, $remember);
            $request->session()->regenerate();
            $request->session()->forget(self::SESSION_KEY);

            RateLimiter::clear($this->resendThrottleKey($locked->admin_id));

            $this->otpService->recordSuccessfulLogin($admin, $request);

            Log::info('Admin OTP verification succeeded', ['admin_id' => $admin->id]);

            return redirect()->intended(route('admin.dashboard'));
        });
    }

    public function resend(Request $request): RedirectResponse
    {
        $challenge = $this->pendingChallenge($request);

        if (!$challenge) {
            return $this->expiredSessionRedirect();
        }

        $rateKey = $this->resendThrottleKey($challenge->admin_id);

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            return back()->withErrors([
                'otp_code' => __('অনেকবার চেষ্টা করা হয়েছে। কিছুক্ষণ পর আবার চেষ্টা করুন।'),
            ]);
        }

        if (!$challenge->isExpired() && !$challenge->hasExceededAttempts()) {
            return back()->withErrors([
                'otp_code' => __('বর্তমান OTP এখনো মেয়াদ আছে। মেয়াদ শেষ হওয়ার পর আবার পাঠান।'),
            ]);
        }

        RateLimiter::hit($rateKey, 600);

        if (!$this->otpService->reissue($challenge)) {
            return back()->withErrors([
                'otp_code' => __('OTP পাঠানো যায়নি। কিছুক্ষণ পর আবার চেষ্টা করুন।'),
            ]);
        }

        return redirect()->route('admin.login.otp')
            ->with('status', __('A new OTP has been sent.'));
    }

    /**
     * The pending, not-yet-verified challenge tied to THIS browser session —
     * never trust a challenge id that didn't come from the session itself.
     */
    protected function pendingChallenge(Request $request): ?AdminOtpChallenge
    {
        $id = $request->session()->get(self::SESSION_KEY);

        if (!$id) {
            return null;
        }

        return AdminOtpChallenge::whereNull('verified_at')->find($id);
    }

    protected function resendThrottleKey(int $adminId): string
    {
        return 'admin-otp-resend:' . $adminId;
    }

    protected function expiredSessionRedirect(): RedirectResponse
    {
        return redirect()->route('admin.login')->withErrors([
            'username' => __('Your login session has expired. Please log in again.'),
        ]);
    }
}
