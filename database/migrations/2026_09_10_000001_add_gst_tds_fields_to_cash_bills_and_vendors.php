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
            if (!Schema::hasColumn('building_cash_bills', 'gst_type')) {
                $table->string('gst_type')->default('none')->after('amount'); // none, cgst_sgst, igst
            }
            if (!Schema::hasColumn('building_cash_bills', 'gst_rate')) {
                $table->decimal('gst_rate', 5, 2)->default(0.00)->after('gst_type');
            }
            if (!Schema::hasColumn('building_cash_bills', 'gst_amount')) {
                $table->decimal('gst_amount', 15, 2)->default(0.00)->after('gst_rate');
            }
            if (!Schema::hasColumn('building_cash_bills', 'tds_section')) {
                $table->string('tds_section')->default('none')->after('gst_amount'); // none, 194C, 194J
            }
            if (!Schema::hasColumn('building_cash_bills', 'tds_rate')) {
                $table->decimal('tds_rate', 5, 2)->default(0.00)->after('tds_section');
            }
            if (!Schema::hasColumn('building_cash_bills', 'tds_amount')) {
                $table->decimal('tds_amount', 15, 2)->default(0.00)->after('tds_rate');
            }
            if (!Schema::hasColumn('building_cash_bills', 'net_payable')) {
                $table->decimal('net_payable', 15, 2)->default(0.00)->after('tds_amount');
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'gst_registered')) {
                $table->boolean('gst_registered')->default(false)->after('gstin');
            }
            if (!Schema::hasColumn('vendors', 'default_tds_section')) {
                $table->string('default_tds_section')->nullable()->after('gst_registered');
            }
            if (!Schema::hasColumn('vendors', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('default_tds_section');
            }
            if (!Schema::hasColumn('vendors', 'bank_account_no')) {
                $table->string('bank_account_no')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('vendors', 'bank_ifsc')) {
                $table->string('bank_ifsc')->nullable()->after('bank_account_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_cash_bills', function (Blueprint $table) {
            $table->dropColumn(['gst_type', 'gst_rate', 'gst_amount', 'tds_section', 'tds_rate', 'tds_amount', 'net_payable']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['gst_registered', 'default_tds_section', 'bank_name', 'bank_account_no', 'bank_ifsc']);
        });
    }
};
