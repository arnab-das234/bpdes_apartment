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
        Schema::table('proposals', function (Blueprint $table) {
            $table->date('execution_date')->nullable();
            $table->date('deadline')->nullable();
            $table->uuid('assigned_secretary_id')->nullable();
            $table->string('document_path')->nullable();
            $table->text('justification')->nullable();
            $table->jsonb('involved_members')->nullable();

            $table->foreign('assigned_secretary_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropForeign(['assigned_secretary_id']);
            
            $table->dropColumn([
                'execution_date',
                'deadline',
                'assigned_secretary_id',
                'document_path',
                'justification',
                'involved_members'
            ]);
        });
    }
};
