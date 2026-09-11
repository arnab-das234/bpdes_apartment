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
        // 1. Bank Accounts
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('bank_name');
            $table->string('branch');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('ifsc');
            $table->string('account_type')->default('Savings'); // Savings, Current
            $table->date('opening_date')->nullable();
            $table->string('purpose')->nullable(); // General, Sinking Fund, Reserve
            $table->text('cheque_signing_rules')->nullable(); // e.g. Jointly signed by President and Treasurer
            $table->jsonb('documents_json')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // 2. Ledger Accounts
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('name');
            $table->string('type'); // Asset, Liability, Equity, Revenue, Expense
            $table->string('code')->nullable();
            $table->uuid('parent_id')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('ledger_accounts')->onDelete('cascade');
        });

        // 3. Transactions (Replaces lightweight journal entries with proper bookkeeping)
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('ledger_account_id');
            $table->uuid('project_id')->nullable(); // Nullable if building expense
            $table->uuid('proposal_id')->nullable(); // Linked for audit tracing
            $table->uuid('unit_id')->nullable(); // Linked if maintenance collection
            
            $table->string('voucher_number')->nullable();
            $table->string('receipt_number')->nullable();
            $table->date('transaction_date');
            $table->string('type'); // DEBIT, CREDIT
            $table->decimal('amount', 15, 2);
            $table->string('payment_mode')->default('Cash'); // Cash, Cheque, Bank Transfer, UPI
            $table->string('reference_number')->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->text('description');
            $table->uuid('recorded_by');
            $table->uuid('approved_by')->nullable();
            $table->string('verification_status')->default('Pending'); // Pending, Verified, Approved

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('ledger_account_id')->references('id')->on('ledger_accounts')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // 4. Vendors
        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->string('pan')->nullable();
            $table->string('gstin')->nullable();
            $table->string('service_category')->nullable(); // Plumbing, Security, Elevators, Paint
            $table->string('status')->default('active'); // active, blacklisted

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // 5. Assets
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('name');
            $table->string('category')->nullable(); // Generator, Lift, Water Pump
            $table->string('location')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_amount', 15, 2)->default(0.00);
            $table->date('warranty_expiry')->nullable();
            $table->text('amc_details')->nullable();
            $table->string('depreciation_method')->default('Straight Line');
            $table->decimal('current_value', 15, 2)->default(0.00);
            $table->string('status')->default('active');
            $table->string('responsible_person')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // 6. Inventory Items
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->string('unit')->default('pcs'); // pcs, kgs, liters, meters
            $table->integer('min_stock_level')->default(5);

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // 7. Inventory Transactions
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('inventory_item_id');
            $table->string('type'); // Purchase, Issue, Return, Adjustment
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->uuid('reference_id')->nullable(); // Project ID, Task ID, or Vendor ID
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
        });

        // 8. Meetings
        Schema::create('meetings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('meeting_type'); // AGM, SGM, Committee, Emergency
            $table->date('date');
            $table->time('time');
            $table->string('venue');
            $table->text('agenda');
            $table->string('notice_doc_path')->nullable();
            $table->string('minutes_doc_path')->nullable();
            $table->boolean('quorum_present')->default(true);

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // 9. Resolutions
        Schema::create('resolutions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('meeting_id');
            $table->string('resolution_number');
            $table->text('subject');
            $table->string('proposed_by')->nullable();
            $table->string('seconded_by')->nullable();
            
            // Voting Details
            $table->integer('votes_for')->default(0);
            $table->integer('votes_against')->default(0);
            $table->integer('votes_abstained')->default(0);
            $table->string('result')->default('passed'); // passed, failed
            $table->date('effective_date')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
        });

        // 10. Documents
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('name');
            $table->string('category'); // Registration Certificate, Declaration, Form A, Form 1, Bye-Laws, AGM Minutes
            $table->string('file_path');
            $table->text('description')->nullable();
            $table->uuid('uploaded_by')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
        });

        // 11. Complaints
        Schema::create('complaints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('unit_id')->nullable();
            $table->uuid('person_id')->nullable();
            $table->string('category')->nullable(); // Plumber, Electrician, Lift, Leakage
            $table->text('description');
            $table->string('priority')->default('Medium'); // Low, Medium, High, Critical
            $table->string('assigned_staff_name')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0.00);
            $table->decimal('actual_cost', 12, 2)->default(0.00);
            $table->string('status')->default('reported'); // reported, assigned, resolved, closed
            
            $table->string('before_photo')->nullable();
            $table->string('after_photo')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
        });

        // 12. Security Residents
        Schema::create('security_residents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('unit_id');
            $table->string('name');
            $table->string('mobile');
            $table->string('resident_type')->default('Owner'); // Owner, Tenant, Staff, Guard
            $table->string('rfid_tag')->nullable()->unique();
            $table->string('access_status')->default('active'); // active, blocked

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });

        // 13. Security Logs
        Schema::create('security_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('unit_id')->nullable();
            $table->string('visitor_name');
            $table->string('visitor_mobile')->nullable();
            $table->string('entry_type')->default('visitor'); // visitor, delivery, resident, staff
            $table->string('vehicle_number')->nullable();
            $table->timestamp('entry_time')->useCurrent();
            $table->timestamp('exit_time')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
        });

        // 14. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('user_id')->nullable();
            $table->string('auditable_type');
            $table->uuid('auditable_id');
            $table->string('action'); // created, updated, deleted, state_transition
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('reason')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Enable Row-Level Security (RLS) on all these tables (only if using PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            $tables = [
                'bank_accounts', 'ledger_accounts', 'transactions', 'vendors', 'assets',
                'inventory_items', 'inventory_transactions', 'meetings', 'resolutions',
                'documents', 'complaints', 'security_residents', 'security_logs', 'audit_logs'
            ];
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
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('security_residents');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('resolutions');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('ledger_accounts');
        Schema::dropIfExists('bank_accounts');
    }
};
