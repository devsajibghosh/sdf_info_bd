<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data-only migration (no schema changes): re-activates the old donor-facing
 * manual payment gateways (bkash-payment, rocket, nagad, bank) that migration
 * 2026_09_05_000000_setup_sslcommerz_as_only_online_gateway.php deactivated
 * (status = 0) when SSLCommerz was introduced.
 *
 * SSLCommerz stays the only *automatic* gateway donors see on the public
 * donate pages (PaymentGateway::scopeHidden() now excludes manual=1 rows, so
 * this flip cannot resurrect them there or in the logged-in user donate
 * page). Re-activating them only makes them selectable again in the admin's
 * manual-donation tools (Create Manual Donation dropdown, Manual Payment
 * Gateways list), restoring the old manual-entry workflow alongside
 * SSLCommerz rather than instead of it.
 *
 * Scoped to manual = 1 only, unlike the 2026_09_05 migration's update
 * clause, so this never touches the broken/duplicate "bkash payment" rows
 * (ids 47/48, manual = 0) that migration also happened to deactivate —
 * those are automatic-flagged leftovers and must stay inactive, or they'd
 * resurface as fake "automatic" choices next to SSLCommerz via scopeHidden().
 *
 * Fully reversible: down() flips the status back to 0.
 */
return new class extends Migration
{
    protected array $donorFacingManualKeys = ['bkash-payment', 'rocket', 'nagad', 'bank'];

    public function up(): void
    {
        DB::table('payment_gateways')
            ->whereIn('key', $this->donorFacingManualKeys)
            ->where('for_admin', 0)
            ->where('manual', 1)
            ->update(['status' => 1]);
    }

    public function down(): void
    {
        DB::table('payment_gateways')
            ->whereIn('key', $this->donorFacingManualKeys)
            ->where('for_admin', 0)
            ->where('manual', 1)
            ->update(['status' => 0]);
    }
};
