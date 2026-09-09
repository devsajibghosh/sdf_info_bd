<?php

namespace App\Models;

use App\Notifications\CustomAdminResetPasswordNotification;
use App\Traits\Modeling;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class Admin extends Authenticatable
{
    
    use Notifiable, HasRolesAndAbilities, Modeling;

    protected $casts = ['last_login' => 'datetime'];
    
    protected $fillable = ['username', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomAdminResetPasswordNotification($token));
    }

    public function adminLogins()
    {
        return $this->hasMany(AdminLogin::class);
    }

    public function getRoleAttribute()
    {
        return $this->roles()->first();
    }
}
