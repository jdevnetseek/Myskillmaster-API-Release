<?php

namespace App\Models\Traits;

use App\Models\Plan;
use Laravel\Cashier\Subscription;

trait HasSubscription
{
    /**
     * Check if the user has already subscribed
     *
     * @return boolean
     */
    public function getIsSubscribedAttribute()
    {
        return $this->activeSubscription ? true : false;
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->active();
    }
}
