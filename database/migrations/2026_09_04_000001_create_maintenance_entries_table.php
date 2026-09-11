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
        Schema::create('maintenance_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('unit_id')->index();
            $table->uuid('person_id')->nullable()->index();
            
            $table->integer('billing_year');
            $table->integer('billing_month'); // 1-12
            $table->string('month_name'); // e.g. "January 2026"
            $table->string('title')->default('Monthly Maintenance');
            $table->boolean('is_backlog')->default(false); // Flags historical backlog / arrears charge
            
            $table->decimal('base_maintenance', 15, 2)->default(0.00);
            $table->decimal('backlog_amount', 15, 2)->default(0.00);
            $table->decimal('late_fee', 15, 2)->default(0.00);
            $table->decimal('utility_charge', 15, 2)->default(0.00);
            $table->decimal('total_due', 15, 2)->default(0.00);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            
            $table->string('status')->default('Unpaid'); // Paid, Partial, Unpaid, Overdue
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('payment_mode')->nullable(); // Cash, UPI, Bank Transfer, Cheque
            $table->string('reference_number')->nullable();
            $table->text('remarks')->nullable();
            $table->uuid('recorded_by')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('set null');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE maintenance_entries ENABLE ROW LEVEL SECURITY');
            DB::statement('CREATE POLICY maintenance_entries_isolation ON maintenance_entries USING (organization_id = current_org_id())');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_entries');
    }
};
