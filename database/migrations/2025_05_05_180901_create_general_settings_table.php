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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_title')->default('Site title');
            $table->string('site_description')->nullable()->default('A short description of the site.');
            $table->string('site_logo')->nullable(); 
            $table->string('site_favicon')->nullable(); 
            $table->string('site_email')->nullable()->default('contact@domain.com'); 
            $table->string('site_phone')->nullable();
            $table->string('software_version')->default('1.0.0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
