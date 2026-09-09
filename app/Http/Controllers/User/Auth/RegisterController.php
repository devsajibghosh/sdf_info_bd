<?php

namespace App\Http\Controllers\User\Auth;

use App\Facades\System;
use App\Helpers\BulkSmsHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    protected $usernameField = 'phone_number'; // can be email or phone

    public function register()
    {
        $title = generalSetting('user_registration') ? 'User Registration' : 'Registration is Disabled';

        if (!generalSetting('user_registration')) {
            return view('user.auth.register_disabled', compact('title'));
        }

        return view('user.auth.register', compact('title'));
    }

    public function registerStore(Request $request)
    {
        $rules = [
            'phone_number' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^\+?[0-9]{10,15}$/', $value)) {
                    $fail('The ' . $attribute . ' must be a valid email or phone number.');
                }
            }],
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'password'   => 'required|string|min:6',
        ];

        if (System::googleCaptchaEnabled()) {
            $rules['g-recaptcha-response'] = ['required', 'captcha'];
        }

        $request->validate($rules);

        $input = $request->phone_number;

        $user = null;
        $isPhone = false;

        // Check for email or phone
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            if (User::where('donator', 1)->where('email', $input)->exists()) {
                return back()->withErrors(['email' => 'Email already exists']);
            }
            $user = User::where('email', $input)->first() ?? new User();
            $user->email = $input;
        } elseif (preg_match('/^\+?[0-9]{10,15}$/', $input)) {
            if (User::where('donator', 1)->where('phone_number', $input)->exists()) {
                return back()->withErrors(['email' => 'Phone number already exists']);
            }
            $user = User::where('phone_number', $input)->first() ?? new User();
            $user->phone_number = $input;
            $isPhone = true;
        } else {
            return back()->withErrors(['email' => 'Invalid email or phone number']);
        }

        // Set name and password
        $user->first_name = $request->first_name;
        $user->last_name  = $request->last_name;
        $user->password   = Hash::make($request->password);
        $user->status = 0;
        $user->donator = 0;

        // KYC flag
        if (generalSetting('kyc')) {
            $user->kyc_required = true;
        }

        // Save user first
        $user->save();

        // Generate and store OTP if it's a phone number
        if ($isPhone) {
            $code = rand(100000, 999999);
            $user->otp_code    = $code;
            $user->otp_sent_at = now();
            $user->save();

            // Send SMS
            (new BulkSmsHelper())->send(
                $user->phone_number,
                "Hello {$user->first_name} {$user->last_name}, Your one-time SDF password is: {$code}"
            );
        }

        // Admin Notification
        $adminNotification          = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->link    = route('admin.user.details', $user->id);
        $adminNotification->details = __('New user registered');
        $adminNotification->save();

        // Login the user
        Auth::loginUsingId($user->id);

        // Redirect
        return $isPhone
            ? to_route('user.phone.verify.notice') // Show verify phone page
            : to_route('user.dashboard');          // If email, go straight to dashboard
    }
}
