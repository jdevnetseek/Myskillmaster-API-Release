<?php

namespace App\Exceptions;

use App\Enums\ErrorCodes;
use App\Exceptions\HttpException;
use Illuminate\Http\Response;

class MasterLessonException extends HttpException
{
    protected $errorCode  = ErrorCodes::MASTER_LESSON_ERROR;

    protected $statusCode = Response::HTTP_BAD_REQUEST;

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
