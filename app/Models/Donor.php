<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Donor extends Authenticatable
{
    use Modeling;

    protected $guarded = [];

    protected $casts = [
        'otp_sent_at' => 'datetime',
    ];

public function donations()
    {
        return $this->hasMany(Donation::class, 'phone_number', 'phone_number');
    }

    public function lastDonation()
    {
        return $this->hasOne(Donation::class)->latestOfMany();
    }
}
