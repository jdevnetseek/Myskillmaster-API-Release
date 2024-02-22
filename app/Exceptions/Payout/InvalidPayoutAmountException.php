<?php

namespace App\Exceptions\Payout;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Exceptions\HttpException;

class InvalidPayoutAmountException extends HttpException
{
    protected $errorCode  = ErrorCodes::PAYOUT_INVALID_AMOUNT;

    protected $statusCode = Response::HTTP_BAD_REQUEST;
}
