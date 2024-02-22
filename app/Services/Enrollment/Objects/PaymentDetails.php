<?php

namespace App\Services\Enrollment\Objects;

class PaymentDetails
{
    public function __construct(
        public readonly float $price,
        public readonly ApplicationFee $applicationFee,
        public readonly string $currency,
        public readonly float $masterEarnings,
        public readonly float $subTotal,
        public readonly float $grandTotal
    ) {

    }
}
