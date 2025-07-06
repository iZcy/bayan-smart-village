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
            $table->uuid('place_id');
            $table->string('label');
            $table->string('url');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('place_id')->references('id')->on('sme_tourism_places')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_links');
    }
};
