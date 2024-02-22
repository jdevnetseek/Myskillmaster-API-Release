<?php

namespace App\Services\Subscription\Exceptions;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Exceptions\HttpException;

class DefaultPaymentMethodNotFound extends HttpException
{
    protected $errorCode  = ErrorCodes::DEFAULT_PAYMENT_NOT_FOUND;

    protected $message    = "Unable to process the transaction. You do not have any payment method added to your account. Please add a valid payment method to your account and try again.";

    protected $statusCode = Response::HTTP_NOT_FOUND;
}
