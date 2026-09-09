<?php

namespace App\Services\Gateways;

use App\Helpers\GatewayHelper;
use App\Models\PaymentGateway;

class Gateway
{
    use GatewayHelper;

    protected static $key;

    protected static $image;
    
    protected static $config = [];

    protected static $staticConfig = [];

}