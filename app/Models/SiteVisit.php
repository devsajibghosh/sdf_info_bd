<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = [
        'ip',
        'visit_date'
    ];

    protected $casts = [
        'visit_date' => 'datetime',
    ];
}
