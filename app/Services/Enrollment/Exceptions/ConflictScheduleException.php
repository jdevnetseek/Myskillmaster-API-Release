<?php

namespace App\Services\Enrollment\Exceptions;

class ConflictScheduleException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Conflict schedule.');
    }
}
