<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MySQL's TEXT column caps out at 65,535 bytes (~10-15k words of HTML,
     * depending on markup density) — not enough for the "20,000+ word
     * article" requirement. LONGTEXT supports up to 4GB. Raw SQL is used
     * (rather than Blueprint::change(), which needs doctrine/dbal, not
     * installed here) via MODIFY COLUMN, which alters the type in place
     * without touching existing row data.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE blog_posts MODIFY body LONGTEXT NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE blog_posts MODIFY body TEXT NOT NULL');
        }
    }
};
