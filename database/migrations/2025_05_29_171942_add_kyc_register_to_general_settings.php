<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->tinyInteger('user_registration')->default(1)->comment('User registration done');
            $table->tinyInteger('kyc')->default(1)->comment('KYC is required for new users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn('user_registration');
            $table->dropColumn('kyc');
        });
    }
};
