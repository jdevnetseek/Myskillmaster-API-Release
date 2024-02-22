<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Cashier\Events\WebhookReceived;
use App\Jobs\Stripe\ChargeSucceededJob;
use App\Mail\Subscription\SubscriptionConfirmation;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\Subscription\ChargeFailed;
use App\Notifications\Subscription\InvoicePaymentFailed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class StripeEventListener implements ShouldQueue
{
    /**
     * Handle stripe event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(WebhookReceived $event)
    {
        try {
            $payload = data_get($event->payload, 'data.object');
            $user    = User::where('stripe_id', data_get($payload, 'customer'))->first();

            // Handle the event
            switch ($event->payload['type']) {
                case 'charge.succeeded':
                    // Handle the charge and save payment information to database
                    dispatch(new ChargeSucceededJob($payload));
                    break;
                case 'invoice.payment_succeeded':
                    if ($user) {
                        $stripePlan =   data_get($payload['lines']['data'], '0.plan.id');
                        $plan = Plan::where('stripe_plan',  $stripePlan)->first();

                        Mail::to($user->email)->send(new SubscriptionConfirmation($user, $plan, $payload));
                    }
                    break;
                case 'invoice.payment_failed':
                    if ($user) {
                        Notification::send($user, new InvoicePaymentFailed($payload));
                    }
                    break;
                case 'charge.failed':
                case 'payment_intent.payment_failed';
                    if ($user) {
                        Notification::send($user, new ChargeFailed(data_get($payload, 'last_payment_error')));
                    }
                    break;
                    // Unexpected event type
                    return response('Received unknown event type ' . $event->payload['type'], 200);
            }
        } catch (\Exception $e) {
            return response($e->getMessage(), 400);
        }
    }
}
