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
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->default(0);
            $table->integer('admin_id')->nullable()->default(0)->comment('will act as member there');
            $table->boolean('status')->comment('please check the status const class');
            $table->dateTime('call_time')->default(now())->nullable();
            $table->decimal('amount', 28, 8)->default(0);
            $table->date('approx_date')->nullable()->comment('this is for affirmed donate');
            $table->date('next_call_date')->nullable()->comment('This is what, the user asked to call another date');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
