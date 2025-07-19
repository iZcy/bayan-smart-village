<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000004_create_categories_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('village_id');
            $table->string('name');
            $table->enum('type', ['service', 'product']);
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();

            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            $table->index(['village_id', 'type']);
            $table->unique(['village_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
