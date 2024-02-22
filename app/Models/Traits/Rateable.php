<?php

namespace App\Models\Traits;

use App\Enums\RateType;
use \Illuminate\Database\Eloquent\Relations\MorphMany;

trait Rateable
{
    public function ratings(): MorphMany
    {
        return $this->morphMany(\App\Models\Rating::class, 'rateable');
    }

    public function averageRating(): float
    {
        return $this->ratings()->avg('rating');
    }

    public function masterAverageRating(): float
    {
        $ratings = $this->ratings
            ->where('type', RateType::Master)
            ->avg('rating') ?? 0;

        return round($ratings, 1);
    }

    public function masterNoOfReviews(): int
    {
        return $this->ratings
            ->where('type', RateType::Master)
            ->count();
    }
}
