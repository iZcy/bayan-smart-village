<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_links', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Village relationship - now the primary way to determine domain
            $table->uuid('village_id')->nullable();

            // Place relationship (optional, links to specific places within a village)
            $table->uuid('place_id')->nullable();

            $table->string('label');
            $table->text('url');
            $table->string('icon')->nullable();

            // Only slug is needed now - subdomain comes from village
            $table->string('slug');
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->integer('click_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Ensure unique slug combinations
            // For village links: village_id + slug must be unique
            // For apex links: slug must be unique where village_id is null
            $table->unique(['village_id', 'slug']);

            $table->index(['village_id', 'is_active']);
            $table->index(['place_id', 'is_active']);
            $table->index('slug');
            $table->index('is_active');

            // Foreign key constraints
            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            $table->foreign('place_id')->references('id')->on('sme_tourism_places')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_links');
    }
};
