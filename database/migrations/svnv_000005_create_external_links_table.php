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
            // Add village_id to link external links to villages
            $table->uuid('village_id')->nullable();

            // Add place_id to link external links to specific places
            $table->uuid('place_id')->nullable();

            $table->string('label');
            $table->text('url');
            $table->string('icon')->nullable();
            $table->string('subdomain');
            $table->string('slug');
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->integer('click_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Ensure unique subdomain.domain/l/slug combinations
            $table->unique(['subdomain', 'slug']);
            $table->index(['subdomain', 'slug']);
            $table->index('is_active');

            // Add indexes for better performance
            $table->index('village_id');
            $table->index('place_id');
            $table->index(['village_id', 'is_active']);
            $table->index(['place_id', 'is_active']);

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
