<?php

namespace App\Exceptions\Subscription;


use App\Enums\ErrorCodes;
use App\Exceptions\HttpException;
use Illuminate\Http\Response;

class SubscriptionException extends HttpException
{
    protected $errorCode  = ErrorCodes::SUBSCRIPTION_ERROR;

    protected $statusCode = Response::HTTP_BAD_REQUEST;

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
