<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use Modeling;
    
    protected $fillable = ['name', 'email', 'phone_number', 'subject', 'message'];
}
