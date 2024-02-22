<?php

namespace App\Providers;

use App\Listeners\StripeEventListener;
use Illuminate\Auth\Events\Registered;
use Laravel\Cashier\Events\WebhookReceived;
use App\Events\LessonEnrollment\LessonCancelled;
use App\Listeners\Lesson\LessonCancellationListener;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        WebhookReceived::class => [
            StripeEventListener::class,
        ],
        LessonCancelled::class => [
            LessonCancellationListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
