<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique()->index(); // For subdomain routing
            $table->text('description')->nullable();
            $table->string('domain')->nullable(); // Custom domain if needed
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('image_url')->nullable();
            $table->json('settings')->nullable(); // Village-specific settings
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('established_at')->nullable();
            $table->timestamps();

            // Indexes for search functionality
            $table->index(['name', 'is_active']);
            $table->index(['slug', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
