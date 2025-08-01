<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000011_create_offer_tags_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('offer_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('village_id');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            $table->index('village_id');
            $table->index('usage_count');
            
            // Unique constraints scoped to village
            $table->unique(['village_id', 'name']);
            $table->unique(['village_id', 'slug']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_tags');
    }
};
