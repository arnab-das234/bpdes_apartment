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
        Schema::create('building_cash_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('voucher_number');
            $table->string('title');
            $table->string('category')->default('Maintenance & Repairs');
            $table->decimal('amount', 15, 2);
            $table->date('bill_date');
            $table->uuid('responsible_person_id')->nullable();
            $table->string('responsible_person_name')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->string('fund_source')->default('Main Cash Collection Fund');
            $table->string('vendor_name')->nullable();
            $table->string('receipt_ref')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('DISBURSED'); // DISBURSED, APPROVED, PENDING
            $table->uuid('recorded_by');
            $table->uuid('approved_by')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('responsible_person_id')->references('id')->on('persons')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_cash_bills');
    }
};
