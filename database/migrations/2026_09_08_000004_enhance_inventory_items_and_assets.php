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
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_items', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(0)->after('min_stock_level');
            }
            if (!Schema::hasColumn('inventory_items', 'reserved_quantity')) {
                $table->integer('reserved_quantity')->default(0)->after('stock_quantity');
            }
            if (!Schema::hasColumn('inventory_items', 'unit_cost')) {
                $table->decimal('unit_cost', 12, 2)->default(0.00)->after('reserved_quantity');
            }
            if (!Schema::hasColumn('inventory_items', 'allocated_budget')) {
                $table->decimal('allocated_budget', 15, 2)->default(0.00)->after('unit_cost');
            }
            if (!Schema::hasColumn('inventory_items', 'storage_location')) {
                $table->string('storage_location')->nullable()->after('allocated_budget');
            }
            if (!Schema::hasColumn('inventory_items', 'remarks')) {
                $table->text('remarks')->nullable()->after('storage_location');
            }
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_transactions', 'proposal_id')) {
                $table->uuid('proposal_id')->nullable()->after('reference_id');
            }
            if (!Schema::hasColumn('inventory_transactions', 'project_id')) {
                $table->uuid('project_id')->nullable()->after('proposal_id');
            }
            if (!Schema::hasColumn('inventory_transactions', 'milestone_id')) {
                $table->uuid('milestone_id')->nullable()->after('project_id');
            }
            if (!Schema::hasColumn('inventory_transactions', 'recorded_by')) {
                $table->uuid('recorded_by')->nullable()->after('milestone_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'reserved_quantity', 'unit_cost', 'allocated_budget', 'storage_location']);
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn(['proposal_id', 'project_id', 'milestone_id', 'recorded_by']);
        });
    }
};
