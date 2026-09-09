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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->integer('expense_category_id')->default(0);
            $table->decimal('amount', 28, 8)->default(0);
            $table->string('note')->nullable();
            $table->integer('expense_by')->default(0)->comment('an admin/member id');
            $table->integer('expense_for')->default(0)->comment('an admin/member id - expense for whom');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
