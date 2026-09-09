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
        Schema::create('donation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('admin_id')->default(0)->nullable();
            $table->decimal('min_donation_amount', 28, 8)->nullable();
            $table->string('image')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_categories');
    }
};
