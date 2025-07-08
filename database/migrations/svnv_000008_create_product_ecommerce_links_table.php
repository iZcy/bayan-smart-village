<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_ecommerce_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->enum('platform', [
                'tokopedia',
                'shopee',
                'tiktok_shop',
                'bukalapak',
                'blibli',
                'lazada',
                'instagram',
                'whatsapp',
                'website',
                'other'
            ]);
            $table->string('store_name')->nullable();
            $table->text('product_url');
            $table->decimal('price_on_platform', 10, 2)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('click_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'platform', 'is_active']);
            $table->index(['product_id', 'sort_order']);

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_ecommerce_links');
    }
};
