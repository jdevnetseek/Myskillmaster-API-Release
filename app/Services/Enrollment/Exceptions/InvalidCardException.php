<?php

namespace App\Services\Enrollment\Exceptions;

class InvalidCardException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
