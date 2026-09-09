<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminOtpChallenge extends Model
{
    /**
     * How long a generated OTP stays valid, in seconds.
     */
    const VALIDITY_SECONDS = 60;

    /**
     * Maximum number of wrong OTP submissions before the challenge is
     * invalidated and a new OTP must be requested.
     */
    const MAX_ATTEMPTS = 5;

    protected $fillable = [
        'admin_id',
        'otp_hash',
        'attempts',
        'expires_at',
        'verified_at',
        'ip_address',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? true;
    }

    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }
}
