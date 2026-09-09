<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Razorpay\Api\Api;

class RazorpayGateway extends Gateway implements PaymentGatewayInterface
{
    protected static $key = 'razorpay';
    protected static $image = 'razorpay.png';
    protected static $config = [
        'api_key' => 'API Key',      // Replace with Razorpay API Key
        'api_secret' => 'API Secret', // Replace with Razorpay API Secret
    ];

    public function __construct()
    {
        $this->api = new Api(self::$config['api_key'], self::$config['api_secret']);
    }

    // Get supported currencies for Razorpay (modify if needed)
    public function getSupportedCurrencies(): array
    {
        return ["INR", "USD", "EUR", "GBP"];
    }

    // Create a payment order using Razorpay's API
    public function create(Payment $payment): string
    {
        $orderData = [
            'receipt'         => $payment->id,
            'amount'          => $payment->amount * 100, // Amount in paise (cents for INR)
            'currency'        => $payment->currency,
            'payment_capture' => 1, // Auto capture
        ];

        try {
            // Create Razorpay order
            $order = $this->api->order->create($orderData);

            // Save the order ID to payment's metadata
            $payment->meta = [
                'razorpay_order_id' => $order->id,
            ];
            $payment->save();

            // Redirect the user to Razorpay payment page
            $razorpayUrl = 'https://checkout.razorpay.com/v1/checkout.js?order_id=' . $order->id;
            
            return redirect()->away($razorpayUrl);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Verify the payment after transaction
    public function verify($request)
    {
        $orderId = $request->input('razorpay_order_id');
        $paymentId = $request->input('razorpay_payment_id');
        $signature = $request->input('razorpay_signature');

        if (!$orderId || !$paymentId || !$signature) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        $payment = Payment::where('meta->razorpay_order_id', $orderId)->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Verify signature
        $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, self::$config['api_secret']);

        if ($signature !== $generatedSignature) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Mark payment as successful
        $payment->status = 'paid';
        $payment->paid_at = now();
        $payment->meta = array_merge($payment->meta ?? [], [
            'razorpay_payment_id' => $paymentId,
        ]);
        $payment->save();

        return $this->paymentSuccess($request, $payment);
    }
}
