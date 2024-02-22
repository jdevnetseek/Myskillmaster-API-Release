<?php

namespace App\Services\Enrollment\Exceptions;

class InvalidStudentException extends LessonCancellationException
{
    public function __construct()
    {
        parent::__construct('Invalid student. Student cannot cancel the lesson of other students or masters.');
    }
}
