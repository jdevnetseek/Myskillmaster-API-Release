<?php

namespace App\Exceptions;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;

class SubscriptionFound extends HttpException
{
    protected $errorCode  = ErrorCodes::SUBSCRIPTION_FOUND;

    protected $message    = "Sorry, you are already subscribed to a plan. If you would like to change or cancel your subscription, please visit your go pro settings.";

    protected $statusCode = Response::HTTP_FORBIDDEN;
}
