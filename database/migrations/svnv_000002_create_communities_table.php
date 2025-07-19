<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000002_create_communities_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('village_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('domain')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            $table->unique(['village_id', 'slug']);
            $table->index(['village_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('communities');
    }
};
