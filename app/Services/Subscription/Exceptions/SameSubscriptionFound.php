<?php

namespace App\Services\Subscription\Exceptions;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Exceptions\HttpException;

class SameSubscriptionFound extends HttpException
{
    protected $errorCode  = ErrorCodes::SAME_SUBSCRIPTION_FOUND;

    protected $message    = "Oops! Looks like you are already subscribed to this plan. If you would like to manage your subscription or upgrade to a higher tier plan, please visit your go pro settings.";

    protected $statusCode = Response::HTTP_FORBIDDEN;
}
