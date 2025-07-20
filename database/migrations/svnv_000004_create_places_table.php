<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000003_create_places_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('places', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('village_id');
            $table->uuid('category_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone_number')->nullable();
            $table->string('image_url')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();

            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index('slug');
            $table->index('category_id');
            $table->unique(['village_id', 'slug']);
            $table->index('village_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('places');
    }
};
