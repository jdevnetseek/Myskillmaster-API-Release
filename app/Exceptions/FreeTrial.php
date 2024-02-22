<?php

namespace App\Exceptions;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;

class FreeTrial extends HttpException
{
    protected $errorCode  = ErrorCodes::INCORRECT_PLAN;

    protected $message    = 'Free trial is only applicable on the highest plan.';

    protected $statusCode = Response::HTTP_FORBIDDEN;
}
