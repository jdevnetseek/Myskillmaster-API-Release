<?php

namespace App\Exceptions;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;

class IncompleteMasterProfile extends HttpException
{
    protected $errorCode  = ErrorCodes::INCOMPLETE_MASTER_PROFILE;

    protected $message    = "We noticed that you haven't completed your onboarding process yet.";

    protected $statusCode = Response::HTTP_FORBIDDEN;
}
