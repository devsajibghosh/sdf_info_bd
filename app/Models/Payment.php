<?php

namespace App\Models;

use App\Services\SslCommerzChannel;
use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use Modeling;

    protected $guarded = [
        // '*'
        // 'user_id',
        // 'status',
        // 'amount',
        // 'currency',
        // 'transaction_no',
        // 'method',
        // 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->status == 'pending') {
            return '<span class="badge text-bg-dark">' . __('Pending') . '</span>';
        }

        if ($this->status == 'success') {
            return '<span class="badge text-bg-success">' . __('Success') . '</span>';
        }


        if ($this->status == 'failed') {
            return '<span class="badge text-bg-danger">' . __('Failed') . '</span>';
        }

        if ($this->status == 'refunded') {
            return '<span class="badge text-bg-warning">' . __('Refunded') . '</span>';
        }
    }
    
    public function scopeSuccess($query) {
        return $query->where('status', 'success');
    }

    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function donation()
    {
        return $this->hasOne(Donation::class);
    }

    /**
     * Payment channel for display, e.g. "SSLCommerz — bKash", "SSLCommerz —
     * VISA". For every gateway other than SSLCommerz this is unchanged
     * from the existing gateway name. The SSLCommerz-specific part is
     * derived from the validation data SSLCommerz already returned for
     * this transaction (payments.meta->sslcz_validation) — no new column.
     */
    public function getDisplayChannelAttribute(): string
    {
        $gatewayName = $this->paymentGateway?->name ?? '-';

        if ($this->paymentGateway?->key !== 'sslcommerz') {
            return $gatewayName;
        }

        $validation = is_array($this->meta) ? ($this->meta['sslcz_validation'] ?? null) : null;

        return $gatewayName . ' — ' . SslCommerzChannel::label($validation);
    }
}
