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
        Schema::table('building_cash_bills', function (Blueprint $table) {
            $table->uuid('proposal_id')->nullable()->after('unit_id');
            $table->uuid('project_id')->nullable()->after('proposal_id');
            $table->uuid('milestone_id')->nullable()->after('project_id');

            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('milestone_id')->references('id')->on('project_milestones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_cash_bills', function (Blueprint $table) {
            $table->dropForeign(['proposal_id']);
            $table->dropForeign(['project_id']);
            $table->dropForeign(['milestone_id']);

            $table->dropColumn(['proposal_id', 'project_id', 'milestone_id']);
        });
    }
};
