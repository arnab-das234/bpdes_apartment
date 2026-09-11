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
            $table->string('bill_document_path')->nullable()->after('receipt_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_cash_bills', function (Blueprint $table) {
            $table->dropColumn('bill_document_path');
        });
    }
};
