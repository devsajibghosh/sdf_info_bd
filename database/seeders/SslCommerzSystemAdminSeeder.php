<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates a dedicated "SSLCommerz" admin record used only as the
 * approved_by reference for donations that SSLCommerz auto-approves
 * after a successful payment (see GatewayHelper::addBalanceToUser,
 * which looks this admin up by username matching the sslcommerz
 * PaymentGateway "key"). This admin never logs in.
 */
class SslCommerzSystemAdminSeeder extends Seeder
{
    public function run(): void
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'system.local';

        Admin::firstOrCreate(
            ['username' => 'sslcommerz'],
            [
                'name' => 'SSLCommerz',
                'email' => 'sslcommerz@' . $host,
                'password' => Hash::make(Str::random(40)),
            ]
        );
    }
}
