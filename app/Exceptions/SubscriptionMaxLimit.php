<?php

namespace App\Exceptions;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;

class SubscriptionMaxLimit extends HttpException
{
    protected $errorCode  = ErrorCodes::SUBSCRIPTION_MAX_LIMIT_EXCEEDED;

    protected $message    = 'You have reached the upload limit for your current subscription plan.';

    protected $statusCode = Response::HTTP_FORBIDDEN;
}
