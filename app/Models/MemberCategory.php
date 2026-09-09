<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class MemberCategory extends Model
{
    use Modeling;

    public function users()
    {
        return $this->hasMany(User::class, 'category_id', 'id');
    }
}
