<?php

namespace App\Services\Enrollment\Exceptions;

class InvalidMasterException extends LessonCancellationException
{
    public function __construct()
    {
        parent::__construct('Invalid master. Master cannot cancel the lesson of other masters.');
    }
}
