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
        // 1. Properties (Complexes/Premises)
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('name');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('street')->nullable();
            $table->string('locality')->nullable();
            $table->string('ward')->nullable();
            $table->string('municipality')->nullable();
            $table->string('police_station')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->default('West Bengal');
            $table->string('pin');

            // Land/Property Identification
            $table->string('plot_number')->nullable();
            $table->string('dag_number')->nullable();
            $table->string('khatian_number')->nullable();
            $table->string('mouza')->nullable();
            $table->string('jl_number')->nullable();
            $table->decimal('total_land_area', 12, 2)->nullable();
            $table->jsonb('common_areas')->nullable(); // Parking, Garden, Clubhouse details

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // 2. Buildings (Towers/Blocks within a property)
        Schema::create('buildings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('property_id');
            $table->string('name'); // Tower A, Block B
            $table->integer('floors')->default(1);
            $table->integer('units_count')->default(0);

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
        });

        // 3. Units (Individual Flats/Apartments)
        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('building_id');
            $table->string('flat_number');
            $table->integer('floor')->default(0);
            $table->string('unit_type')->nullable(); // 2BHK, 3BHK, etc.
            $table->decimal('carpet_area', 8, 2)->nullable();
            $table->decimal('built_up_area', 8, 2)->nullable();
            $table->decimal('super_built_up_area', 8, 2)->nullable();
            $table->decimal('undivided_land_share', 8, 2)->nullable();
            $table->string('parking_slots')->nullable();
            
            $table->string('ownership_type')->default('Owner'); // Owner, Tenant
            $table->string('occupancy_status')->default('Vacant'); // Self Occupied, Rented, Vacant
            $table->string('maintenance_category')->default('Standard');
            $table->decimal('monthly_maintenance_amount', 12, 2)->default(0.00);
            $table->decimal('outstanding_amount', 12, 2)->default(0.00);
            $table->string('status')->default('active'); // active, maintenance, sold

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
        });

        // 4. Persons (Contacts register for owners, tenants, candidates)
        Schema::create('persons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('name');
            $table->string('guardian_name')->nullable(); // Father's/Mother's/Spouse's Name
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('id_type')->nullable(); // Aadhaar, PAN, Passport
            $table->string('id_number')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('occupation')->nullable();
            $table->string('pan')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // 5. Memberships (Resolves owner-unit relationships)
        Schema::create('memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('unit_id');
            $table->uuid('person_id');
            $table->string('membership_number')->nullable();
            $table->boolean('primary_owner')->default(true);
            $table->boolean('co_owner')->default(false);
            $table->date('membership_date')->nullable();
            $table->string('membership_status')->default('active'); // active, inactive
            $table->boolean('voting_rights')->default(true);

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
        });

        // 6. Nominees
        Schema::create('nominees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('membership_id');
            $table->string('name');
            $table->string('relationship');
            $table->date('dob')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('percentage_share', 5, 2)->default(100.00);
            $table->string('guardian_name')->nullable(); // If nominee is minor

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('cascade');
        });

        // 7. Committee Appointments
        Schema::create('committee_appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('person_id');
            $table->string('designation'); // President, Vice President, Secretary, Joint Secretary, Treasurer, Committee Member
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('appointment_method')->default('Election'); // Election, Selection, Co-opted
            $table->string('status')->default('active'); // active, expired, resigned

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
        });

        // Enable Row-Level Security (RLS) on all these tables (only if using PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            $tables = ['properties', 'buildings', 'units', 'persons', 'memberships', 'nominees', 'committee_appointments'];
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
        Schema::dropIfExists('committee_appointments');
        Schema::dropIfExists('nominees');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('persons');
        Schema::dropIfExists('units');
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('properties');
    }
};
