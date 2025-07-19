<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000008_create_external_links_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('external_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('village_id')->nullable();
            $table->uuid('community_id')->nullable();
            $table->uuid('sme_id')->nullable();
            $table->string('label');
            $table->text('url');
            $table->string('icon')->nullable();
            $table->string('slug');
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->integer('click_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->foreign('sme_id')->references('id')->on('smes')->onDelete('cascade');
            $table->unique(['village_id', 'slug']);
            $table->unique(['community_id', 'slug']);
            $table->index(['community_id', 'is_active']);
            $table->index(['sme_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('external_links');
    }
};
