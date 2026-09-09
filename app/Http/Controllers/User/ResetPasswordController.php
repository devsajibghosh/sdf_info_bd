<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ResetPasswordController extends Controller
{
    public function showResetForm()
    {
        if (!session()->has('otp_reset_user_id')) {
            return redirect()->route('password.request')->withErrors('Session expired. Please request OTP again.');
        }

        return view('user.auth.reset-password'); // Show password reset input form
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6',
        ]);

        $userId = session('otp_reset_user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('password.request')->withErrors('User not found.');
        }

        $user->password = Hash::make($request->password);
        $user->otp_code = null;
        $user->otp_sent_at = null;
        $user->save();

        session()->forget('otp_reset_user_id');

        Auth::login($user);

        return redirect()->route('user.dashboard')->withSuccess(__('Password reset successfully.'));
    }
}
