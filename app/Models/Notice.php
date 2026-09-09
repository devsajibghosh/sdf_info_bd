<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use Modeling;

    protected $fillable = [
        'title',
        'description',
        'status',
        'visible_from',
        'visible_to'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('visible_from')->orWhere('visible_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('visible_to')->orWhere('visible_to', '>=', now());
            });
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
    }
}
