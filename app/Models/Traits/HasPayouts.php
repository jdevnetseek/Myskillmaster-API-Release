<?php

namespace App\Models\Traits;

use App\Models\UserPayout;

trait HasPayouts
{
    public function payouts()
    {
        return $this->hasMany(UserPayout::class);
    }
}
