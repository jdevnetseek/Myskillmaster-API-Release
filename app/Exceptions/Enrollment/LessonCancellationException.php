<?php

namespace App\Exceptions\Enrollment;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Exceptions\HttpException;

class LessonCancellationException extends HttpException
{
    protected $errorCode  = ErrorCodes::ENROLLMENT_CANCELLATION_ERROR;

    protected $statusCode = Response::HTTP_BAD_REQUEST;

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
