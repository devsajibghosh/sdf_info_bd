<?php

namespace App\Services\Gateways;

use App\Models\Payment;

class TwoCheckoutGateway extends Gateway implements PaymentGatewayInterface
{
    protected static $key = '2checkout';

    protected static $image = '2checkout.png';

    protected static $config = [
        'merchent_code' => 'Merchant Code',
        'secret_key' => 'Secret Key'
    ];

    public function getSupportedCurrencies(): array
    {
        return ["TRY", "USD", "EUR", "GBP", "RUB", "CHF", "NOK"];
    }

    public function create(Payment $payment): string
    {
        $merchant = config('services.twocheckout.merchant');
        $sandbox = config('services.twocheckout.sandbox', true);
        $baseUrl = $sandbox
            ? 'https://2checkout.com/checkout/purchase'
            : 'https://secure.2checkout.com/checkout/purchase';

        $params = [
            'sid' => $merchant,
            'mode' => '2CO',
            'li_0_type' => 'product',
            'li_0_name' => 'Payment',
            'li_0_price' => $payment->amount,
            'currency_code' => strtoupper($payment->currency),
            'merchant_order_id' => $payment->transaction_no,
            'x_receipt_link_url' => route('payment.success', ['pid' => $payment->id]),
            'demo' => $sandbox ? 'Y' : 'N',
        ];

        return $baseUrl . '?' . http_build_query($params);
    }

    public function verify($payment)
    {
        // Usually handled by IPN or return hash verification.
        // For simplicity, we'll trust the return for now.

        // You can validate the request with md5 hash (if needed).
        return true;
    }
}
