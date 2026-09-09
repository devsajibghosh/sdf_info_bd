<?php

namespace App\Services;

use App\Helpers\BulkSmsHelper;
use App\Models\Admin;
use App\Models\AdminOtpChallenge;
use Illuminate\Support\Facades\Hash;
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
            $message = "Your SDF admin login verification code is: {$code}. It expires in 1 minute.";

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
}
