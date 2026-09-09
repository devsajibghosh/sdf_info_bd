<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class AdminLogin extends Model
{
    use Modeling;
    
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
