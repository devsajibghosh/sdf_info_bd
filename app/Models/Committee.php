<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    use Modeling;
    
    protected $table = 'committees';   
    
}