<?php

namespace App\Services\Enrollment\Objects;

class ApplicationFee
{
    public function __construct(
        public readonly float $amount,
        public readonly float $rate,
        public readonly float $adminFee,
        public readonly float $totalFee
    ) {
    }
}
