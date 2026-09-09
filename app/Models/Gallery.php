<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use Modeling;
    
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function galleryCategory()
    {
        return $this->belongsTo(GalleryCategory::class);
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
