<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sme_tourism_places', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('village_id')->nullable();
            $table->string('name');
            $table->text('description');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone_number')->nullable();
            $table->string('image_url')->nullable();
            $table->uuid('category_id');
            $table->json('custom_fields')->nullable();
            $table->timestamps();

            $table->index('village_id');
            $table->index(['village_id', 'category_id']);
            $table->foreign('village_id')->references('id')->on('villages')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sme_tourism_places');
    }
};
