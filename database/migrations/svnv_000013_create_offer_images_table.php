<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000013_create_offer_images_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('offer_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('offer_id');
            $table->string('image_url');
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
            $table->index(['offer_id', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_images');
    }
};
