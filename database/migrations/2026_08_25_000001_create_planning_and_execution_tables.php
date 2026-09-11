<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Proposals Table
        Schema::create('proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('title');
            $table->text('description');
            $table->decimal('budget', 15, 2);
            $table->string('status')->default('DRAFT'); // DRAFT, SUBMITTED, VERIFICATION, VERIFIED, COMMITTEE_REVIEW, RECOMMENDED, PRESIDENT_REVIEW, APPROVED, REJECTED, DEFERRED, RETURNED
            $table->jsonb('checklist')->nullable(); // Dynamic checklist gates
            $table->uuid('created_by');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });

        // 2. Proposal Decisions Table (Audit log for President actions)
        Schema::create('proposal_decisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('proposal_id');
            $table->uuid('decided_by');
            $table->string('decision'); // APPROVED, REJECTED, DEFERRED
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('cascade');
            $table->foreign('decided_by')->references('id')->on('users')->onDelete('restrict');
        });

        // 3. Projects Table
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('proposal_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->decimal('budget', 15, 2);
            $table->string('status')->default('Planning'); // Planning, Active, Completed, Closed
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('set null');
        });

        // 4. Tasks Table
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('project_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->string('status')->default('Not Started'); // Not Started, Assigned, Accepted, In Progress, Submitted, Under Verification, Correction Required, Verified, Completed, Closed
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });

        // 5. Finance Journal Entries Table (Double-Entry Ledger)
        Schema::create('finance_journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('project_id')->nullable(); // Nullable for building-level finance
            $table->uuid('proposal_id')->nullable(); // Tracing back to proposal
            $table->string('reference')->nullable();
            $table->string('type'); // DEBIT or CREDIT
            $table->decimal('amount', 15, 2);
            $table->string('account_name'); // e.g. 'Project Fund', 'Maintenance Reserve'
            $table->string('description');
            $table->uuid('recorded_by');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('set null');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
        });

        // Enable Row-Level Security (RLS) on all domain tables (only if using PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            $tables = ['proposals', 'proposal_decisions', 'projects', 'tasks', 'finance_journal_entries'];
            foreach ($tables as $tbl) {
                DB::statement("ALTER TABLE {$tbl} ENABLE ROW LEVEL SECURITY");
                DB::statement("CREATE POLICY {$tbl}_isolation ON {$tbl} USING (organization_id = current_org_id())");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_journal_entries');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('proposal_decisions');
        Schema::dropIfExists('proposals');
    }
};
