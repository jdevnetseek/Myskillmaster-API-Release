<?php

namespace App\Services\Objects;

class Money
{
    public function __construct(public readonly float $amount, public readonly string $currency)
    {}

    public function inCents(): int
    {
        return $this->amount * 100;
    }
}
