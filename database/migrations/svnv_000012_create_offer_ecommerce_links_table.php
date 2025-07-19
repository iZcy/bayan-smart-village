<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000012_create_offer_ecommerce_links_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('offer_ecommerce_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('offer_id');
            $table->enum('platform', ['tokopedia', 'shopee', 'tiktok_shop', 'bukalapak', 'blibli', 'lazada', 'instagram', 'whatsapp', 'website', 'other']);
            $table->string('store_name')->nullable();
            $table->text('product_url');
            $table->decimal('price_on_platform', 10, 2)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('click_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
            $table->index(['offer_id', 'platform', 'is_active']);
            $table->index(['offer_id', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_ecommerce_links');
    }
};
