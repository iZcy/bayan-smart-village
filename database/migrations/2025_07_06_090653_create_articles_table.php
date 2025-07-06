<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->longText('content');
            $table->string('cover_image_url')->nullable();
            $table->uuid('place_id')->nullable();
            $table->timestamps();

            $table->foreign('place_id')->references('id')->on('sme_tourism_places')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
