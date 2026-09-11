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
        // 1. Enable UUID extension in PostgreSQL (only if using PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        }

        // 2. Create Organizations table
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('subdomain')->unique();
            $table->string('registration_type')->nullable(); // Society, Apartment Owners Association, Cooperative, Other
            $table->string('registration_number')->nullable();
            $table->date('registration_date')->nullable();
            $table->string('registration_authority')->nullable();
            $table->string('registration_act')->nullable(); // e.g. West Bengal Apartment Ownership Act, 1972
            $table->string('pan')->nullable();
            $table->string('tan')->nullable();
            $table->string('gstin')->nullable();
            $table->string('official_email')->nullable();
            $table->string('official_mobile')->nullable();
            $table->string('status')->default('active'); // active, suspended, under_registration
            $table->jsonb('settings')->nullable();
            $table->timestamps();
        });

        // 3. Create Outbox Events table for Transactional Outbox Pattern
        Schema::create('outbox_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('organization_id')->nullable(); // Outbox events are tenant-aware
            $table->string('event_type'); // E.g., 'proposal.created'
            $table->jsonb('payload'); // Event payload
            $table->string('queue')->default('default'); // E.g., 'critical', 'default', 'bulk'
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->string('status')->default('pending'); // pending, processed, failed
            $table->text('error_message')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            
            $table->index(['status', 'created_at']);
        });

        // 4. Create Postgres RLS policy helper functions (only on PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            // Function to check if RLS is bypassed (e.g. for super admins/console)
            DB::statement("
                CREATE OR REPLACE FUNCTION current_org_id() RETURNS UUID AS $$
                BEGIN
                    RETURN NULLIF(current_setting('app.current_org_id', true), '')::UUID;
                EXCEPTION
                    WHEN OTHERS THEN
                        RETURN NULL;
                END;
                $$ LANGUAGE plpgsql STABLE;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('organizations');
        
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("DROP FUNCTION IF EXISTS current_org_id();");
        }
    }
};
