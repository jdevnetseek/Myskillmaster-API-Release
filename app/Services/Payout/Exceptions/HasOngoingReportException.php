<?php

namespace App\Services\Payout\Exceptions;

class HasOngoingReportException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Cannot request payout. User is still under investigation due to reports.');
    }
}
