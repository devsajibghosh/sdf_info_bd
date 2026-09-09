<?php

namespace App\Helpers;

use App\Models\Admin;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use stdClass;

trait GatewayHelper
{
    public static function dbConfig()
    {
        $model = PaymentGateway::where('key', static::$key)->first();

        return $model?->config ?? new stdClass;
    }

    public static function filledConfig()
    {
        $model = PaymentGateway::where('key', static::$key)->first();

        if (!$model)
            return static::$config;

        static::$staticConfig = static::$config;

        static::$config = $model->config;

        return static::getConfigInput(true);
    }


    public static function getConfigInput($returnFromDb = false)
    {
        $inputs = [];

        foreach (($returnFromDb ? static::$staticConfig : static::$config) as $confKey => $confLabel) {
            $label = $returnFromDb ? static::$staticConfig[$confKey] : '';

            $value = $returnFromDb ? static::$config?->$confKey : '';

            $inputs[] = '<div class="form-group">
                <label for="' . htmlspecialchars($confKey) . '" class="form-label">' . __($label) . '</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($value) . '" name="config[' . htmlspecialchars($confKey) . ']" />
            </div>';
        }

        return $inputs;
    }

    public static function paymentGateways($key = null, $fromDb = false, $createIfNotExists = false)
    {
        $classes = getClassesInNamespace('App\Services\Gateways');

        $gateways = [];

        foreach ($classes as $gatewayClassName) {
            $class = "App\\Services\\Gateways\\$gatewayClassName";

            $gateways[] = [
                'key' => $class::getKey(),
                'image' => $class::getImage(),
                'config' => $class::getConfig(),
                'class' => $class
            ];

            if (!$fromDb) {
                $gateways['config_input'] = $class::getConfigInput();
            }
        }

        if ($createIfNotExists) {
            self::createIfNotExists($gateways);
        }

        if ($key) {
            return collect($gateways)->where('key', $key)->first();
        }

        return $gateways;
    }

    public static function getKey()
    {
        return static::$key;
    }

    public static function getImage()
    {
        return static::$image;
    }

    public static function getConfig()
    {
        return static::$config;
    }

    protected function paymentSuccess($request,$payment)
    {
        $this->addBalanceToUser($payment);

        $isManualGateway = $payment?->paymentGateway?->manual == 1;

        // Determine the correct redirect URL based on the user's authentication status
        if (!auth()->check()) {
            $redirectUrl = route('download.receipt', encrypt($payment->donation->id));
        } else {
            $redirectUrl = route('user.payment.download', $payment->donation->id);
        }

        $redirectUrl = route('donation.success', [encrypt($payment->donation->id), $isManualGateway]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($redirectUrl);
        }

        // For a standard web request, perform a normal redirect with a success message
        if (auth()->check()) {
            return redirect($redirectUrl)->withSuccess(__('Payment successful'));
        }

        // For guests in a standard request, just redirect
        return redirect($redirectUrl);
    }

    /**
     * Credit the user's balance, flip the donation to "approved" and send the
     * confirmation SMS — exactly once per payment, no matter how many times a
     * gateway callback fires (browser back button, duplicate POST, retried
     * webhook, etc). A row lock + a persisted "balance_credited" flag on the
     * payment's meta column make this safe under concurrent requests.
     */
    private function addBalanceToUser(Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (!$locked || !empty($locked->meta['balance_credited'])) {
                return;
            }

            if ($locked->status !== 'success') {
                return;
            }

            if ($locked->user_id) {
                $user = User::whereKey($locked->user_id)->lockForUpdate()->first();
                if ($user) {
                    $user->balance += $locked->amount;
                    $user->save();
                }
            }

            if ($locked->donation) {
                $locked->donation->status = 1; // Approved

                if (!$locked->donation->approved_by) {
                    $locked->donation->approved_by = $this->resolveGatewaySystemAdminId($locked);
                }

                $locked->donation->save();
            }

            $locked->meta = array_merge((array) $locked->meta, ['balance_credited' => true]);
            $locked->save();

            $this->sendPaymentSuccessSms($locked);
        });
    }

    /**
     * When a gateway auto-approves a donation (no admin clicked "Approve"),
     * attribute it to a dedicated system admin named after that gateway
     * (e.g. an admin with username "sslcommerz" for the SSLCommerz
     * gateway), instead of leaving approved_by blank. Falls back to null
     * if no such admin has been created for the gateway yet.
     */
    private function resolveGatewaySystemAdminId(Payment $payment): ?int
    {
        $gatewayKey = $payment->paymentGateway?->key;

        if (!$gatewayKey) {
            return null;
        }

        return Admin::where('username', $gatewayKey)->value('id');
    }

    /**
     * Auto-notify the donor/member by SMS so admins never have to manually
     * confirm a successful online donation. Delegates to DonationSmsService,
     * the same service the manual admin-approval flow uses, so the message
     * and donor-name resolution stay identical between both paths.
     */
    private function sendPaymentSuccessSms(Payment $payment)
    {
        $recipient = $payment->user ?? $payment->donation?->donor;

        (new \App\Services\DonationSmsService())->sendSuccessSms(
            $recipient?->phone_number,
            (float) $payment->amount,
            $recipient,
            $payment->id
        );
    }

    private static function createIfNotExists($gatewaysFromClass)
    {
        // foreach ($gatewaysFromClass as $gatewayFromClass) {
        //     if (empty($gatewayFromClass['key'])) {
        //         continue;
        //     }

        //     $dbEntry = PaymentGateway::firstOrCreate(
        //         ['key' => $gatewayFromClass['key']],
        //         [
        //             'name' => ucfirst($gatewayFromClass['key']),
        //             'image' => $gatewayFromClass['image'],
        //             'config' => array_combine(
        //                 array_keys($gatewayFromClass['config']),
        //                 array_map(fn() => '-------------', $gatewayFromClass['config'])
        //             )
        //         ]
        //     );
        // }
    }
}