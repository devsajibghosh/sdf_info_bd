<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class PaystackGateway extends Gateway implements PaymentGatewayInterface
{
    protected static $key = 'paystack';
    protected static $image = 'paystack.png';
    protected static $config = [
        'api_key' => 'Api Key',
        'secret_key' => 'Secret Key',
    ];

    public function getSupportedCurrencies(): array
    {
        return ["NGN", "USD", "EUR", "GBP"];
    }

    /**
     * Create payment with Paystack API
     */
    public function create(Payment $payment): string
    {
        $secretKey = self::dbConfig()?->secret_key ?? null;

        if (!$secretKey) {
            throw new \Exception('Paystack secret key not configured.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $secretKey,
            'Accept' => 'application/json',
        ])->connectTimeout(20)->timeout(30)->retry(2, 1000)->post('https://api.paystack.co/transaction/initialize', [
                    'email' => $payment->user->email,
                    'amount' => $payment->amount * 100, // Paystack expects kobo
                    'currency' => 'NGN',
                    'callback_url' => route('payment.notify', 'paystack'),
                    'metadata' => [
                        'payment_id' => $payment->id,
                    ],
                ]);

        $data = $response->json();

        // dd($data);

        if (!$response->successful() || !isset($data['data']['authorization_url'])) {
            throw new \Exception($data['message'] ?? 'Unable to initiate Paystack payment.');
        }

        $payment->meta = [
            'paystack_reference' => $data['data']['reference']
        ];
        $payment->save();

        return redirect($data['data']['authorization_url']);
    }

    /**
     * Verify the payment and update user information 
     * @param mixed $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function verify($request)
    {
        $reference = $request->input('reference');

        if (!$reference) {
            return response()->json(['error' => 'Missing reference'], 400);
        }

        // Retrieve the transaction details
        $url = "https://api.paystack.co/transaction/verify/$reference";

        $secret_key = self::dbConfig()?->secret_key;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $secret_key,
        ])->connectTimeout(20)->timeout(30)->retry(2, 1000)->get($url);

        if ($response->failed()) {
            return response()->json(['error' => 'Unable to verify payment'], 500);
        }

        $data = $response->json();

        $payment = Payment::where('meta->paystack_reference', $reference)->first();

        if (!$payment) {
            return back()->withError('Payment failed');
        }

        if ($data['data']['status'] == 'success') {
            $payment->status = 'success';
            $payment->paid_at = now();
            $payment->meta = array_merge($payment->meta ?? [], [
                'paystack_transaction_id' => $data['data']['id'],
            ]);
            $payment->save();

            return $this->paymentSuccess($request, $payment);
        }

        return response()->json(['error' => 'Payment failed'], 400);
    }
}
