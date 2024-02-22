<?php

namespace App\Models\Traits;

use App\Models\UserPayout;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait InteractsWithUserPayout
{
    public function payouts()
    {
        return $this->belongsToMany(UserPayout::class, 'lesson_enrollment_payouts')->latest();
    }
}
