<?php

namespace App\Services\Enrollment;

use App\Services\Enrollment\Objects\ApplicationFee;
use App\Services\Enrollment\Objects\PaymentDetails;

/**
 * Handles computation of a lesson
 */
class LessonPaymentCalculator
{
    protected float $adminFee = 1;

    public function __construct(
        protected float $price,
        protected ?float $applicationFeeRate = 5,
        protected string $currency = 'aud'
    ) {
    }

    public function setAdminFee(float $adminFee): self
    {
        $this->adminFee = $adminFee;
        return $this;
    }

    public function execute() : PaymentDetails
    {
        /**
         * Formula
         * Grand total = lesson price + admin fee
         *   admin fee = $1
         *   Grand total = lesson price + $1
         *
         * Master Earnings
         *  Master earnings = lesson price - application fee
         *  Application fee rate = 5% of lesson price
         *
         * Platform earnings
         *   earnings = application fee + admin fee
         *
         * Sample:
         *   Lesson price = $10
         *   admin fee = $1
         *   Grand total = lesson price + admin fee = $10 + $1 = $11
         *
         *   application fee = lesson price * 0.05 = $10 * 0.05 = $0.50
         *
         *   Master earnings = lesson price - application fee = $10 - $0.50 = $9.50
         *
         *   Platform earnings = admin fee + application fee = $1 + $0.50 = $1.50
         */

        $subTotal = $this->price;
        $grandTotal = $subTotal + $this->adminFee;

        $applicationFee = $this->applicationFee($subTotal);
        $totalFee = $applicationFee + $this->adminFee;

        $masterEarnings = $subTotal - $applicationFee;

        return new PaymentDetails(
            $this->price,
            new ApplicationFee($applicationFee, $this->applicationFeeRate, $this->adminFee, $totalFee),
            $this->currency,
            $masterEarnings,
            $subTotal,
            $grandTotal
        );
    }

    protected function applicationFee(float $subTotal): float
    {
        $fee = $subTotal * ($this->applicationFeeRate / 100);

        return round($fee, 2);
    }
}
