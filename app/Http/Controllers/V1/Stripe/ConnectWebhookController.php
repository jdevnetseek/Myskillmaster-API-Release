<?php

namespace App\Http\Controllers\V1\Stripe;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Middleware\VerifyConnectWebhookSignature;
use App\Http\Controllers\V1\Stripe\Traits\HandlePayoutEvent;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class ConnectWebhookController extends CashierWebhookController
{
    use HandlePayoutEvent;

    /**
     * Create a new WebhookController instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (config('cashier.connect.webhook.secret')) {
            $this->middleware(VerifyConnectWebhookSignature::class);
        }
    }

    /**
     * Handle users account updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleAccountUpdated(array $payload)
    {
        if ($user = User::where('stripe_connect_id', data_get($payload, 'data.object.id'))->first()) {
            $user->charges_enabled = data_get($payload, 'data.object.charges_enabled', false);
            $user->payouts_enabled = data_get($payload, 'data.object.payouts_enabled', false);
            $user->save();
        }

        return $this->successMethod();
    }
}
