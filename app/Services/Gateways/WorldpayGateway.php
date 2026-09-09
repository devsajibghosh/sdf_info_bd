<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class WorldpayGateway extends Gateway implements PaymentGatewayInterface
{
    protected static $key = 'worldpay';
    protected static $image = 'worldpay.jpg';
    protected static $config = [
        'merchant_id' => 'Merchant ID',
        'api_key' => 'API Key',
    ];

    public function getSupportedCurrencies(): array
    {
        return ["USD", "EUR", "GBP"];
    }
    
    public function create(Payment $payment): string
    {
        // Use Worldpay's API to create the payment session
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . self::dbConfig()?->api_key,
        ])->connectTimeout(20)->timeout(30)->retry(2, 1000)->post('https://api.worldpay.com/v1/transactions', [
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'description' => 'Payment for ' . generalSetting('site_title'),
                    'capture_mode' => 'AUTOMATIC',
                    'merchant_order_reference' => $payment->id,
                    'success_url' => route('payment.success', ['pid' => $payment->id]),
                    'failure_url' => route('payment.failure', ['pid' => $payment->id]),
                ]);

        $transactionData = $response->json();
        $payment->meta = [
            'transaction_id' => $transactionData['transactionId'],
            'payment_url' => $transactionData['paymentUrl'],
        ];
        $payment->save();

        // Redirect to the payment page (you can customize the redirect)
        return redirect()->away($transactionData['paymentUrl']);
    }

    // Verify the payment after the transaction
    public function verify($request)
    {
        $transactionId = $request->input('transactionId');

        if (!$transactionId) {
            return response()->json(['error' => 'Missing transaction ID'], 400);
        }

        // Retrieve payment status from Worldpay API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . self::dbConfig()?->api_key,
        ])->connectTimeout(20)->timeout(30)->retry(2, 1000)->get('https://api.worldpay.com/v1/transactions/' . $transactionId);

        $transactionStatus = $response->json();

        if ($transactionStatus['status'] == 'SUCCESS') {
            $payment = Payment::where('meta->transaction_id', $transactionId)->first();

            if ($payment) {
                $payment->status = 'paid';
                $payment->paid_at = now();
                $payment->save();

                return $this->paymentSuccess($request, $payment);
            }
        }

        return response()->json(['error' => 'Payment verification failed'], 400);
    }
}
