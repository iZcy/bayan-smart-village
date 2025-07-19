<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration: svnv_000013_create_offer_tag_pivot_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('offer_tag_pivot', function (Blueprint $table) {
            $table->uuid('offer_id');
            $table->uuid('offer_tag_id');
            $table->timestamps();

            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
            $table->foreign('offer_tag_id')->references('id')->on('offer_tags')->onDelete('cascade');
            $table->primary(['offer_id', 'offer_tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_tag_pivot');
    }
};
