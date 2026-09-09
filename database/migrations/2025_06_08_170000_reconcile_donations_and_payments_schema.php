<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Reconciles this repo's migration history with columns that exist on the
 * live database but were never captured in a committed migration (added
 * directly to the DB at some point). Every change here is additive/guarded
 * so it is a no-op against a DB that already has these columns (production)
 * and correctly builds the schema from scratch on a fresh install (new
 * environment, CI, staging).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            if (!Schema::hasColumn('donations', 'donor_id')) {
                $table->integer('donor_id')->default(0)->nullable()->index();
            }
            if (!Schema::hasColumn('donations', 'approved_by')) {
                $table->integer('approved_by')->default(0)->nullable();
            }
            if (!Schema::hasColumn('donations', 'admin_id')) {
                $table->integer('admin_id')->default(0)->nullable();
            }
            if (!Schema::hasColumn('donations', 'is_manual')) {
                $table->boolean('is_manual')->default(0)->nullable();
            }
            if (!Schema::hasColumn('donations', 'field_collection')) {
                $table->boolean('field_collection')->default(0)->nullable();
            }
            if (!Schema::hasColumn('donations', 'status')) {
                // Named to match the index the later
                // add_performance_indexes_for_reports_and_dashboard migration
                // expects to find and replace with a composite index.
                $table->tinyInteger('status')->default(0)->nullable()->index();
            }
            if (Schema::hasColumn('donations', 'phone_number') && !$this->indexExists('donations', 'donations_phone_number_index')) {
                $table->index('phone_number', 'donations_phone_number_index');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'donor_id')) {
                $table->integer('donor_id')->nullable()->index();
            }
        });

        // payments.user_id was created NOT NULL (foreignId->constrained()),
        // but the guest-donation flow never sets it. Make it nullable with a
        // safe default so guest payments can be inserted without a user.
        if (Schema::hasColumn('payments', 'user_id')) {
            DB::statement('ALTER TABLE payments MODIFY user_id BIGINT UNSIGNED NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
        // Intentionally left as a no-op: these columns/indexes already exist
        // on production and are relied upon by application code, so
        // reversing this migration would break the live schema.
    }

    private function indexExists(string $table, string $indexName): bool
    {
        // doctrine/dbal isn't installed in this project, so query the
        // information schema directly instead of Schema::getIndexes().
        $result = DB::select(
            'SELECT COUNT(1) as cnt FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $indexName]
        );

        return ($result[0]->cnt ?? 0) > 0;
    }
};
