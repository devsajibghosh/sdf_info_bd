<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use Modeling;

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    
    public function category()
    {
        return $this->belongsTo(DonationCategory::class, 'donation_category_id', 'id')->withDefault([
            'name' => 'N/A'
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function scopePending($query) {
        return $query->where('status', 0);
    }

    public function scopeSuccess($query) {
        return $query->where('status', 1);
    }

    public function scopeRejected($query) {
        return $query->where('status', 2);
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->status == 0) {
            return '<span class="badge text-bg-dark">' . __('Pending') . '</span>';
        }

        if ($this->status == 1) {
            return '<span class="badge text-bg-success">' . __('Success') . '</span>';
        }

        if ($this->status == 2) {
            return '<span class="badge text-bg-danger">' . __('Rejected') . '</span>';
        }
    }
}
