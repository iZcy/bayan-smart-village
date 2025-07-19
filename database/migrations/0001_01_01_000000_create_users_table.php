<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the existing users table
        Schema::table('users', function (Blueprint $table) {
            // Change id to UUID
            $table->dropColumn('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->first();

            // Add role and scope fields
            $table->enum('role', ['super_admin', 'village_admin', 'community_admin', 'sme_admin'])
                ->default('sme_admin')
                ->after('password');

            // Scope relationships - nullable because super admin has no scope
            $table->uuid('village_id')->nullable()->after('role');
            $table->uuid('community_id')->nullable()->after('village_id');
            $table->uuid('sme_id')->nullable()->after('community_id');

            // Active status
            $table->boolean('is_active')->default(true)->after('sme_id');

            // Foreign key constraints
            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->foreign('sme_id')->references('id')->on('smes')->onDelete('cascade');

            // Indexes for performance
            $table->index(['role', 'is_active']);
            $table->index(['village_id', 'role']);
            $table->index(['community_id', 'role']);
            $table->index(['sme_id', 'role']);
        });

        // Update sessions table to use UUID for user_id
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->index()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['village_id']);
            $table->dropForeign(['community_id']);
            $table->dropForeign(['sme_id']);

            $table->dropColumn([
                'role',
                'village_id',
                'community_id',
                'sme_id',
                'is_active'
            ]);
        });

        // Revert users table to auto-increment id
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->id()->first();
        });

        // Revert sessions table
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->index()->after('id');
        });
    }
};
