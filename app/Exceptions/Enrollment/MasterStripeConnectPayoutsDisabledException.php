<?php

namespace App\Exceptions\Enrollment;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Exceptions\HttpException;

class MasterStripeConnectPayoutsDisabledException extends HttpException
{
    protected $errorCode  = ErrorCodes::MASTER_STRIPE_CONNECT_PAYOUTS_DISABLED;

    protected $message    = 'Master haven\'t setup their payout details.';

    protected $statusCode = Response::HTTP_BAD_REQUEST;
}
