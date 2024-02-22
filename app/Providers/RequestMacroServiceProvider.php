<?php

namespace App\Providers;

use Stripe\File;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Illuminate\Support\ServiceProvider;

class RequestMacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // A helper to easily configure and get the per page or limit request
        Request::macro('perPage', function ($perPage = 10) {
            return (int) request()->input('per_page', request()->input('limit', $perPage));
        });

        /**
         * Convert an uploaded file to a stripe file.
         */
        Request::macro('asStripeFile', function ($key, $purpose) {
            $params = [
                'purpose' => $purpose,
                'file'    => fopen(request()->file($key)->getRealPath(), 'r')
            ];

            return File::create($params, Cashier::stripeOptions());
        });
    }
}
