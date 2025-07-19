<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000006_create_offers_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sme_id');
            $table->uuid('category_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('price_unit', 50)->nullable();
            $table->decimal('price_range_min', 10, 2)->nullable();
            $table->decimal('price_range_max', 10, 2)->nullable();
            $table->enum('availability', ['available', 'out_of_stock', 'seasonal', 'on_demand'])->default('available');
            $table->json('seasonal_availability')->nullable();
            $table->string('primary_image_url')->nullable();
            $table->json('materials')->nullable();
            $table->json('colors')->nullable();
            $table->json('sizes')->nullable();
            $table->json('features')->nullable();
            $table->json('certification')->nullable();
            $table->string('production_time', 100)->nullable();
            $table->integer('minimum_order')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0);
            $table->timestamps();

            $table->foreign('sme_id')->references('id')->on('smes')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->unique(['sme_id', 'slug']);
            $table->index(['sme_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('offers');
    }
};
