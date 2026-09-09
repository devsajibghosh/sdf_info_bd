<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $casts = [
        'sections' => 'array',
        'seo_content' => 'object',
    ];

    /** scope for privacy page */
    public function scopePrivacy($query)
    {
        return $query->where('privacy', 1);
    }

    public function getIsDefaultBadgeAttribute()
    {
        if ($this->is_default) {
            return '<span class="badge text-bg-success">' . __('Default') . '</span>';
        } else {
            return '<span class="badge text-bg-dark">' . __('No') . '</span>';
        }
    }

    public function getPrivacyPageBadgeAttribute()
    {
        if ($this->privacy) {
            return '<span class="badge text-bg-success">' . __('Yes') . '</span>';
        } else {
            return '<span class="badge text-bg-dark">' . __('No') . '</span>';
        }
    }
}
