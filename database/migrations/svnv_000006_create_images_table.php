<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('village_id')->nullable();
            $table->uuid('place_id');
            $table->string('image_url');
            $table->string('caption')->nullable();
            $table->timestamps();

            $table->index('village_id');
            $table->index(['village_id', 'place_id']);
            $table->foreign('village_id')->references('id')->on('villages')->onDelete('set null');
            $table->foreign('place_id')->references('id')->on('sme_tourism_places')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
