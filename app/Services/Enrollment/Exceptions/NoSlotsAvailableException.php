<?php

namespace App\Services\Enrollment\Exceptions;

use App\Services\Enrollment\Exceptions\InvalidScheduleException;

class NoSlotsAvailableException extends InvalidScheduleException
{
    public function __construct()
    {
        parent::__construct('No available slots for the schedule');
    }
}
