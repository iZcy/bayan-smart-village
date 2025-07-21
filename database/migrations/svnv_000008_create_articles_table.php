<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000008_create_articles_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('village_id')->nullable();
            $table->uuid('community_id')->nullable();
            $table->uuid('sme_id')->nullable();
            $table->uuid('place_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->longText('content');
            $table->string('cover_image_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->foreign('sme_id')->references('id')->on('smes')->onDelete('cascade');
            $table->foreign('place_id')->references('id')->on('places')->onDelete('cascade');

            $table->index(['village_id', 'slug']);
            $table->index(['community_id', 'slug']);
            $table->index(['sme_id', 'is_published']);
            $table->index(['village_id', 'is_published']);

            $table->unique(['village_id', 'community_id', 'sme_id', 'place_id', 'slug'], 'articles_scope_slug_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
    }
};
