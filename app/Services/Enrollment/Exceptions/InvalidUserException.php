<?php

namespace App\Services\Enrollment\Exceptions;

class InvalidUserException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
