<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Product Images table
        Schema::create('product_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->string('image_url');
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // Product Tags table
        Schema::create('product_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->index('usage_count');
        });

        // Product Tag Pivot table
        Schema::create('product_tag_pivot', function (Blueprint $table) {
            $table->uuid('product_id');
            $table->uuid('product_tag_id');
            $table->timestamps();

            $table->primary(['product_id', 'product_tag_id']);
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_tag_id')->references('id')->on('product_tags')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tag_pivot');
        Schema::dropIfExists('product_tags');
        Schema::dropIfExists('product_images');
    }
};
