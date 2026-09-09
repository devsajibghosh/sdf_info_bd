<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data-only migration (no schema changes): makes SSLCommerz the only
 * donor-facing online payment gateway.
 *
 * - Ensures a "sslcommerz" row exists in payment_gateways (created inactive
 *   with placeholder credentials if missing, so a fresh install never ships
 *   with a real-looking store_id/store_passwd baked into a migration file).
 * - Deactivates (status = 0) the old donor-facing manual gateways
 *   (bkash-payment, rocket, nagad, bank) and any broken/duplicate rows that
 *   used to fall through to ManualPaymentGateway.
 * - Never touches admin-only internal bookkeeping gateways (cash, goods,
 *   bank-balance, sdf-taken-loan — for_admin = 1) and never deletes any
 *   historical Payment/Donation record.
 *
 * Fully reversible: down() only flips status flags back, it performs no
 * destructive operation.
 */
return new class extends Migration
{
    protected array $donorFacingManualKeys = ['bkash-payment', 'rocket', 'nagad', 'bank', 'bkash payment'];

    public function up(): void
    {
        $exists = DB::table('payment_gateways')->where('key', 'sslcommerz')->exists();

        if (!$exists) {
            DB::table('payment_gateways')->insert([
                'key' => 'sslcommerz',
                'name' => 'SSLCommerz',
                'image' => 'sslcommerz.png',
                'manual' => 0,
                'for_admin' => 0,
                'status' => 0, // left inactive until real credentials are entered in the admin panel
                'config' => json_encode([
                    'store_id' => '',
                    'store_passwd' => '',
                    'sandbox_mode' => 'yes',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('payment_gateways')
            ->whereIn('key', $this->donorFacingManualKeys)
            ->where('for_admin', 0)
            ->update(['status' => 0]);
    }

    public function down(): void
    {
        DB::table('payment_gateways')
            ->whereIn('key', $this->donorFacingManualKeys)
            ->where('for_admin', 0)
            ->update(['status' => 1]);
    }
};
