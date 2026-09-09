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
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->bigInteger('views')->default(0)->comment('total views of this post');
            $table->tinyInteger('status')->default(0)->comment('0=unpublished, 1=published');
            $table->text('seo_content')->nullable();
            $table->string('seo_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('views');
            $table->dropColumn('status');
            $table->dropColumn('seo_content');
            $table->dropColumn('seo_image');
        });
    }
};
