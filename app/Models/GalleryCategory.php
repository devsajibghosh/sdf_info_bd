<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class GalleryCategory extends Model
{
    use Modeling;
    
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->status == 1) {
            return '<span class="badge text-bg-success">' . __('Active') . '</span>';
        } else {
            return '<span class="badge text-bg-danger">' . __('Inactive') . '</span>';
        }
    }
}
