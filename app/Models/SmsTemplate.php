<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Admin-editable text for every automatic SMS (Settings > SMS Settings).
 * Placeholders like {name} / {amount} / {code} are filled by render().
 */
class SmsTemplate extends Model
{
    protected $fillable = ['key', 'message', 'status'];

    protected $casts = ['status' => 'boolean'];

    /**
     * Every template the system uses: label, help text, allowed placeholders,
     * and whether it carries a login/verification code. OTP templates can't be
     * switched off individually (that would lock users out of login/reset) and
     * must keep the {code} placeholder.
     */
    public const EVENTS = [
        'donation_online' => [
            'label'        => 'Online donation success (SSLCommerz)',
            'help'         => 'Sent automatically to the donor/member when an online payment succeeds.',
            'placeholders' => ['{name}', '{amount}', '{trx}', '{date}'],
            'otp'          => false,
        ],
        'donation_manual' => [
            'label'        => 'Manual donation entry',
            'help'         => 'Sent when an admin creates a donation from Manual Donation (bKash/Nagad/Rocket/Bank/Cash/Goods).',
            'placeholders' => ['{name}', '{amount}', '{trx}', '{date}'],
            'otp'          => false,
        ],
        'donation_approved' => [
            'label'        => 'Pending donation approved',
            'help'         => 'Sent when a pending donation is approved by an admin or by the auto-approve API.',
            'placeholders' => ['{name}', '{amount}', '{trx}', '{date}'],
            'otp'          => false,
        ],
        'admin_login_otp' => [
            'label'        => 'Admin login OTP',
            'help'         => 'Verification code sent to an admin after the password is accepted.',
            'placeholders' => ['{code}', '{name}'],
            'otp'          => true,
        ],
        'user_login_otp' => [
            'label'        => 'Member OTP login',
            'help'         => 'One-time password for members logging in with their phone number.',
            'placeholders' => ['{code}'],
            'otp'          => true,
        ],
        'user_register_otp' => [
            'label'        => 'Member registration OTP',
            'help'         => 'Sent right after a member registers with a phone number.',
            'placeholders' => ['{code}', '{name}'],
            'otp'          => true,
        ],
        'phone_verification_otp' => [
            'label'        => 'Phone verification OTP (resend)',
            'help'         => 'Sent when a member requests a new phone verification code.',
            'placeholders' => ['{code}'],
            'otp'          => true,
        ],
        'password_reset_otp' => [
            'label'        => 'Password reset OTP',
            'help'         => 'Sent when a member resets the password by phone.',
            'placeholders' => ['{code}'],
            'otp'          => true,
        ],
    ];

    protected const CACHE_KEY = 'sms-templates';

    public static function allCached()
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::all()->keyBy('key'));
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * The final SMS text for $key with placeholders filled in, or null when
     * this SMS must not be sent (template switched off, or a non-OTP SMS
     * while the master SMS switch is off). OTP templates are never blocked
     * here — they are what lets people log in.
     */
    public static function render(string $key, array $data = []): ?string
    {
        $isOtp = self::EVENTS[$key]['otp'] ?? false;

        if (!$isOtp && !generalSetting('sms_enabled')) {
            return null;
        }

        $template = static::allCached()->get($key);

        if ($template && !$isOtp && !$template->status) {
            return null;
        }

        $message = $template?->message ?: '';

        $replace = [];
        foreach ($data as $name => $value) {
            $replace['{' . $name . '}'] = (string) $value;
        }

        return trim(strtr($message, $replace)) ?: null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }
}
