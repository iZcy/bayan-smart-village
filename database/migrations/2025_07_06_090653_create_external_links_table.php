<?php
// database/migrations/2025_07_06_090653_create_external_links_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('place_id');
            $table->string('label');
            $table->text('url');
            $table->string('icon')->nullable();
            $table->string('subdomain'); // Required from the start
            $table->string('slug'); // Required from the start
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('place_id')->references('id')->on('sme_tourism_places')->onDelete('cascade');

            // Ensure unique subdomain.domain/l/slug combinations
            $table->unique(['subdomain', 'slug']);
            $table->index(['subdomain', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_links');
    }
};
