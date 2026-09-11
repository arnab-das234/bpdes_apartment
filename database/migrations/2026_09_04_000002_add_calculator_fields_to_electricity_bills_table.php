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
        Schema::table('electricity_bills', function (Blueprint $table) {
            $table->integer('previous_reading')->nullable()->after('submeter_number');
            $table->integer('current_reading')->nullable()->after('previous_reading');
            $table->integer('units_consumed')->nullable()->after('current_reading');
            
            $table->integer('common_meter_total_units')->nullable()->after('units_consumed');
            $table->decimal('energy_charge', 12, 2)->nullable()->after('common_meter_total_units');
            $table->decimal('electricity_duty', 12, 2)->nullable()->after('energy_charge');
            $table->decimal('fixed_charge', 12, 2)->nullable()->after('electricity_duty');
            $table->decimal('meter_rent', 12, 2)->nullable()->after('fixed_charge');
            $table->decimal('lpsc_exclusion', 12, 2)->nullable()->after('meter_rent');
            $table->decimal('gross_bill_amount', 12, 2)->nullable()->after('lpsc_exclusion');
            
            $table->decimal('recommended_rate_per_unit', 10, 4)->nullable()->after('gross_bill_amount');
            $table->decimal('personal_charge', 12, 2)->nullable()->after('recommended_rate_per_unit');
            $table->decimal('common_share', 12, 2)->nullable()->after('personal_charge');
            $table->decimal('total_payable', 12, 2)->nullable()->after('common_share');
            $table->integer('total_flats_count')->nullable()->after('total_payable');
            $table->integer('billing_cycle_months')->default(3)->after('total_flats_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('electricity_bills', function (Blueprint $table) {
            $table->dropColumn([
                'previous_reading',
                'current_reading',
                'units_consumed',
                'common_meter_total_units',
                'energy_charge',
                'electricity_duty',
                'fixed_charge',
                'meter_rent',
                'lpsc_exclusion',
                'gross_bill_amount',
                'recommended_rate_per_unit',
                'personal_charge',
                'common_share',
                'total_payable',
                'total_flats_count',
                'billing_cycle_months',
            ]);
        });
    }
};
