<?php

namespace App\Models\Traits;

use Illuminate\Support\Arr;

trait ChecksAttribute
{
    /**
     * Check if model has attribute loaded
     *
     * @return boolean
     */
    public function hasAttribute($key)
    {
        return Arr::has($this->attributes, $key);
    }
}
