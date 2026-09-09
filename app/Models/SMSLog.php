<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SMSLog extends Model
{
    protected $guarded = [];
    
    protected $table = 'sms_logs';

    public function user(){
        return $this->belongsTo(User::class);
    }
}