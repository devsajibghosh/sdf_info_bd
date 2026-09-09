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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->nullable();
            $table->string('file_path');
            $table->string('file_type');
            $table->double('size_in_byte')->nullable();
            $table->boolean('is_public')->default(false);
            $table->unsignedBigInteger('uploaded_by')->default(0);     
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
