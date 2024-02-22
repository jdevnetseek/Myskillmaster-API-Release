<?php

namespace App\Models\Traits;

use Exception;
use App\Models\Address;
use App\Enums\AddressType;

trait HasAddresses
{
    /**
     * Address Relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'model');
    }

    /**
     * Delivery Address.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function deliveryAddress()
    {
        return $this->morphOne(Address::class, 'model')
            ->where('type', AddressType::DELIVERY)
            ->latest()
            ->take(1);
    }

    /**
     * Checks if model has delivery address.
     *
     * @return boolean
     */
    public function hasDeliveryAddress()
    {
        return filled($this->deliveryAddress);
    }
}
