<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional, admin/user-editable English name used for receipts and other
     * documents where non-Latin script cannot be reliably shaped/printed.
     * Nullable and never auto-populated: the original `name` is never
     * overwritten or guessed from.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
        });

        Schema::table('donors', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name_en');
        });

        Schema::table('donors', function (Blueprint $table) {
            $table->dropColumn('name_en');
        });
    }
};
