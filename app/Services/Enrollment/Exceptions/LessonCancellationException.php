<?php

namespace App\Services\Enrollment\Exceptions;

use Exception;

class LessonCancellationException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
