<?php

namespace App\Http\Controllers\User;

use App\Helpers\BulkSmsHelper;
use App\Http\Controllers\Controller;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login()
    {
        $title = 'User login';

        return view('user.auth.login', compact('title'));
    }

    public function otpLoginStore(Request $request)
    {
        $request->validate([
            'phone_number' => 'required'
        ]);

        $user = Donor::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return $request->ajax()
                ? response()->json(['message' => __('No user found with this phone number'), 'success' => false], 404)
                : back()->withErrors(__('No user found with this phone number'));
        }

        // if ($user->status == 0) {
        //     return $request->ajax()
        //         ? response()->json(['message' => __('Please wait for admin approval'), 'success' => false], 404)
        //         : back()->withErrors(__('Please wait for admin approval'));
        // }

        // if ($user->status == 2) {
        //     return $request->ajax()
        //         ? response()->json(['message' => __('User ID Locked, please contact with admin'), 'success' => false], 404)
        //         : back()->withErrors(__('User ID Locked, please contact with admin'));
        // }

        if ($user->otp_sent_at && $user->otp_sent_at->diffInSeconds(now()) < generalSetting('otp_time')) {
            $remainingSeconds = generalSetting('otp_time') - $user->otp_sent_at->diffInSeconds(now());

            return $request->ajax()
                ? response()->json([
                    'message' => 'OTP already sent. Please wait before requesting a new one.',
                    'success' => false,
                    'remaining_seconds' => $remainingSeconds
                ])
                : back()->withErrors("OTP already sent. Please wait {$remainingSeconds} seconds.");
        }


        try {
            $code              = rand(100000, 999999);
            $user->otp_code    = $code;
            $user->otp_sent_at = now();
            $user->save();

            (new BulkSmsHelper())->send($request->phone_number, "Your one-time password is: {$code}");

            return $request->ajax()
                ? response()->json([
                    'message' => 'OTP sent successfully. Please check your phone.',
                    'success' => true,
                    'remaining_seconds' => 120
                ])
                : back()->withSuccess('OTP sent successfully. Please check your phone.');
        } catch (\Exception $e) {
            return $request->ajax()
                ? response()->json([
                    'message' => 'Failed to send OTP. Please check SMS configuration.',
                    'success' => false
                ], 500)
                : back()->withErrors('Failed to send OTP: ' . $e->getMessage());
        }
    }

    public function validateOtpAndLogin(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'otp_code'     => 'required|digits:6',
        ]);
        $user = Donor::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return $request->ajax()
                ? response()->json(['message' => 'Donor not found.', 'success' => false], 404)
                : back()->withErrors('Donor not found.');
        }

        // Check if OTP matches
        if ($user->otp_code !== $request->otp_code) {
            return $request->ajax()
                ? response()->json(['message' => 'Invalid OTP.', 'success' => false], 422)
                : back()->withErrors('Invalid OTP.');
        }

        // Check if OTP is expired (older than 2 minutes)
        if (!$user->otp_sent_at || now()->diffInSeconds($user->otp_sent_at) > 120) {
            return $request->ajax()
                ? response()->json(['message' => 'OTP has expired. Please request a new one.', 'success' => false], 410)
                : back()->withErrors('OTP has expired. Please request a new one.');
        }

        // OTP is valid, log in the user
        Auth::guard('donor')->login($user);

        // Optionally, clear the OTP after successful login
        $user->otp_code = null;
        $user->otp_sent_at = null;
        if(@$user->donator) {
            $user->pv  = 1;
            $user->phone_verified_at = now();
        }
        $user->save();

        return to_route('user.dashboard');
        return $request->ajax()
            ? response()->json(['message' => 'Logged in successfully.', 'success' => true])
            : redirect()->intended('/user/dashboard')->withSuccess('Logged in successfully.');
    }

    public function loginStore(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password'     => 'required|string',
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return back()->withErrors([
                'phone_number' => 'Invalid credentials.',
            ])->withInput($request->only('phone_number'));
        }

        // Check status: inactive
        if ($user->status == 0) {
            return back()->withError(__('Please wait for admin approval'));
        }

        // Check status: blocked
        if ($user->status == 2) {
            return back()->withError(__('User ID is locked. Please contact the admin.'));
        }

        // Attempt login
        if (Auth::attempt([
            'phone_number' => $request->phone_number,
            'password'     => $request->password,
        ])) {
            return redirect()->intended('/user/dashboard')->withSuccess(__('You are logged in'));
        }

        // Password wrong but phone exists
        return back()->withErrors([
            'phone_number' => 'Invalid credentials.',
        ])->withInput($request->only('phone_number'));
    }

    public function logout()
    {
        Auth::logout();
        return to_route('home')->withSuccess(__('You are logged out'));
    }
}
