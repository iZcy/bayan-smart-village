<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000005_create_smes_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('smes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('community_id');
            $table->uuid('place_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('type', ['service', 'product']);
            $table->string('owner_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('business_hours')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->foreign('place_id')->references('id')->on('places')->onDelete('set null');
            $table->unique(['community_id', 'slug']);
            $table->index(['community_id', 'type', 'is_active']);
            $table->index('place_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('smes');
    }
};
