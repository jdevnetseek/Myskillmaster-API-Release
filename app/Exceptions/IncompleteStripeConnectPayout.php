<?php

namespace App\Exceptions;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;

class IncompleteStripeConnectPayout extends HttpException
{
    protected $errorCode  = ErrorCodes::STRIPE_CONNECT_PAYOUTS_DISABLED;

    protected $message    = 'Please complete your stripe connect details.';

    protected $statusCode = Response::HTTP_FORBIDDEN;
}
