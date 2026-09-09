<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restores a migration missing from history: `phone_number` was added to
 * `users` directly on the live DB at some point (same gap pattern fixed for
 * donations/payments in 2025_06_08_170000_reconcile_donations_and_payments_schema).
 * Guarded so it's a no-op where the column already exists and builds
 * correctly from scratch on a fresh install.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'phone_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone_number')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Intentionally left as a no-op: relied upon by application code.
    }
};
