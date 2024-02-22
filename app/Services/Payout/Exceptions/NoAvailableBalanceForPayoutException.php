<?php

namespace App\Services\Payout\Exceptions;

class NoAvailableBalanceForPayoutException extends \Exception
{
    public function __construct()
    {
        parent::__construct('No available balance for payout. Balance might be still in process.');
    }
}
