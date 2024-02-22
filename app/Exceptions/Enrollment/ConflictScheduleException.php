<?php

namespace App\Exceptions\Enrollment;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Exceptions\HttpException;

class ConflictScheduleException extends HttpException
{
    protected $errorCode  = ErrorCodes::ENROLLMENT_CONFLICT_SCHEDULE;

    protected $message    = 'Unable to enroll to the lesson due to conflicting schedule.';

    protected $statusCode = Response::HTTP_BAD_REQUEST;
}
