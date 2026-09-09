<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualSubmission extends Model
{
    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id', 'id');
    }


    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    
}

