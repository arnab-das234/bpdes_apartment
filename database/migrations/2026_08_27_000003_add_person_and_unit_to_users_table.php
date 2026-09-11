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
        // 1. Add fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('person_id')->nullable()->after('organization_id')->index();
            $table->uuid('unit_id')->nullable()->after('person_id')->index();

            $table->foreign('person_id')->references('id')->on('persons')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
        });

        // 2. Add columns to persons table (family count, professional status, parking requests)
        Schema::table('persons', function (Blueprint $table) {
            $table->integer('family_members')->default(1)->after('gender');
            $table->boolean('is_professional')->default(true)->after('family_members');
            $table->boolean('car_parking')->default(false)->after('is_professional');
        });

        // 3. Create Electricity Bills table
        Schema::create('electricity_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('unit_id');
            $table->string('meter_number');
            $table->string('submeter_number')->nullable();
            $table->string('billing_month'); // e.g. "August 2026"
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('receipt_path')->nullable();
            $table->string('status')->default('pending'); // pending, approved

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });

        // 4. Create Community Posts table (Share experiences feed)
        Schema::create('community_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('person_id');
            $table->text('content');
            $table->integer('likes')->default(0);

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
        });

        // 5. Create Meeting Feedback table
        Schema::create('meeting_feedbacks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('meeting_id');
            $table->uuid('person_id');
            $table->text('feedback_text');

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
        });

        // Enable PostgreSQL Row-Level Security (RLS) on new tables
        if (DB::getDriverName() === 'pgsql') {
            $tables = ['electricity_bills', 'community_posts', 'meeting_feedbacks'];
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
        Schema::dropIfExists('meeting_feedbacks');
        Schema::dropIfExists('community_posts');
        Schema::dropIfExists('electricity_bills');

        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn(['family_members', 'is_professional', 'car_parking']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['users_person_id_foreign']);
            $table->dropForeign(['users_unit_id_foreign']);
            $table->dropColumn(['person_id', 'unit_id']);
        });
    }
};
