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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_links');
    }
};
