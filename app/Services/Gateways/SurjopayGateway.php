<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SurjopayGateway extends Gateway implements PaymentGatewayInterface
{
    protected static $key = 'surjopay';
    protected static $image = 'surjopay.png'; // Ensure you have this image

    protected static $config = [
        'merchant_username' => 'Merchant Username',
        'merchant_password' => 'Merchant Password',
        'merchant_prefix' => 'Merchant Prefix (e.g., SP)', // Often used in transaction IDs
        'sandbox_mode' => 'Enable Sandbox Mode (yes/no)',
    ];

    // SurjoPay API URLs (Confirm these from their latest V2+ documentation)
    // UAT/Sandbox Endpoints
    protected const SANDBOX_TOKEN_URL = 'https://sandbox.surjopay.com.bd/api/get_token';
    protected const SANDBOX_PAYMENT_URL = 'https://sandbox.surjopay.com.bd/api/secret-pay';
    protected const SANDBOX_VERIFY_URL = 'https://sandbox.surjopay.com.bd/api/verification';

    // Live Endpoints
    protected const LIVE_TOKEN_URL = 'https://pay.surjopay.com.bd/api/get_token'; // Or similar
    protected const LIVE_PAYMENT_URL = 'https://pay.surjopay.com.bd/api/secret-pay';
    protected const LIVE_VERIFY_URL = 'https://pay.surjopay.com.bd/api/verification';

    public function getSupportedCurrencies(): array
    {
        // SurjoPay primarily supports BDT. Check their docs for others.
        return ["BDT"];
    }

    protected function getApiUrl(string $type): string
    {
        $config = self::dbConfig();
        $isSandbox = (isset($config->sandbox_mode) && strtolower($config->sandbox_mode) === 'yes');

        switch ($type) {
            case 'token':
                return $isSandbox ? self::SANDBOX_TOKEN_URL : self::LIVE_TOKEN_URL;
            case 'payment':
                return $isSandbox ? self::SANDBOX_PAYMENT_URL : self::LIVE_PAYMENT_URL;
            case 'verify':
                return $isSandbox ? self::SANDBOX_VERIFY_URL : self::LIVE_VERIFY_URL;
            default:
                throw new \InvalidArgumentException("Invalid API URL type for SurjoPay: {$type}");
        }
    }

    /**
     * Get an authentication token from SurjoPay.
     * @param object $config Gateway configuration.
     * @return string|null Token string or null on failure.
     */
    protected function getAuthToken(object $config): ?string
    {
        $tokenUrl = $this->getApiUrl('token');
        try {
            $response = Http::connectTimeout(20)->timeout(30)->retry(2, 1000)->post($tokenUrl, [
                'username' => $config->merchant_username,
                'password' => $config->merchant_password,
            ]);

            $responseData = $response->json();
            Log::debug('SurjoPay Get Token Response:', $responseData ?? ['raw_body' => $response->body()]);

            if ($response->successful() && isset($responseData['token']) && isset($responseData['expires_in'])) {
                // Optionally cache this token with its expiry if SurjoPay allows reusing tokens
                return $responseData['token'];
            } else {
                Log::error('SurjoPay: Failed to get auth token.', $responseData ?? []);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('SurjoPay: Exception while getting auth token: ' . $e->getMessage());
            return null;
        }
    }

    public function create(Payment $payment): string // Returns redirect URL
    {
        $config = self::dbConfig();
        if (!$config || !isset($config->merchant_username) || !isset($config->merchant_password) || !isset($config->merchant_prefix)) {
            Log::error('SurjoPay: Configuration missing (username, password, or prefix).');
            throw new \Exception('SurjoPay gateway not configured properly.');
        }

        $token = $this->getAuthToken($config);
        if (!$token) {
            throw new \Exception('SurjoPay: Could not retrieve authentication token.');
        }

        $paymentUrl = $this->getApiUrl('payment');
        // SurjoPay order_id is typically their internal unique ID for the transaction.
        // We can use our payment ID with their prefix.
        $orderId = strtoupper($config->merchant_prefix) . $payment->id . Str::random(3); // Unique order ID

        $postData = [
            'token' => $token,
            'store_id' => null, // Usually not needed if token is tied to store, but check docs
            'prefix' => $config->merchant_prefix, // From your merchant panel
            'order_id' => $orderId,
            'amount' => round($payment->amount, 2),
            'currency' => $payment->currency, // Usually "BDT"
            'return_url' => route('payment.notify', ['gateway' => self::$key, 'status' => 'return']), // Generic return
            'cancel_url' => route('payment.notify', ['gateway' => self::$key, 'status' => 'cancel']),
            // 'ipn_url' => route('payment.notify', ['gateway' => self::$key, 'status' => 'ipn']), // Optional IPN

            // Customer Information (Adjust based on SurjoPay's required fields)
            'customer_name' => $payment->user->name ?? 'Valued Customer',
            'customer_phone' => $payment->user->phone ?? '01000000000',
            'customer_email' => $payment->user->email ?? 'guest@example.com',
            'customer_address' => $payment->user->address ?? 'Customer Address',
            'customer_city' => $payment->user->city ?? 'Dhaka',
            'customer_post_code' => $payment->user->postcode ?? '1200',
            'client_ip' => request()->ip(), // SurjoPay often requires this

            // Optional values to pass through
            'value1' => encrypt($payment->id), // Your internal payment ID
            // 'value2' => '',
            // 'value3' => '',
            // 'value4' => '',
        ];

        Log::info('SurjoPay: Create Payment Request Data for payment ID ' . $payment->id, $postData);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json', // SurjoPay usually expects JSON
            ])->connectTimeout(20)->timeout(30)->retry(2, 1000)->post($paymentUrl, $postData);

            $responseData = $response->json();
            Log::info('SurjoPay: Create Payment Response for payment ID ' . $payment->id, $responseData ?? ['raw_body' => $response->body()]);

            if ($response->successful() && isset($responseData['status']) && strtoupper($responseData['status']) === 'SUCCESS' && isset($responseData['payment_url'])) {
                $payment->meta = array_merge((array) $payment->meta, [
                    'surjopay_order_id' => $orderId, // The order_id we sent
                    'surjopay_sp_order_id' => $responseData['sp_order_id'] ?? null, // SurjoPay's own internal ID if provided
                ]);
                $payment->transaction_id = $orderId; // Or $responseData['sp_order_id']
                $payment->save();

                return $responseData['payment_url']; // Redirect user to this URL
            } else {
                $errorMessage = 'SurjoPay: Payment initiation failed.';
                if (isset($responseData['message'])) {
                    $errorMessage .= ' Message: ' . (is_array($responseData['message']) ? json_encode($responseData['message']) : $responseData['message']);
                } else if (!$response->successful()) {
                    $errorMessage .= ' HTTP Error: ' . $response->status();
                }
                Log::error($errorMessage, $responseData ?? []);
                throw new \Exception($errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('SurjoPay: Exception during payment initiation: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw new \Exception('SurjoPay: Payment initiation process encountered an error.');
        }
    }

    public function verify($request) // $request is Illuminate\Http\Request
    {
        Log::info('SurjoPay: Verify Callback/Redirect Received.', $request->all());
        $config = self::dbConfig();
        if (!$config || !isset($config->merchant_username) || !isset($config->merchant_password)) {
            Log::error('SurjoPay: Configuration missing for verification.');
            return back()->withError('Gateway configuration error.');
        }

        $responseData = $request->all(); // Data received from SurjoPay on return_url

        // Retrieve our internal payment ID from value1 (or however it's passed back)
        // SurjoPay might send it as 'order_id' (the one we generated) or via one of value1-value4
        $encryptedPaymentId = $responseData['value1'] ?? null;
        $surjopayOrderId = $responseData['order_id'] ?? null; // This is likely the order_id we sent

        if (!$encryptedPaymentId && !$surjopayOrderId) {
            Log::error('SurjoPay: Required identifiers (value1 or order_id) missing in callback.');
            return back()->withError('Payment verification failed: Invalid request data.');
        }

        $payment = null;
        if ($encryptedPaymentId) {
            try {
                $paymentId = decrypt($encryptedPaymentId);
                $payment = Payment::find($paymentId);
            } catch (\Throwable $th) {
                Log::error('SurjoPay: Failed to decrypt payment ID from value1.', ['value1' => $encryptedPaymentId, 'error' => $th->getMessage()]);
                // Try finding by surjopayOrderId if decryption fails
            }
        }

        if (!$payment && $surjopayOrderId) {
            // Assuming 'transaction_id' or 'meta.surjopay_order_id' stores the $orderId we sent
            $payment = Payment::where('transaction_id', $surjopayOrderId)
                ->orWhere('meta->surjopay_order_id', $surjopayOrderId)
                ->first();
        }


        if (!$payment) {
            Log::error('SurjoPay: Payment record not found.', ['value1' => $encryptedPaymentId, 'surjopay_order_id' => $surjopayOrderId]);
            return back()->withError('Payment record not found.');
        }

        if ($payment->status === 'success') {
            Log::info('SurjoPay: Payment already marked as successful.', ['payment_id' => $payment->id]);
            return redirect()->route('payment.status.page', ['trx' => $payment->trx ?? $payment->id, 'status' => 'success'])
                ->with('success', 'Payment was already confirmed.');
        }

        // Status from SurjoPay's callback (e.g., 'sp_message' or 'status')
        // Their callback might not directly confirm payment. We MUST call their verification API.
        $callbackStatus = strtoupper($responseData['sp_message'] ?? ($responseData['status'] ?? 'UNKNOWN'));

        // If the initial callback already indicates failure or cancellation before verification API
        $routeStatus = $request->route('status'); // 'return', 'cancel' from our routes
        if ($routeStatus === 'cancel' || $callbackStatus === 'CANCELLED' || $callbackStatus === 'FAILED') {
            Log::info('SurjoPay: Payment cancelled or failed based on initial callback for payment ID ' . $payment->id, $responseData);
            $payment->status = ($callbackStatus === 'CANCELLED' || $routeStatus === 'cancel') ? 'cancelled' : 'failed';
            $payment->save();
            $message = ($payment->status === 'cancelled') ? 'Payment was cancelled.' : 'Payment failed at gateway.';
            return redirect()->route('payment.status.page', ['trx' => $payment->trx ?? $payment->id, 'status' => $payment->status])
                ->with($payment->status === 'cancelled' ? 'info' : 'error', $message);
        }

        // Proceed to call SurjoPay's Verification API
        $verifyUrl = $this->getApiUrl('verify');
        $spOrderIdToVerify = $responseData['sp_order_id'] ?? $payment->meta['surjopay_sp_order_id'] ?? $surjopayOrderId; // Use SP's ID if available, else ours

        if (!$spOrderIdToVerify) {
            Log::error('SurjoPay: sp_order_id missing for verification API call.', ['payment_id' => $payment->id, 'callback_data' => $responseData]);
            return back()->withError('Verification failed: Missing transaction identifier.');
        }

        try {
            $verifyResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->connectTimeout(20)->timeout(30)->retry(2, 1000)->post($verifyUrl, [
                'order_id' => $spOrderIdToVerify
            ]); // Verification API might need more params like store_id or auth

            $verifyData = $verifyResponse->json();
            Log::info('SurjoPay: Verification API Response for payment ID ' . $payment->id, $verifyData ?? ['raw_body' => $verifyResponse->body()]);

            if ($verifyResponse->successful() && is_array($verifyData)) {
                // SurjoPay verification API often returns an array of transaction(s)
                // We need to find our specific transaction and check its status.
                // The structure of verifyData can vary. It might be $verifyData[0]['status'] or $verifyData['status']
                $transactionData = null;
                if (isset($verifyData[0]) && is_array($verifyData[0]) && ($verifyData[0]['order_id'] ?? '') == $spOrderIdToVerify) {
                    $transactionData = $verifyData[0];
                } elseif (isset($verifyData['order_id']) && $verifyData['order_id'] == $spOrderIdToVerify) {
                    // If the response is not an array but a single transaction object
                    $transactionData = $verifyData;
                }


                if ($transactionData && isset($transactionData['status'])) {
                    $verifiedStatus = strtoupper($transactionData['status']);

                    if ($verifiedStatus === 'SUCCESS' || $verifiedStatus === 'COMPLETED' || $verifiedStatus === 'PAID') {
                        // Additional checks: amount, currency if available in verification response
                        $verifiedAmount = (float) ($transactionData['amount'] ?? 0);
                        $originalAmount = round((float) $payment->amount, 2);

                        if (abs($verifiedAmount - $originalAmount) > 0.01 && $verifiedAmount > 0) { // Only check if amount is present
                            Log::error('SurjoPay: Amount mismatch during verification for payment ID ' . $payment->id, [
                                'expected' => $originalAmount,
                                'received' => $verifiedAmount
                            ]);
                            $payment->status = 'failed'; // Or "validation_failed"
                            $payment->save();
                            return back()->withError('Payment verification failed: Amount mismatch.');
                        }

                        $payment->paid_at = now();
                        $payment->status = 'success';
                        $payment->meta = array_merge((array) $payment->meta, ['surjopay_verification' => $transactionData]);
                        $payment->save();

                        Log::info('SurjoPay: Payment verified via API and marked as success for payment ID ' . $payment->id);
                        return $this->paymentSuccess($request, $payment);
                    } elseif ($verifiedStatus === 'FAILED' || $verifiedStatus === 'CANCELLED') {
                        Log::error('SurjoPay: Payment verification API reported ' . $verifiedStatus . ' for payment ID ' . $payment->id, $transactionData);
                        $payment->status = strtolower($verifiedStatus);
                        $payment->save();
                        return back()->withError('Payment is ' . $verifiedStatus . ' as per gateway.');
                    } else { // PENDING, INITIATED, etc.
                        Log::warning('SurjoPay: Payment verification API reported status ' . $verifiedStatus . ' for payment ID ' . $payment->id, $transactionData);
                        $payment->status = 'pending'; // Or map to your equivalent
                        $payment->save();
                        return back()->withInfo('Payment status is currently: ' . $verifiedStatus);
                    }
                } else {
                    Log::error('SurjoPay: Verification API response did not contain expected transaction data or status for order_id ' . $spOrderIdToVerify . ' and payment ID ' . $payment->id, $verifyData);
                    return back()->withError('Payment verification response unclear.');
                }
            } else {
                Log::error('SurjoPay: Verification API call failed or returned invalid data for payment ID ' . $payment->id, $verifyData ?? ['status_code' => $verifyResponse->status()]);
                return back()->withError('Failed to verify payment with the gateway.');
            }
        } catch (\Exception $e) {
            Log::error('SurjoPay: Exception during verification API call: ' . $e->getMessage(), ['payment_id' => $payment->id]);
            return back()->withError('An error occurred during payment verification.');
        }
    }
}
