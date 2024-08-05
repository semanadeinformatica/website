<?php

namespace App\Providers;

use App\Maintenance\EnvMaintenanceMode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\MaintenanceModeManager;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(
            MaintenanceModeManager::class,
            function (MaintenanceModeManager $manager) {
                $manager->extend('env', function (Container $container) {
                    return new EnvMaintenanceMode(
                        $container->make(Application::class)
                    );
                });

                return $manager;
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
