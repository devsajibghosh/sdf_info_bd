<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restores migrations missing from history: `manual` and `for_admin` were
 * added to `payment_gateways` directly on the live DB at some point (same
 * gap pattern fixed for donations/payments in
 * 2025_06_08_170000_reconcile_donations_and_payments_schema, and for
 * users.phone_number in 2025_06_05_135224). Guarded so it's a no-op where
 * the columns already exist and builds correctly from scratch on a fresh
 * install.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_gateways', 'manual')) {
                $table->boolean('manual')->default(0);
            }
            if (!Schema::hasColumn('payment_gateways', 'for_admin')) {
                $table->boolean('for_admin')->default(0);
            }
        });
    }

    public function down(): void
    {
        // Intentionally left as a no-op: relied upon by application code.
    }
};
