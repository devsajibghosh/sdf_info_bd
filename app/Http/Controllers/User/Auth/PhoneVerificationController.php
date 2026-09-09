<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PhoneVerificationController extends Controller
{
    /**
     * Resend OTP to the user's phone number.
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        // Already verified
        if ($user->phone_verified_at) {
            return redirect()->route('user.dashboard')->with('status', 'already-verified');
        }

        $cooldown = 60; // seconds

        if ($user->otp_sent_at && now()->lessThan($user->otp_sent_at->addSeconds($cooldown))) {
            $wait = $user->otp_sent_at->addSeconds($cooldown)->diffInSeconds(now());
            return back()->with('error', "Please wait {$wait} seconds before requesting a new OTP.");
        }

        // Generate and store new OTP
        $code              = rand(100000, 999999);
        $user->otp_code    = $code;
        $user->otp_sent_at = now();
        $user->save();

        (new \App\Helpers\BulkSmsHelper())->send(
            $user->phone_number,
            "Your one-time SDF password is: {$code}"
        );

        return back()->with('status', 'otp-sent');
    }

    /**
     * Verify the submitted OTP code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $user = $request->user();

        // Check if OTP exists and hasn't expired
        if (!$user->otp_code || !$user->otp_sent_at || now()->diffInSeconds($user->otp_sent_at) > generalSetting('otp_time')) {
            return back()->with('error', 'OTP expired or not found. Please resend.');
        }

        // Check if OTP matches
        if ($request->otp != $user->otp_code) {
            return back()->with('error', 'Invalid OTP code.');
        }

        // Mark phone as verified
        $user->phone_verified_at = now();
        $user->otp_code = null; // Optional: Clear the OTP
        $user->otp_sent_at = null; // Optional: Clear the timestamp
        $user->save();

        return redirect()->route('user.dashboard')->with('status', 'phone-verified');
    }
}
