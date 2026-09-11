<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the application's database seeders.
     */
    public function run(): void
    {
        // Disable RLS triggers/checks during seeding (only if using PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SET CONSTRAINTS ALL DEFERRED');
        }

        // 1. Seed Permissions & Roles
        $this->call(PermissionSeeder::class);

        // 2. Seed Global Superadmin (Tenant ID is NULL)
        User::firstOrCreate(
            ['email' => 'superadmin@bpdes.app'],
            [
                'name' => 'BPDES Superadmin',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
            ]
        );

        // 3. Seed Full System Bulk Demo Data (Idempotent & non-destructive)
        $this->call(BulkDemoDataSeeder::class);
    }
}
