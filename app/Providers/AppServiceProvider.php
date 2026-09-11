<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\TenantManager::class, function ($app) {
            return new \App\Services\TenantManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\DomainEvent::class,
            \App\Listeners\DomainEventListener::class
        );

        // Self-healing check to seed vacant flats for registration
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('units') && \App\Models\Unit::count() === 2) {
                $bldg = \App\Models\Building::first();
                if ($bldg) {
                    \App\Models\Unit::create([
                        'organization_id' => $bldg->organization_id,
                        'building_id' => $bldg->id,
                        'flat_number' => '201',
                        'floor' => 2,
                        'unit_type' => '3BHK',
                        'super_built_up_area' => 1500.00,
                        'carpet_area' => 1200.00,
                        'ownership_type' => 'Owner',
                        'occupancy_status' => 'Vacant',
                        'monthly_maintenance_amount' => 3500.00,
                        'outstanding_amount' => 0.00,
                    ]);
                    \App\Models\Unit::create([
                        'organization_id' => $bldg->organization_id,
                        'building_id' => $bldg->id,
                        'flat_number' => '202',
                        'floor' => 2,
                        'unit_type' => '2BHK',
                        'super_built_up_area' => 1100.00,
                        'carpet_area' => 900.00,
                        'ownership_type' => 'Owner',
                        'occupancy_status' => 'Vacant',
                        'monthly_maintenance_amount' => 2600.00,
                        'outstanding_amount' => 0.00,
                    ]);
                    \App\Models\Unit::create([
                        'organization_id' => $bldg->organization_id,
                        'building_id' => $bldg->id,
                        'flat_number' => '301',
                        'floor' => 3,
                        'unit_type' => '1BHK',
                        'super_built_up_area' => 750.00,
                        'carpet_area' => 600.00,
                        'ownership_type' => 'Owner',
                        'occupancy_status' => 'Vacant',
                        'monthly_maintenance_amount' => 1800.00,
                        'outstanding_amount' => 0.00,
                    ]);
                    \App\Models\Unit::create([
                        'organization_id' => $bldg->organization_id,
                        'building_id' => $bldg->id,
                        'flat_number' => '302',
                        'floor' => 3,
                        'unit_type' => 'PENTHOUSE',
                        'super_built_up_area' => 2200.00,
                        'carpet_area' => 1800.00,
                        'ownership_type' => 'Owner',
                        'occupancy_status' => 'Vacant',
                        'monthly_maintenance_amount' => 5000.00,
                        'outstanding_amount' => 0.00,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore database connection failures during migrations
        }
    }
}
