<?php

namespace App\Providers;

use App\Http\Middleware\DevConsoleAuth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class DevConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register middleware for dev console
        $this->app['router']->aliasMiddleware('DevConsoleAuth', DevConsoleAuth::class);

        $this->registerTelescope();
        $this->registerHorizon();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            // Prune telescope records that 7 days old
            $schedule->command('telescope:prune --hours=168')->daily();
            // Record horizon metrics
            $schedule->command('horizon:snapshot')->everyFiveMinutes();
        });
    }

    /**
     * Register Telescope Service Provider
     *
     * @return void
     */
    private function registerTelescope()
    {
        if (config('dev.telescope_enabled')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Register Horizon Service Provider
     *
     * @return void
     */
    private function registerHorizon()
    {
        if (config('dev.horizon_enabled')) {
            $this->app->register(\Laravel\Horizon\HorizonServiceProvider::class);
            $this->app->register(HorizonServiceProvider::class);
        }
    }
}
