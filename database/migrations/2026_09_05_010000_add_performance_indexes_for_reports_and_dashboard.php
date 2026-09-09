<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive, non-destructive index changes only. Justified against actual
 * EXPLAIN output on the live database (see docs/QUERY_OPTIMIZATION_REPORT.md):
 *
 * - donations: the approved/pending/rejected admin lists all run
 *   WHERE status = ? ORDER BY created_at DESC LIMIT n (via ->success()/
 *   ->pending()/->rejected()->latest()->paginate()). The existing
 *   single-column status index lets MySQL find matching rows but still
 *   needs a filesort to satisfy the ORDER BY (confirmed via EXPLAIN:
 *   "Using filesort" on ~3.4k matching rows). A composite (status,
 *   created_at) index serves both the WHERE and ORDER BY in one pass and
 *   fully subsumes the single-column status index for status-only
 *   lookups, so the old index is dropped to avoid an unnecessary
 *   duplicate write cost.
 *
 * - site_visits: the admin dashboard's today/this_week/this_month/
 *   this_year visit counts filter only on visit_date. The only existing
 *   index with visit_date is composite (ip, visit_date), which EXPLAIN
 *   shows MySQL cannot use as a seek for a visit_date-only filter (it
 *   scans the full index, ~34k rows, "Using index" but type=index, not
 *   range/ref). A standalone visit_date index is added; the (ip,
 *   visit_date) composite is kept as-is since it serves the
 *   once-per-pageview SiteVisitMiddleware existence check on a
 *   different access pattern (ip + visit_date equality).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            if (!$this->indexExists('donations', 'donations_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'donations_status_created_at_index');
            }

            // Not every environment has this index (it's only created when the
            // `status` column itself is added by an earlier migration), so the
            // drop is skipped rather than assumed.
            if ($this->indexExists('donations', 'donations_status_index')) {
                $table->dropIndex('donations_status_index');
            }
        });

        Schema::table('site_visits', function (Blueprint $table) {
            if (!$this->indexExists('site_visits', 'site_visits_visit_date_index')) {
                $table->index('visit_date', 'site_visits_visit_date_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            if (!$this->indexExists('donations', 'donations_status_index')) {
                $table->index('status', 'donations_status_index');
            }

            if ($this->indexExists('donations', 'donations_status_created_at_index')) {
                $table->dropIndex('donations_status_created_at_index');
            }
        });

        Schema::table('site_visits', function (Blueprint $table) {
            if ($this->indexExists('site_visits', 'site_visits_visit_date_index')) {
                $table->dropIndex('site_visits_visit_date_index');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }

        return false;
    }
};
