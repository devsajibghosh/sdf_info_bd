<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AamarpayGateway extends Gateway implements PaymentGatewayInterface
{
    protected static $key = 'aamarpay';
    protected static $image = 'aamarpay.png'; // Ensure you have this image

    protected static $config = [
        'store_id' => 'Store ID',
        'signature_key' => 'Signature Key',
        'sandbox_mode' => 'Enable Sandbox Mode (yes/no)',
    ];

    // Aamarpay API URLs (confirm these from their latest documentation)
    protected const SANDBOX_URL = 'https://sandbox.aamarpay.com/jsonpost.php'; // Or /index.php or /request.php
    protected const LIVE_URL = 'https://secure.aamarpay.com/jsonpost.php';   // Or /index.php or /request.php

    // For payment verification/status check (if they have a separate endpoint)
    // protected const SANDBOX_VERIFY_URL = 'https://sandbox.aamarpay.com/api/v1/trxcheck/request.php';
    // protected const LIVE_VERIFY_URL = 'https://secure.aamarpay.com/api/v1/trxcheck/request.php';


    public function getSupportedCurrencies(): array
    {
        // Aamarpay primarily supports BDT. Check their docs for others.
        return ["BDT"];
    }

    protected function getApiBaseUrl(string $type = 'payment'): string
    {
        $config = self::dbConfig();
        $isSandbox = (isset($config->sandbox_mode) && strtolower($config->sandbox_mode) === 'yes');

        if ($type === 'payment') {
            return $isSandbox ? self::SANDBOX_URL : self::LIVE_URL;
        }
        // Uncomment and use if they have a separate verification endpoint
        // if ($type === 'verify') {
        //     return $isSandbox ? self::SANDBOX_VERIFY_URL : self::LIVE_VERIFY_URL;
        // }
        return $isSandbox ? self::SANDBOX_URL : self::LIVE_URL; // Default to payment URL
    }

    /**
     * Generate the signature for Aamarpay request.
     * The fields and order are critical. Refer to Aamarpay documentation.
     * @param array $fields
     * @param string $signatureKey
     * @return string
     */
    protected function generateRequestSignature(array $fields, string $signatureKey): string
    {
        // Example field order - THIS MUST MATCH AAMARPAY'S DOCUMENTATION EXACTLY
        // Often, it's store_id, tran_id, amount, currency, then your signature key.
        // For example: $fields['store_id'].$fields['tran_id'].$fields['amount'].$fields['currency'].$signatureKey
        // This is a placeholder - replace with the actual documented fields and order.

        $stringToHash = "";
        // Common fields included in signature generation (confirm with Aamarpay docs)
        $signatureFields = ['store_id', 'tran_id', 'success_url', 'fail_url', 'cancel_url', 'amount', 'currency'];
        foreach ($signatureFields as $field) {
            if (isset($fields[$field])) {
                $stringToHash .= $fields[$field];
            }
        }
        $stringToHash .= $signatureKey;

        return md5($stringToHash);
    }

    /**
     * Generate the signature for Aamarpay response/callback verification.
     * @param array $responseData
     * @param string $signatureKey
     * @return string
     */
    protected function generateResponseSignature(array $responseData, string $signatureKey): string
    {
        // Example field order for response - THIS MUST MATCH AAMARPAY'S DOCUMENTATION
        // Often it's mer_txnid, amount_original, pay_status, then your signature key.
        // This is a placeholder.
        $stringToHash = "";
        // Common fields in response signature (confirm with Aamarpay docs)
        // The 'pg_service_charge_bdt' and 'amount_original' might be specific
        $responseSignatureFields = ['mer_txnid', 'pay_status', 'amount_original', 'pg_service_charge_bdt', 'pg_card_risklevel']; // Adjust as per docs
        foreach ($responseSignatureFields as $field) {
            if (isset($responseData[$field])) {
                $stringToHash .= $responseData[$field];
            }
        }
        $stringToHash .= $signatureKey;

        return md5($stringToHash);
    }


    public function create(Payment $payment): string // Returns redirect URL or HTML form
    {
        $config = self::dbConfig();
        if (!$config || !isset($config->store_id) || !isset($config->signature_key)) {
            Log::error('Aamarpay: Configuration missing (store_id or signature_key).');
            throw new \Exception('Aamarpay gateway not configured properly.');
        }

        $paymentUrl = $this->getApiBaseUrl('payment');
        $transactionId = 'AAP' . $payment->id . Str::random(5); // Unique transaction ID

        $postData = [
            'store_id' => $config->store_id,
            'tran_id' => $transactionId,
            'success_url' => route('payment.notify', ['gateway' => self::$key, 'status' => 'success']),
            'fail_url' => route('payment.notify', ['gateway' => self::$key, 'status' => 'fail']),
            'cancel_url' => route('payment.notify', ['gateway' => self::$key, 'status' => 'cancel']),
            'amount' => round($payment->amount, 2),
            'currency' => $payment->currency, // Typically "BDT"

            // Customer Information
            'cus_name' => $payment->user->name ?? 'N/A',
            'cus_email' => $payment->user->email ?? 'guest@example.com',
            'cus_phone' => $payment->user->phone ?? '01000000000',
            'cus_add1' => $payment->user->address ?? 'N/A',
            'cus_city' => $payment->user->city ?? 'N/A',
            'cus_country' => $payment->user->country ?? 'Bangladesh',

            // Product Information
            'desc' => 'Payment for ' . generalSetting('site_title') . ' Order #' . $payment->id,
            // 'product_name' => 'Service Payment', // Alternative description field
            // 'product_category' => 'Digital Service',

            // Optional parameters to pass through and receive back
            'opt_a' => encrypt($payment->id), // Your internal payment ID
            // 'opt_b' => '',
            // 'opt_c' => '',
            // 'opt_d' => '',
        ];

        // Generate request signature
        // The signature field name might be 'signature_key' or 'signature' or 'verify_sign' etc.
        // Aamarpay usually names it `signature_key` in the request.
        $postData['signature_key'] = $this->generateRequestSignature($postData, $config->signature_key);

        Log::info('Aamarpay: Create Payment Request Data for payment ID ' . $payment->id, $postData);

        // Aamarpay's payment initiation might not be a JSON API post like SSLCommerz.
        // Often, you redirect the user to their payment page with GET parameters,
        // or POST to their page which then redirects.
        // If their endpoint is a page that expects a POST and then redirects,
        // you might need to return an HTML form that auto-submits.
        // However, if `jsonpost.php` takes parameters and redirects, then this is fine.

        // Let's assume `jsonpost.php` or a similar endpoint processes these POST fields and
        // directly redirects or returns a URL to redirect to.
        // Some Aamarpay versions might return JSON with a redirect URL.

        // Option 1: If Aamarpay endpoint directly redirects or returns JSON with a redirect URL
        // $response = Http::asForm()->post($paymentUrl, $postData);
        // $responseData = $response->json();
        // Log::info('Aamarpay: Create Payment Response', $responseData ?? ['raw_body' => $response->body()]);
        // if ($response->successful() && isset($responseData['payment_url'])) {
        //     $payment->meta = ['aamarpay_tran_id' => $transactionId];
        //     $payment->transaction_id = $transactionId;
        //     $payment->save();
        //     return $responseData['payment_url'];
        // }

        // Option 2: Construct the redirect URL with GET parameters (Common for older Aamarpay versions)
        // This is more likely if their endpoint is not a JSON API.
        // The payment URL is often the base URL with parameters appended.
        $redirectUrl = $paymentUrl . '?' . http_build_query($postData);
        $payment->meta = ['aamarpay_tran_id' => $transactionId];
        $payment->transaction_id = $transactionId;
        $payment->save();
        Log::info('Aamarpay: Redirecting to: ' . $redirectUrl);
        return $redirectUrl; // Redirect using GET


        // Option 3: If Aamarpay requires auto-submitting form (Less ideal but sometimes necessary)
        // If the previous options don't work, you might need to build an HTML form.
        // $htmlForm = '<form action="'.$paymentUrl.'" method="POST" id="aamarpay_form">';
        // foreach ($postData as $key => $value) {
        //     $htmlForm .= '<input type="hidden" name="'.$key.'" value="'.htmlspecialchars($value).'">';
        // }
        // $htmlForm .= '</form><script>document.getElementById("aamarpay_form").submit();</script>';
        // return $htmlForm; // Controller needs to render this HTML.

        // Log::error('Aamarpay: Payment initiation failed.', $responseData ?? ['raw_body' => $response->body()]);
        // throw new \Exception('Aamarpay payment initiation failed.');
    }

    public function verify($request) // $request is Illuminate\Http\Request
    {
        Log::info('Aamarpay: Verify Callback Received.', $request->all());
        $config = self::dbConfig();
        if (!$config || !isset($config->store_id) || !isset($config->signature_key)) {
            Log::error('Aamarpay: Configuration missing for verification.');
            return back()->withError('Gateway configuration error.');
        }

        $responseData = $request->all(); // Get all callback parameters

        // Retrieve our internal payment ID
        $encryptedPaymentId = $responseData['opt_a'] ?? null;
        if (!$encryptedPaymentId) {
            Log::error('Aamarpay: opt_a (encrypted payment ID) missing in callback.');
            return back()->withError('Payment verification failed: Invalid request.');
        }

        try {
            $paymentId = decrypt($encryptedPaymentId);
        } catch (\Throwable $th) {
            Log::error('Aamarpay: Failed to decrypt payment ID from opt_a.', ['opt_a' => $encryptedPaymentId, 'error' => $th->getMessage()]);
            return back()->withError('Payment verification failed: Invalid transaction data.');
        }

        $payment = Payment::find($paymentId);
        if (!$payment) {
            Log::error('Aamarpay: Payment record not found for decrypted ID.', ['decrypted_id' => $paymentId]);
            return back()->withError('Payment record not found.');
        }

        if ($payment->status === 'success') {
            Log::info('Aamarpay: Payment already marked as successful.', ['payment_id' => $payment->id]);
            return redirect()->route('payment.status.page', ['trx' => $payment->trx ?? $payment->id, 'status' => 'success'])
                ->with('success', 'Payment was already confirmed.');
        }

        // Verify the signature sent by Aamarpay
        // The signature field in response is often `verify_sign` or `signature`
        $receivedSignature = $responseData['verify_sign'] ?? ($responseData['signature'] ?? null);
        if (!$receivedSignature) {
            Log::error('Aamarpay: Signature missing in callback for payment ID ' . $payment->id, $responseData);
            return back()->withError('Payment verification failed: Signature missing.');
        }

        $expectedSignature = $this->generateResponseSignature($responseData, $config->signature_key);

        if ($receivedSignature !== $expectedSignature) {
            Log::error('Aamarpay: Signature mismatch for payment ID ' . $payment->id, [
                'received' => $receivedSignature,
                'expected' => $expectedSignature,
                'data' => $responseData
            ]);
            $payment->status = 'failed'; // Or a specific "tampered" status
            $payment->save();
            return back()->withError('Payment verification failed: Invalid signature.');
        }

        // Check payment status from Aamarpay
        $paymentStatus = $responseData['pay_status'] ?? 'Failed'; // e.g., "Successful", "Failed", "Cancelled", "Pending"

        if (strtolower($paymentStatus) === 'successful') {
            // Additional checks: amount, currency
            $aamarpayAmount = (float) ($responseData['amount_original'] ?? $responseData['amount'] ?? 0);
            $originalAmount = round((float) $payment->amount, 2);
            $aamarpayCurrency = $responseData['currency'] ?? '';

            if (abs($aamarpayAmount - $originalAmount) > 0.01) {
                Log::error('Aamarpay: Amount mismatch for payment ID ' . $payment->id, [
                    'expected' => $originalAmount,
                    'received' => $aamarpayAmount
                ]);
                $payment->status = 'failed'; // Or "validation_failed"
                $payment->save();
                return back()->withError('Payment verification failed: Amount mismatch.');
            }

            if (strtoupper($aamarpayCurrency) !== strtoupper($payment->currency)) {
                Log::error('Aamarpay: Currency mismatch for payment ID ' . $payment->id, [
                    'expected' => $payment->currency,
                    'received' => $aamarpayCurrency
                ]);
                $payment->status = 'failed'; // Or "validation_failed"
                $payment->save();
                return back()->withError('Payment verification failed: Currency mismatch.');
            }

            // All checks passed
            $payment->paid_at = now();
            $payment->status = 'success';
            $payment->transaction_id = $responseData['mer_txnid'] ?? $payment->transaction_id; // Aamarpay's transaction ID
            $payment->meta = array_merge((array) $payment->meta, ['aamarpay_response' => $responseData]);
            $payment->save();

            Log::info('Aamarpay: Payment verified and marked as success for payment ID ' . $payment->id);
            return $this->paymentSuccess($request, $payment);
        } elseif (strtolower($paymentStatus) === 'failed') {
            Log::error('Aamarpay: Payment failed at gateway for payment ID ' . $payment->id, $responseData);
            $payment->status = 'failed';
            $payment->save();
            $reason = $responseData['reason'] ?? ($responseData['pg_error_code_desc'] ?? 'Unknown error from gateway');
            return redirect()->route('payment.status.page', ['trx' => $payment->trx ?? $payment->id, 'status' => 'failed'])
                ->with('error', 'Payment failed. Reason: ' . $reason);
        } elseif (strtolower($paymentStatus) === 'cancelled') {
            Log::info('Aamarpay: Payment cancelled by user for payment ID ' . $payment->id, $responseData);
            $payment->status = 'cancelled';
            $payment->save();
            return redirect()->route('payment.status.page', ['trx' => $payment->trx ?? $payment->id, 'status' => 'cancelled'])
                ->with('info', 'Payment was cancelled.');
        } else {
            // Handle other statuses like 'Pending' if necessary
            Log::warning('Aamarpay: Payment status is \'' . $paymentStatus . '\' for payment ID ' . $payment->id, $responseData);
            $payment->status = 'pending'; // Or map to your equivalent status
            $payment->save();
            return back()->withInfo('Payment is currently: ' . $paymentStatus);
        }
    }
}
