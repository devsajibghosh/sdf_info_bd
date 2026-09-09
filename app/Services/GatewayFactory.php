<?php

namespace App\Services;

use App\Services\Gateways\BkashGateway;
use App\Services\Gateways\IyzicoGateway;
use App\Services\Gateways\ManualPaymentGateway;
use App\Services\Gateways\PaystackGateway;
use App\Services\Gateways\SslCommerzGateway;
use App\Services\Gateways\StripeGateway;
use App\Services\Gateways\PaymentGatewayInterface;
use App\Services\Gateways\TwoCheckoutGateway;
use App\Services\Gateways\WorldpayGateway;

class GatewayFactory
{
    public static function make(string $gateway)
    {
        return match ($gateway) {
            'stripe'     => new StripeGateway(),
            '2checkout'  => new TwoCheckoutGateway(),
            'iyzico'     => new IyzicoGateway(),
            'worldpay'   => new WorldpayGateway(),
            'paystack'   => new PaystackGateway(),
            'sslcommerz' => new SslCommerzGateway(),
            'bkash'      => new BkashGateway(),
            default      => new ManualPaymentGateway($gateway)
        };
    }
}
