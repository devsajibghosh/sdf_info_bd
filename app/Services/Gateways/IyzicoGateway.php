<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\CheckoutForm;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Options;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Model\Locale;
use Iyzipay\Model\Currency;
use Iyzipay\Request\RetrieveCheckoutFormRequest;

class IyzicoGateway extends Gateway implements PaymentGatewayInterface
{
    protected Options $options;

    protected static $key = 'iyzico';

    protected static $image = 'iyzico.png';

    protected static $config = [
        'api_key' => 'Api Key',
        'secret_key' => 'Secret Key',
    ];

    public function __construct()
    {
        $this->options = new Options();
        $this->options->setApiKey(config('services.iyzico.api_key'));
        $this->options->setSecretKey(config('services.iyzico.secret_key'));
        $this->options->setBaseUrl(config('services.iyzico.base_url'));
    }

    public function getSupportedCurrencies(): array
    {
        return ["TL", "USD", "EUR", "GBP", "RUB", "CHF", "NOK"];
    }


    public function create(Payment $payment): string
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey(env('IYZICO_API_KEY'));
        $options->setSecretKey(env('IYZICO_SECRET_KEY'));
        $options->setBaseUrl("https://sandbox-api.iyzipay.com");

        // Mocked cart & user data (you should load this from your DB)
        $cart = [
            'id' => 'B67832',
            'sub_total' => 100,
            'grand_total' => 110,
            'cart_currency_code' => 'TRY',      // Use string currency code
            'customer_first_name' => 'John',
            'customer_last_name' => 'Doe',
            'created_at' => now(),
        ];

        $user = [
            'phone' => '+905350000000',
            'email' => 'john@example.com',
            'created_at' => now(),
        ];


        $requestIyzico = new CreateCheckoutFormInitializeRequest();
        $requestIyzico->setLocale(Locale::EN);
        $requestIyzico->setPrice($payment->amount);
        $requestIyzico->setPaidPrice($payment->amount);
        $requestIyzico->setCurrency(Currency::USD);
        $requestIyzico->setBasketId($cart['id']);
        $requestIyzico->setPaymentGroup(PaymentGroup::PRODUCT);
        $requestIyzico->setCallbackUrl(route('payment.notify', 'iyzico'));
        // $requestIyzico->setEnabledInstallments([1, 2, 3, 6, 9]);

        $buyer = new Buyer();
        $buyer->setId($payment->user_id);
        $buyer->setName($payment->user->first_name);
        $buyer->setSurname($payment->user->last_name);
        $buyer->setGsmNumber($user['phone']);
        $buyer->setEmail($payment->user->email);
        $buyer->setIdentityNumber("11111111111");
        $buyer->setLastLoginDate((string) $cart['created_at']);
        $buyer->setRegistrationDate((string) $user['created_at']);
        $buyer->setRegistrationAddress('Address in earth');
        $buyer->setIp(request()->ip());
        $buyer->setCity('Istanbul');
        $buyer->setCountry('Turkey');
        $buyer->setZipCode('1010');
        $requestIyzico->setBuyer($buyer);

        $shippingAddress = new Address();
        $shippingAddress->setContactName($cart['customer_first_name'] . ' ' . $cart['customer_last_name']);
        $shippingAddress->setCity('Istanbul');
        $shippingAddress->setCountry('Turkey');
        $shippingAddress->setAddress('Address in earth');
        $shippingAddress->setZipCode('1010');
        $requestIyzico->setShippingAddress($shippingAddress);

        $billingAddress = new Address();
        $billingAddress->setContactName('Mr Johnson');
        $billingAddress->setCity('Istanbul');
        $billingAddress->setCountry('Turkey');
        $billingAddress->setAddress('Billing Address');
        $billingAddress->setZipCode('1010');
        $requestIyzico->setBillingAddress($billingAddress);

        $product = [
            'id'        => $payment->id,
            'name'      => 'Payment to ' . generalSetting('site_title'),
            'total'     => $payment->amount,
            // 'stockable' => true,
        ];

        $basketItem = new BasketItem();
        $basketItem->setId($product['id']);
        $basketItem->setName('Payment to ' . generalSetting('site_title'));
        $basketItem->setCategory1('DIGITAL_GOODS');
        // $basketItem->setCategory2($product['stockable'] ? 'PHYSICAL_GOODS' : 'DIGITAL_GOODS');
        $basketItem->setItemType(BasketItemType::VIRTUAL);
        $basketItem->setPrice($payment->amount);

        $requestIyzico->setBasketItems([$basketItem]);

        $checkoutFormInitialize = CheckoutFormInitialize::create($requestIyzico, $options);

        $token = $checkoutFormInitialize->getToken();

        $payment->meta = [
            'token' => $token
        ];
        $payment->save();

        $iframeContent = $checkoutFormInitialize->getCheckoutFormContent(); // can be used  in a blade view
        $paymentPageUrl = $checkoutFormInitialize->getPaymentPageUrl();

        return redirect()->away($paymentPageUrl);
    }

    /**
     * Verify the payment and update user information 
     * @param mixed $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function verify($request)
    {
        $token = $request->input('token');

        if (!$token) {
            return response()->json(['error' => 'Missing token'], 400);
        }

        $payment = Payment::where('meta->token', $token)->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        if ($payment->user_id) {
            \Auth::loginUsingId($payment->user_id);
        }


        $options = new Options();
        $options->setApiKey(config('services.iyzico.api_key'));
        $options->setSecretKey(config('services.iyzico.secret_key'));
        $options->setBaseUrl("https://sandbox-api.iyzipay.com");

        $retrieveRequest = new RetrieveCheckoutFormRequest();
        $retrieveRequest->setLocale(Locale::EN);
        $retrieveRequest->setToken($token);

        $checkoutForm = CheckoutForm::retrieve($retrieveRequest, $options);

        if ($checkoutForm->getStatus() === 'success') {
            $paymentStatus = $checkoutForm->getPaymentStatus();

            if ($paymentStatus == 'SUCCESS') {
                $payment->status = 'paid';
                $payment->paid_at = now();
                $payment->meta = array_merge($payment->meta ?? [], [
                    'conversation_id' => $checkoutForm->getConversationId(),
                ]);
                
                $payment->save();

                return $this->paymentSuccess($request, $payment);
            }

            Log::warning('Iyzico checkout retrieved with a non-success payment status', [
                'payment_id' => $payment->id,
                'status'     => $paymentStatus,
            ]);

            return response()->json(['error' => 'Payment was not successful'], 402);
        }

        Log::warning('Iyzico checkout form retrieval did not return success', [
            'payment_id' => $payment->id,
        ]);

        return response()->json(['error' => 'Unable to verify payment'], 502);
    }
}
