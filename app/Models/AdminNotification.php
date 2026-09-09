<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function donor() 
    {
        return $this->belongsTo(Donor::class);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', 1);
    }

    public function scopeUnRead($query)
    {
        return $query->where('is_read', 0);
    }
}
