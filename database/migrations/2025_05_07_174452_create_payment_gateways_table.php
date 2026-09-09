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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(1)->comment('1=active,0=insactive');
            $table->string('name');
            $table->string('image')->nullable();
            $table->boolean('is_test_mode')->default(false);
            $table->json('config')->nullable();
            $table->string('currency', 40)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0); // For UI ordering
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
