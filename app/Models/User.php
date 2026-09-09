<?php

namespace App\Models;

use App\Mail\SystemNotification;
use App\Notifications\UserResetPassword;
use App\Traits\Modeling;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Modeling;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'city',
        'country_code',
        'zipcode',
        'name',
        'name_en',
        'preference',
        'reference',
        'joining_media',
        'email',
        'address',
        'password',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'date_of_birth',
        'current_age',
        'id_type',
        'id_number',
        'gender',
        'blood_group',
        'occupation',
        'marital_status',
        'country',
        'phone_number',
        'whatsapp_number',
        'facebook_id_link',
        'political_relation',
        'post_and_politics',
        'division',
        'district',
        'upazila',
        'post_office',
        'ward',
        'monthly_fee',
        'image_path',
        'image',
        'nid_front',
        'present_address',
        'nid_back'
    ];

    public function callLogs()
    {
        return $this->hasMany(CallLog::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_sent_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted()
    {
        static::saving(function ($user) {
            $user->name = trim($user->first_name . ' ' . $user->last_name);

            if (empty($user->username)) {
                $base           = Str::slug($user->first_name . $user->last_name);
                $suffix         = rand(10, 99);
                $user->username = strtolower($base . $suffix);
            }
        });
    }


    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    
    public function scopeInActive($query)
    {
        return $query->where('status', 0);
    }

    public function userLogins()
    {
        return $this->hasMany(UserLogin::class);
    }
    
   // relation with donation
public function relationwithDonation()
    {
        return $this->hasone(Donation::class,'phone_number','phone_number');
    }
    
    
    
    public static function getUniqueLocations(string $column)
    {
        return self::select($column)
                    ->whereNotNull($column)
                    ->where($column, '!=', '')
                    ->distinct()
                    ->orderBy($column)
                    ->pluck($column);
    }


    public function getStatusBadgeAttribute()
    {
        if ($this->status == 1) {
            return '<span class="badge text-bg-success">' . __('Active') . '</span>';
        }

        if ($this->status == 0) {
            return '<span class="badge text-bg-warning">' . __('Inactive') . '</span>';
        }

        if ($this->status == 2) {
            return '<span class="badge text-bg-danger">' . __('Blocked') . '</span>';
        }
    }

    // public function donations()
    // {
    //     return $this->hasMany(Donation::class);
    // }
    public function donations()
{
    return $this->hasMany(Donation::class, 'phone_number', 'phone_number');
}


    public function getProfileCompletedBadgeAttribute()
    {
        if ($this->pc == 1) {
            return '<span class="badge text-bg-success">' . __('Yes') . '</span>';
        }

        if ($this->pc == 0) {
            return '<span class="badge text-bg-danger">' . __('No') . '</span>';
        }
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPassword($token));
    }

    public function sendEmailVerificationNotification()
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->getKey(), 'hash' => sha1($this->getEmailForVerification())]
        );

        Mail::to($this->email)->send(new SystemNotification(
            subjectLine: '📧 Verify Your Email Address',
            viewName: 'email-verification',
            data: [
                'user' => $this,
                'verificationUrl' => $verificationUrl,
            ],
            user: $this
        ));
    }

    public function scopeEmailUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    public function scopePhoneNumberUnverified($query)
    {
        return $query->whereNull('phone_verified_at');
    }


    public function scopeNew($query)
    {
        return $query->where('created_at', 'like', date("Y-m-d") . "%");
    }

    public function scopeIncompleteProfile($query)
    {
        return $query->where('pc', 0);
    }
}
