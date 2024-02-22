<?php

namespace App\Services\Subscription\Exceptions;

use Exception;

class SubscriptionException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
