<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeGateway extends Gateway implements PaymentGatewayInterface
{
    protected static $key = 'stripe';

    protected static $image = 'stripe.jpeg';

    protected static $config = [
        'api_key' => 'Api Key',
        'secret_key' => 'Secret Key',
    ];

    public function getSupportedCurrencies(): array
    {
        return ["TRY", "USD", "EUR", "GBP", "RUB", "CHF", "NOK"];
    }

    public function create(Payment $payment): string
    {
        Stripe::setApiKey(self::dbConfig()?->secret_key);

        $session = Session::create([
            'mode' => 'payment',
            'success_url' => route('payment.notify', 'stripe') . '?trx=' . encrypt($payment->id),
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $payment->currency,
                        'unit_amount' => $payment->amount * 100, // to cents
                        'product_data' => ['name' => 'Payment to ' . generalSetting('site_title')]
                    ]
                ]
            ],
        ]);

        $payment->meta = ['stripe_session_id' => $session->id];
        $payment->save();

        return redirect()->away($session->url);
    }

    public function verify($request)
    {
        $trx = $request->trx;

        try {
            $id = decrypt($trx);
        } catch (\Throwable $th) {
            return back()->withError('Payment Failed');
        }

        $payment = Payment::where('id', $id)->first();

        if (!$payment) {
            return back()->withError('Payment not found');
        }

        Stripe::setApiKey(self::dbConfig()?->secret_key);

        $session = Session::retrieve($payment->meta['stripe_session_id']);

        if ($session->payment_status === 'paid') {
            $payment->paid_at = now();
            $payment->status = 'success';
            $payment->save();

            return $this->paymentSuccess($request, $payment);
        }

        return back()->withError('Payment not completed');
    }

}
