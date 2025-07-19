<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000010_create_offer_tags_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('offer_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->index('usage_count');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_tags');
    }
};
