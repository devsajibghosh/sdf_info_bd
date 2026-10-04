<?php

namespace App\Services;

use App\Helpers\BulkSmsHelper;
use App\Models\SmsTemplate;
use App\Models\Admin;
use App\Models\AdminLogin;
use App\Models\AdminOtpChallenge;
use Browser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Issues, resends and validates the 4-digit OTP challenge admins must clear
 * (after their phone/password are correct) before a dashboard session is
 * established. Reuses the project's existing BulkSmsHelper for delivery.
 */
class AdminOtpService
{
    /**
     * Create a brand-new OTP challenge for the given admin and send it.
     * Returns ['success' => bool, 'challenge' => AdminOtpChallenge|null].
     */
    public function issueChallenge(Admin $admin, ?string $ip): array
    {
        $code = $this->generateCode();

        $challenge = AdminOtpChallenge::create([
            'admin_id'   => $admin->id,
            'otp_hash'   => Hash::make($code),
            'attempts'   => 0,
            'expires_at' => now()->addSeconds(AdminOtpChallenge::VALIDITY_SECONDS),
            'ip_address' => $ip,
        ]);

        if (!$this->sendSms($admin, $code)) {
            $challenge->delete();

            return ['success' => false, 'challenge' => null];
        }

        Log::info('Admin OTP challenge issued', [
            'admin_id'     => $admin->id,
            'challenge_id' => $challenge->id,
        ]);

        return ['success' => true, 'challenge' => $challenge];
    }

    /**
     * Replace the OTP on an existing (expired) challenge in place — this is
     * what makes the previous code stop working immediately: the hash it
     * would have been checked against no longer exists.
     */
    public function reissue(AdminOtpChallenge $challenge): bool
    {
        $admin = $challenge->admin;

        if (!$admin) {
            return false;
        }

        $code = $this->generateCode();

        if (!$this->sendSms($admin, $code)) {
            return false;
        }

        $challenge->update([
            'otp_hash'    => Hash::make($code),
            'attempts'    => 0,
            'expires_at'  => now()->addSeconds(AdminOtpChallenge::VALIDITY_SECONDS),
            'verified_at' => null,
        ]);

        Log::info('Admin OTP challenge reissued', [
            'admin_id'     => $admin->id,
            'challenge_id' => $challenge->id,
        ]);

        return true;
    }

    /**
     * Cryptographically secure random 4-digit code (1000-9999).
     */
    protected function generateCode(): string
    {
        return (string) random_int(1000, 9999);
    }

    /**
     * @return bool true only when the SMS was actually accepted by the gateway.
     */
    protected function sendSms(Admin $admin, string $code): bool
    {
        if (empty($admin->phone_number)) {
            Log::error('Admin OTP SMS not sent: admin has no phone number on file', [
                'admin_id' => $admin->id,
            ]);

            return false;
        }

        try {
            $message = (string) SmsTemplate::render('admin_login_otp', ['code' => $code, 'name' => $admin->name]);

            $result = (new BulkSmsHelper())->send($admin->phone_number, $message);

            if (empty($result['success'])) {
                Log::error('Admin OTP SMS send failed', [
                    'admin_id' => $admin->id,
                    'status'   => $result['status'] ?? null,
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Admin OTP SMS send threw an exception', [
                'admin_id' => $admin->id,
                'message'  => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * "01886880299" -> "01******299", matching the format expected in the UI.
     */
    public function maskPhone(?string $phone): string
    {
        $phone = (string) $phone;
        $len = strlen($phone);

        if ($len <= 5) {
            return $phone;
        }

        return substr($phone, 0, 2) . str_repeat('*', $len - 5) . substr($phone, -3);
    }

    /**
     * Login history row (Reports > Admin Login) for an admin who just got a
     * dashboard session, with or without the OTP step.
     */
    public function recordSuccessfulLogin($admin, Request $request): void
    {
        try {
            $ip = $request->ip();
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");

            $admin->last_login = now();
            $admin->save();

            $adminLogin              = new AdminLogin();
            $adminLogin->admin_id    = $admin->id;
            $adminLogin->device_type = Browser::deviceType();
            $adminLogin->browser     = Browser::browserName();
            $adminLogin->os          = Browser::platformName();
            $adminLogin->ip          = $ip;
            $adminLogin->country     = $response['country'] ?? 'Unknown';
            $adminLogin->city        = $response['city'] ?? 'Unknown';
            $adminLogin->save();
        } catch (\Throwable $e) {
            // Never let device/geo logging failures block a legitimate, already
            // OTP-verified login.
            Log::warning('Failed to record admin login metadata', ['message' => $e->getMessage()]);
        }
    }
}
