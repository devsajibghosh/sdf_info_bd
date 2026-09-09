<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $casts = [
        'config' => 'object'
    ];

    protected $appends = ['image_url'];

    protected $fillable = ['name', 'key', 'config', 'image'];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    public function scopeHidden($q){
        return $q->whereNotIn('key', ['goods', 'cash'])->where('for_admin', 0);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('manual', 0);
    }

    public function scopeManual($query)
    {
        return $query->where('manual', 1);
    }

    public function getImageUrlAttribute()
    {
        if ($this->manual) {
            return asset('storage/' . $this->image);
        }

        $class = \App\Helpers\GatewayHelper::paymentGateways($this?->key, false, false);

        if ($class && array_key_exists('class', $class)) {
            $class = $class['class'];
            return asset('assets/images/gateway/' . $class::getImage());
        }
    }
}
