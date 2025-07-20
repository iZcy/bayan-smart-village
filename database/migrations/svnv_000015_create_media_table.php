<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000015_create_media_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('village_id')->nullable();
            $table->uuid('community_id')->nullable();
            $table->uuid('sme_id')->nullable();
            $table->uuid('place_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['video', 'audio']);
            $table->enum('context', ['home', 'places', 'products', 'articles', 'gallery', 'global']);
            $table->string('file_url');
            $table->string('thumbnail_url')->nullable(); // For videos
            $table->integer('duration')->nullable(); // In seconds
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable(); // In bytes
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('autoplay')->default(false);
            $table->boolean('loop')->default(false);
            $table->boolean('muted')->default(true);
            $table->decimal('volume', 3, 2)->default(0.30); // 0.00 to 1.00
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable(); // Additional settings
            $table->timestamps();

            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->foreign('sme_id')->references('id')->on('smes')->onDelete('cascade');
            $table->foreign('place_id')->references('id')->on('places')->onDelete('cascade');

            $table->index(['village_id', 'context', 'type', 'is_active']);
            $table->index(['context', 'type', 'is_featured']);
            $table->index(['sort_order', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
};
