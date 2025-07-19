<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000009_create_images_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('village_id')->nullable();
            $table->uuid('community_id')->nullable();
            $table->uuid('sme_id')->nullable();
            $table->uuid('place_id')->nullable();
            $table->string('image_url');
            $table->string('caption')->nullable();
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->foreign('sme_id')->references('id')->on('smes')->onDelete('cascade');
            $table->foreign('place_id')->references('id')->on('places')->onDelete('cascade');
            $table->index(['community_id', 'sort_order']);
            $table->index(['sme_id', 'sort_order']);
            $table->index('place_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('images');
    }
};
