<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Str;
use SKAgarwal\GoogleApi\PlacesApi;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PlacesApi::class, function ($app) {
            return new PlacesApi(config('services.google.places_api'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Relation::morphMap([
        //     'users' => User::class
        // ]);

        Str::macro('cleanPhoneNumber', function (?string $value) {
            return str_replace('+', '', $value);
        });

        if ($this->app->environment(['staging', 'production'])) {
            URL::forceScheme('https');
        }
    }
}
