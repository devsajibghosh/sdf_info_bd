<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class DonationCategory extends Model
{
    use Modeling;
    
    protected $guarded = [];
    
    public function getStatusBadgeAttribute()
    {
        if ($this->status == 1) {
            return '<span class="badge text-bg-success">' . __('Active') . '</span>';
        } else {
            return '<span class="badge text-bg-danger">' . __('Inactive') . '</span>';
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }
}
