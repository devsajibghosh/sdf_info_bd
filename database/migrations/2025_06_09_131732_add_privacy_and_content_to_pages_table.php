<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restores migrations missing from history: `privacy` and `content` were
 * added to `pages` directly on the live DB at some point (same gap pattern
 * fixed for donations/payments in
 * 2025_06_08_170000_reconcile_donations_and_payments_schema, for
 * users.phone_number in 2025_06_05_135224, and for payment_gateways.manual
 * / for_admin in 2025_06_08_211445). Guarded so it's a no-op where the
 * columns already exist and builds correctly from scratch on a fresh
 * install.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'privacy')) {
                $table->tinyInteger('privacy')->default(0)->comment('Is a privacy-style single-content page (e.g. return/refund policy)');
            }
            if (!Schema::hasColumn('pages', 'content')) {
                $table->longText('content')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally left as a no-op: relied upon by application code.
    }
};
