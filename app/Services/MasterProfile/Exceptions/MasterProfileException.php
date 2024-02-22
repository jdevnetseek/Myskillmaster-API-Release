<?php

namespace App\Services\MasterProfile\Exceptions;

use Exception;

class MasterProfileException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
