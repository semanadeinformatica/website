<?php

namespace App\Maintenance;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Foundation\MaintenanceMode;

class EnvMaintenanceMode implements MaintenanceMode
{
    public function __construct(protected Application $app) {} 

    /**
     * Take the application down for maintenance.
     *
     * @param  array  $payload
     * @return void
     */
    public function activate(array $payload): void {
        //
    }

    /**
     * Take the application out of maintenance.
     *
     * @return void
     */
    public function deactivate(): void {
        //
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function active(): bool {
        return $this->app->environment(['maintenance']);
    }

    /**
     * Get the data array which was provided when the application was placed into maintenance.
     *
     * @return array
     */
    public function data(): array {
        return [];
    }
}
