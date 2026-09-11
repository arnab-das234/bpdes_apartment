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
        // 1. Create Master Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category');
            $table->timestamps();
        });

        // 2. Create Role-Permission pivot table
        Schema::create('role_permission', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->uuid('permission_id');

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            
            $table->primary(['role_id', 'permission_id']);
        });

        // 3. Drop JSON column from roles
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->jsonb('permissions')->nullable();
        });

        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
    }
};
