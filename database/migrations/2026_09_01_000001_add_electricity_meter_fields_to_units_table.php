<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('electricity_connection_type')->default('own_meter')->after('outstanding_amount');
            $table->string('meter_number')->nullable()->after('electricity_connection_type');
            $table->string('submeter_number')->nullable()->after('meter_number');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn([
                'electricity_connection_type',
                'meter_number',
                'submeter_number',
            ]);
        });
    }
};
