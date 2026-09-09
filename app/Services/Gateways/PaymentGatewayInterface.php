<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;

interface PaymentGatewayInterface
{

    public function create(Payment $payment): string|RedirectResponse;

    public function verify($mixed);

    public function getSupportedCurrencies(): array;
}
