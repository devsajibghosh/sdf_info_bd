<?php

namespace App\Services\Gateways;

use App\Helpers\GatewayHelper;
use App\Models\Payment;
use App\Models\PaymentGateway;

class ManualPaymentGateway
{
    use GatewayHelper;

    protected static $key = 'manual';

    protected static $image = 'stripe.jpeg';

    protected static $config = [];


    public function __construct($gatewayKey) {}

    public function create($payment)
    {
        $gateway = PaymentGateway::where('id', $payment->payment_gateway_id)->firstOrFail();

        $paymentId = encrypt($payment->id);

        return view('user.manual_gateway', compact('gateway', 'paymentId'));
    }

    public function verify($request)
    {
        $paymentId = decrypt($request->payment_id);
        $payment = Payment::where('id', $paymentId)->first();

        if(!$request->trx) {
            return back()->withError(__('Transaction field is required'));
        }
        
        if (!$payment) {
            return back()->withError(__('Payment not found'));
        }

        $payment->meta = [
            'trx' => $request->trx
        ];
        $payment->paid_at = now();
        $payment->status = 'pending';
        $payment->save();

        return $this->paymentSuccess($request, $payment);
    }
}
