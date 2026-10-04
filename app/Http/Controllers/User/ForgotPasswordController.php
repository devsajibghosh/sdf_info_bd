<?php

namespace App\Http\Controllers\User;

use App\Helpers\BulkSmsHelper;
use App\Models\SmsTemplate;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    public function verifyOtpForm()
    {
        return view('user.auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'otp_code'     => 'required|digits:6',
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return back()->withErrors(['phone_number' => 'User not found.']);
        }

        if ($user->otp_code !== $request->otp_code) {
            return back()->withErrors(['otp_code' => 'Invalid OTP.']);
        }

        if (!$user->otp_sent_at || now()->diffInSeconds($user->otp_sent_at) > 120) {
            return back()->withErrors(['otp_code' => 'OTP has expired.']);
        }

        session(['otp_reset_user_id' => $user->id]);

        return redirect()->route('password.reset.form')->withSuccess('OTP verified. You can now reset your password.');
    }


    public function requestForm()
    {
        return view('user.auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $request->validate(['phone_number' => 'required|exists:users,phone_number']);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return back()->withErrors(['phone_number' => 'No user found with this phone number']);
        }

        if ($user->otp_sent_at && $user->otp_sent_at->diffInSeconds(now()) < generalSetting('otp_time')) {
            $remainingSeconds = generalSetting('otp_time') - $user->otp_sent_at->diffInSeconds(now());

            return back()->withErrors(['phone_number' => "OTP already sent. Please wait {$remainingSeconds} seconds."]);
        }

        try {
            $otpCode = rand(100000, 999999);
            $user->otp_code = $otpCode;
            $user->otp_sent_at = now();
            $user->save();

            $result = (new BulkSmsHelper())->send($user->phone_number, (string) SmsTemplate::render('password_reset_otp', ['code' => $otpCode]));

            if (empty($result['success'])) {
                Log::error('Password reset OTP SMS send failed', ['phone' => $user->phone_number, 'status' => $result['status'] ?? null, 'body' => $result['body'] ?? null]);

                return back()->withErrors(['phone_number' => 'Failed to send OTP. Please check SMS configuration.']);
            }

            return redirect()
                ->route('password.otp.form')
                ->withInput(['phone_number' => $user->phone_number])
                ->withSuccess('OTP sent successfully. Please enter it below.');
        } catch (\Exception $e) {
            Log::error('Failed to send OTP: ' . $e->getMessage());
            return back()->withErrors(['phone_number' => 'Failed to send OTP. Try again later.']);
        }
    }
}
