<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use Modeling;

    protected $guarded = [];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    
    // public function approvedBy()
    // {
    //     return $this->belongsTo(Admin::class,'approved_by');
    // }
    
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
    
public function relationwithUser()
    {
        return $this->hasone(User::class,'phone_number','phone_number');
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

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
    
    public function scopeNotFieldCollection($q){
        return $q->where('field_collection', 0);
    }
    
    public function scopeFieldCollection($q) {
        return $q->where('field_collection', 1);
    }
    
     public function getFieldCollectionBadgeAttribute()
    {
        if ($this->field_collection == 0) {
            return '<span class="badge text-bg-dark">' . __('No') . '</span>';
        }

        if ($this->field_collection == 1) {
            return '<span class="badge text-bg-success">' . __('Yes') . '</span>';
        }
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

    /**
     * Resolve the donor's name for documents (e.g. the PDF receipt) that
     * should prefer an English value.
     *
     * Priority: an explicit `name_en` set by the admin/user (never guessed
     * or auto-transliterated) > the stored `name` if it's already
     * ASCII/English > the stored `name` as-is (last resort, so a real name
     * is never dropped or replaced with fabricated data). The original
     * `name` column is never modified by this method.
     */
    public function receiptDonorName(): string
    {
        foreach ([$this->user?->name_en, $this->donor?->name_en] as $candidate) {
            $candidate = is_string($candidate) ? trim($candidate) : '';
            if ($candidate !== '') {
                return $candidate;
            }
        }

        $names = [$this->user?->name, $this->donor?->name];

        foreach ($names as $candidate) {
            $candidate = is_string($candidate) ? trim($candidate) : '';
            if ($candidate !== '' && preg_match('/^[\x20-\x7E]+$/', $candidate)) {
                return $candidate;
            }
        }

        foreach ($names as $candidate) {
            $candidate = is_string($candidate) ? trim($candidate) : '';
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return __('N/A');
    }
}
