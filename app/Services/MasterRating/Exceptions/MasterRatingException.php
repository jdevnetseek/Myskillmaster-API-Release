<?php

namespace App\Services\MasterRating\Exceptions;

use Exception;

class MasterRatingException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
