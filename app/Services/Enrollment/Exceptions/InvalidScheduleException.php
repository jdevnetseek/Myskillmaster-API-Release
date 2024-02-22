<?php

namespace App\Services\Enrollment\Exceptions;

class InvalidScheduleException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
