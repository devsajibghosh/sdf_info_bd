<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http; // Laravel's HTTP Client
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Support\Str;

class SslCommerzGateway extends Gateway implements PaymentGatewayInterface
{
    protected static $key = 'sslcommerz';
    protected static $image = 'sslcommerz.png';

    protected static $config = [
        'store_id' => 'Store ID',
        'store_passwd' => 'Store Password',
        'sandbox_mode' => 'Enable Sandbox Mode (yes/no)', // To switch between live and sandbox
    ];

    protected const SANDBOX_URL = 'https://sandbox.sslcommerz.com';
    protected const LIVE_URL = 'https://securepay.sslcommerz.com';

    public function getSupportedCurrencies(): array
    {
        // SSLCommerz primarily settles in BDT; other display currencies are converted by them.
        return ["BDT", "USD", "EUR", "GBP"];
    }

    protected function getApiBaseUrl(): string
    {
        $config = self::dbConfig();
        return (isset($config->sandbox_mode) && strtolower($config->sandbox_mode) === 'yes')
            ? self::SANDBOX_URL
            : self::LIVE_URL;
    }

    /**
     * Never let store_id/store_passwd reach the log files, even inside a larger payload.
     */
    protected function redactCredentials(array $data): array
    {
        foreach (['store_id', 'store_passwd'] as $sensitiveKey) {
            if (array_key_exists($sensitiveKey, $data)) {
                $data[$sensitiveKey] = '[REDACTED]';
            }
        }

        return $data;
    }

    protected function hasValidConfig($config): bool
    {
        return $config
            && !empty($config->store_id)
            && !empty($config->store_passwd);
    }

    public function create(Payment $payment): string|RedirectResponse
    {
        $config = self::dbConfig();

        if (!$this->hasValidConfig($config)) {
            Log::error('SSLCommerz: Configuration missing (store_id or store_passwd).');
            throw new \Exception('SSLCommerz gateway is not configured. Please contact support.');
        }

        $apiBaseUrl = $this->getApiBaseUrl();
        $endpoint = $apiBaseUrl . '/gwprocess/v4/api.php';

        // SSLCommerz tran_id must be unique and alphanumeric (max 30 chars).
        $transactionId = 'TXN' . $payment->id . Str::random(5);

        $postData = [
            'store_id' => $config->store_id,
            'store_passwd' => $config->store_passwd,
            'total_amount' => round($payment->amount, 2),
            'currency' => $payment->currency,
            'tran_id' => $transactionId,
            'success_url' => route('payment.notify', 'sslcommerz') . '?status=success',
            'fail_url' => route('payment.notify', 'sslcommerz') . '?status=fail',
            'cancel_url' => route('payment.notify', 'sslcommerz') . '?status=cancel',

            // Customer Information (Required)
            'cus_name' => $payment->user->name ?? 'Guest Donor',
            'cus_email' => $payment->user->email ?? 'donor@' . parse_url(config('app.url'), PHP_URL_HOST),
            'cus_add1' => $payment->user->address ?? 'Dhaka',
            'cus_city' => $payment->user->city ?? 'Dhaka',
            'cus_state' => $payment->user->state ?? 'Dhaka',
            'cus_postcode' => $payment->user->postcode ?? '1200',
            'cus_country' => $payment->user->country ?? 'Bangladesh',
            'cus_phone' => $payment->user->phone_number ?? $payment->donation->phone_number ?? '01700000000',

            // Product Information (Required)
            'product_name' => 'Donation for ' . generalSetting('site_title'),
            'product_category' => 'Donation',
            'product_profile' => 'general',

            'shipping_method' => 'NO',

            // Encrypted internal payment id, echoed back to us on every callback.
            'value_a' => encrypt($payment->id),
        ];

        Log::info('SSLCommerz: Create Payment Request Data for payment ID ' . $payment->id, $this->redactCredentials($postData));

        // SSLCommerz's sandbox/live endpoints can be slow to establish a TLS handshake;
        // Laravel's default 10s connect timeout with no retry was turning transient
        // network slowness into a hard "payment could not be started" failure for the
        // user. Retry a couple of times before giving up.
        $response = Http::asForm()
            ->connectTimeout(20)
            ->timeout(30)
            ->retry(2, 1000)
            ->post($endpoint, $postData);
        $responseData = $response->json();

        Log::info('SSLCommerz: Create Payment Response for payment ID ' . $payment->id, $responseData ?? ['raw_body' => $response->body()]);

        if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'SUCCESS') {
            $payment->meta = array_merge((array) $payment->meta, [
                'sslcz_sessionkey' => $responseData['sessionkey'] ?? null,
                'sslcz_tran_id' => $transactionId,
            ]);
            $payment->transaction_no = $transactionId;
            $payment->save();

            return redirect()->to($responseData['GatewayPageURL']);
        }

        $errorMessage = 'SSLCommerz: Payment initiation failed.';
        if (isset($responseData['failedreason'])) {
            $errorMessage .= ' Reason: ' . $responseData['failedreason'];
        } elseif (!$response->successful()) {
            $errorMessage .= ' HTTP Error: ' . $response->status();
        }

        Log::error($errorMessage, $responseData ?? []);
        throw new \Exception($errorMessage);
    }

    /**
     * Mark a payment (and its donation) as unsuccessful and send the donor to a
     * dedicated fail/cancel page instead of a fragile back()/session-flash redirect
     * (the visitor is arriving fresh from SSLCommerz's domain, so there is no
     * meaningful "previous page" to go back to).
     */
    protected function markUnsuccessful(Payment $payment, string $status, string $reason = ''): RedirectResponse
    {
        $payment->status = $status;
        if ($reason !== '') {
            $payment->meta = array_merge((array) $payment->meta, ['failure_reason' => $reason]);
        }
        $payment->save();

        if ($payment->donation) {
            $payment->donation->status = 2; // Rejected
            $payment->donation->save();
        }

        $donationId = $payment->donation ? encrypt($payment->donation->id) : null;

        if ($status === 'cancelled') {
            Log::info('SSLCommerz: Payment cancelled by user for payment ID ' . $payment->id);
            return $donationId
                ? redirect()->route('donation.cancelled', $donationId)
                : redirect()->route('home')->withInfo(__('Payment was cancelled.'));
        }

        Log::error('SSLCommerz: Payment failed for payment ID ' . $payment->id . '. Reason: ' . $reason);

        return $donationId
            ? redirect()->route('donation.failed', $donationId)
            : redirect()->route('home')->withError(__('Payment failed at the gateway. Please try again.'));
    }

    public function verify($request) // $request is Illuminate\Http\Request
    {
        Log::info('SSLCommerz: Verify Callback/Redirect Received.', $this->redactCredentials($request->all()));

        // Get our internal payment ID from value_a. Never trust the browser's
        // amount/status/currency fields directly — only SSLCommerz's own
        // server-to-server validation API response (below) is treated as truth.
        $encryptedPaymentId = $request->input('value_a');
        if (!$encryptedPaymentId) {
            Log::error('SSLCommerz: value_a (encrypted payment ID) missing in callback.');
            abort(404, 'Invalid payment request.');
        }

        try {
            $paymentId = decrypt($encryptedPaymentId);
        } catch (\Throwable $th) {
            Log::error('SSLCommerz: Failed to decrypt payment ID from value_a.', ['error' => $th->getMessage()]);
            abort(404, 'Invalid payment request.');
        }

        $payment = Payment::find($paymentId);

        if (!$payment) {
            Log::error('SSLCommerz: Payment record not found for decrypted ID.', ['decrypted_id' => $paymentId]);
            abort(404, 'Payment record not found.');
        }

        // Idempotent: a duplicate success callback (browser double-submit, user
        // pressing back, etc.) must never re-run balance crediting or SMS sending.
        if ($payment->status === 'success') {
            Log::info('SSLCommerz: Payment already marked as successful.', ['payment_id' => $payment->id]);
            return $this->paymentSuccess($request, $payment);
        }

        $routeStatus = $request->query('status');

        if ($routeStatus === 'fail') {
            return $this->markUnsuccessful($payment, 'failed', (string) $request->input('error', 'Declined at gateway'));
        }

        if ($routeStatus === 'cancel') {
            return $this->markUnsuccessful($payment, 'cancelled');
        }

        if (!$request->has('val_id')) {
            Log::warning('SSLCommerz: Missing val_id for verification.', ['status' => $routeStatus]);
            return $this->markUnsuccessful($payment, 'failed', 'Missing val_id from gateway callback');
        }

        $config = self::dbConfig();
        if (!$this->hasValidConfig($config)) {
            Log::error('SSLCommerz: Configuration missing for verification.');
            return $this->markUnsuccessful($payment, 'failed', 'Gateway not configured');
        }

        // Server-to-server validation — this is the only source of truth.
        $valId = $request->input('val_id');
        $apiBaseUrl = $this->getApiBaseUrl();
        $validationEndpoint = $apiBaseUrl . '/validator/api/validationserverAPI.php';

        $validationParams = [
            'val_id' => $valId,
            'store_id' => $config->store_id,
            'store_passwd' => $config->store_passwd,
            'format' => 'json',
        ];

        Log::info('SSLCommerz: Validation API Request for payment ID ' . $payment->id, $this->redactCredentials($validationParams));
        $validationResponse = Http::connectTimeout(20)->timeout(30)->retry(2, 1000)->get($validationEndpoint, $validationParams);
        $validationData = $validationResponse->json();
        Log::info('SSLCommerz: Validation API Response for payment ID ' . $payment->id, $validationData ?? ['raw_body' => $validationResponse->body()]);

        if (
            !$validationResponse->successful() ||
            !isset($validationData['status']) ||
            !in_array($validationData['status'], ['VALID', 'VALIDATED'], true)
        ) {
            $reason = $validationData['status'] ?? ('HTTP ' . $validationResponse->status());
            Log::error('SSLCommerz: Payment validation failed.', ['payment_id' => $payment->id, 'validation_response' => $validationData]);
            return $this->markUnsuccessful($payment, 'failed', 'Validation failed: ' . $reason);
        }

        $expectedTranId = $payment->meta['sslcz_tran_id'] ?? $payment->transaction_no;
        if ((string) $validationData['tran_id'] !== (string) $expectedTranId) {
            Log::error('SSLCommerz: Transaction ID mismatch during validation.', [
                'expected' => $expectedTranId,
                'received' => $validationData['tran_id'],
                'payment_id' => $payment->id,
            ]);
            return $this->markUnsuccessful($payment, 'failed', 'Transaction ID mismatch');
        }

        // SSLCommerz returns amount like "10.00"; compare as floats with a small tolerance.
        $validatedAmount = (float) ($validationData['amount'] ?? $validationData['currency_amount'] ?? 0);
        $originalAmount = round((float) $payment->amount, 2);

        if (abs($validatedAmount - $originalAmount) > 0.01) {
            Log::error('SSLCommerz: Amount mismatch during validation.', [
                'expected' => $originalAmount,
                'received' => $validatedAmount,
                'payment_id' => $payment->id,
            ]);
            return $this->markUnsuccessful($payment, 'failed', 'Amount mismatch');
        }

        if (strtoupper($validationData['currency']) !== strtoupper($payment->currency)) {
            Log::error('SSLCommerz: Currency mismatch during validation.', [
                'expected' => $payment->currency,
                'received' => $validationData['currency'],
                'payment_id' => $payment->id,
            ]);
            return $this->markUnsuccessful($payment, 'failed', 'Currency mismatch');
        }

        // All checks passed.
        $payment->paid_at = now();
        $payment->status = 'success';
        $payment->meta = array_merge((array) $payment->meta, ['sslcz_validation' => $validationData]);
        $payment->save();

        Log::info('SSLCommerz: Payment verified and marked as success for payment ID ' . $payment->id);

        return $this->paymentSuccess($request, $payment);
    }
}
