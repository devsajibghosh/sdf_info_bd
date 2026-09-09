<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Illuminate\Support\Facades\Http; // Using Laravel's HTTP Client for cleaner requests

class BkashGateway extends Gateway implements PaymentGatewayInterface
{
    /**
     * The unique key for this gateway.
     */
    protected static $key = 'bkash';

    /**
     * The gateway's logo image file.
     */
    protected static $image = 'bkash.jpg';

    /**
     * The configuration fields required for this gateway.
     */
    protected static $config = [
        'app_key'      => 'App Key',
        'app_secret'   => 'App Secret',
        'username'     => 'Username',
        'password'     => 'Password',
        'sandbox_mode' => 'Sandbox Mode (true/false)',
    ];

    /**
     * Get the API base URL based on the mode.
     */
    private function getBaseUrl(): string
    {
        $config = self::dbConfig();
        return filter_var($config->sandbox_mode, FILTER_VALIDATE_BOOLEAN)
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta';
    }

    /**
     * Get the bKash authentication token.
     */
    private function getGrantToken(): ?string
    {
        $config = self::dbConfig();
        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'username'      => $config->username,
            'password'      => $config->password,
        ])->connectTimeout(20)->timeout(30)->retry(2, 1000)->post($this->getBaseUrl() . '/tokenized/checkout/token/grant', [
            'app_key'    => $config->app_key,
            'app_secret' => $config->app_secret,
        ]);

        if ($response->successful() && $response->json('id_token')) {
            return $response->json('id_token');
        }

        // Log the error for debugging
        // logger()->error('bKash Grant Token Error: ' . $response->body());
        return null;
    }

    public function getSupportedCurrencies(): array
    {
        return ["BDT"]; // bKash only supports Bangladeshi Taka
    }

    public function create(Payment $payment): string
    {
        // bKash only supports BDT
        if (strtoupper($payment->currency) !== 'BDT') {
            throw new \Exception('bKash only supports BDT currency.');
        }

        $token = $this->getGrantToken();
        if (!$token) {
            // In a real app, you would throw an exception or handle this gracefully
            return back()->withError('bKash gateway is not configured correctly. Please contact support.');
        }

        $config = self::dbConfig();
        $response = Http::withHeaders([
            'Content-Type'    => 'application/json',
            'Accept'          => 'application/json',
            'Authorization'   => $token,
            'X-App-Key'       => $config->app_key,
        ])->connectTimeout(20)->timeout(30)->retry(2, 1000)->post($this->getBaseUrl() . '/tokenized/checkout/create', [
            'mode'                  => '0011', // 0011 for checkout
            'payerReference'        => 'user_' . $payment->user_id,
            'callbackURL'           => route('payment.notify', 'bkash'),
            'amount'                => round($payment->amount, 2),
            'currency'              => 'BDT',
            'intent'                => 'sale',
            'merchantInvoiceNumber' => $payment->id, // Use our payment ID as the invoice number
        ]);
        
        $responseData = $response->json();

        if ($response->successful() && $responseData['statusCode'] === '0000') {
            // Save the bKash paymentID for verification later
            $payment->meta = ['bkash_payment_id' => $responseData['paymentID']];
            $payment->save();

            // Redirect user to the bKash payment page
            return redirect()->away($responseData['bkashURL']);
        }

        // Handle failure
        $errorMessage = $responseData['statusMessage'] ?? 'Unknown error creating bKash payment.';
        return back()->withError($errorMessage);
    }

    public function verify($request)
    {
        $paymentID = $request->input('paymentID');
        $status = $request->input('status');

        if (!$paymentID || $status !== 'success') {
            $errorMessage = $status === 'cancel' ? 'Payment was cancelled.' : 'Payment failed.';
            return back()->withError($errorMessage);
        }

        // Find the payment record using the paymentID from bKash
        $payment = Payment::where('meta->bkash_payment_id', $paymentID)->first();

        if (!$payment) {
            return back()->withError('Payment record not found.');
        }
        
        if ($payment->isPaid()) {
            return $this->paymentSuccess($request, $payment);
        }

        // To securely verify, we must execute the payment
        $token = $this->getGrantToken();
        if (!$token) {
            return back()->withError('Could not verify payment due to gateway configuration error.');
        }

        $config = self::dbConfig();
        $response = Http::withHeaders([
            'Authorization' => $token,
            'X-App-Key'     => $config->app_key,
        ])->connectTimeout(20)->timeout(30)->retry(2, 1000)->post($this->getBaseUrl() . '/tokenized/checkout/execute', [
            'paymentID' => $paymentID,
        ]);

        $responseData = $response->json();

        // Check if execution was successful and transaction is complete
        if ($response->successful() && $responseData['statusCode'] === '0000' && $responseData['transactionStatus'] === 'Completed') {
            $payment->status = 'success';
            $payment->paid_at = now();
            
            // Store the final transaction ID for reference
            $meta = $payment->meta;
            $meta['bkash_trx_id'] = $responseData['trxID'];
            $payment->meta = $meta;
            
            $payment->save();

            return $this->paymentSuccess($request, $payment);
        }

        $errorMessage = $responseData['statusMessage'] ?? 'Payment verification failed.';
        return back()->withError($errorMessage);
    }
}