<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Boot services.
     */
    public function boot(): void
    {
        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);

            // Load routes
            $this->registerRoutes($modulePath, $moduleName);

            // Load migrations
            $migrationPath = $modulePath . '/Database/Migrations';
            if (File::exists($migrationPath)) {
                $this->loadMigrationsFrom($migrationPath);
            }

            // Load views
            $viewsPath = $modulePath . '/Resources/views';
            if (File::exists($viewsPath)) {
                $this->loadViewsFrom($viewsPath, strtolower($moduleName));
            }
        }
    }

    /**
     * Register the module routes.
     */
    protected function registerRoutes(string $modulePath, string $moduleName): void
    {
        $routesPath = $modulePath . '/Routes';

        if (!File::exists($routesPath)) {
            return;
        }

        // Web routes
        if (File::exists($routesPath . '/web.php')) {
            Route::middleware('web')
                ->group($routesPath . '/web.php');
        }

        // API routes
        if (File::exists($routesPath . '/api.php')) {
            Route::middleware('api')
                ->prefix('api/v1/' . strtolower($moduleName))
                ->group($routesPath . '/api.php');
        }
    }
}
