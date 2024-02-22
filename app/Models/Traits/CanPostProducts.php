<?php

namespace App\Models\Traits;

use App\Models\Product;

trait CanPostProducts
{
    /**
     * Product Relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function products()
    {
        return $this->morphMany(Product::class, 'seller');
    }
}
