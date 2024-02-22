<?php

namespace App\Exceptions\Payout;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Exceptions\HttpException;

class NoAvailableBalanceForPayoutException extends HttpException
{
    protected $errorCode  = ErrorCodes::PAYOUT_NO_AVAILABLE_BALANCE;

    protected $message = 'No available balance to payout. Balance might be still in process.';

    protected $statusCode = Response::HTTP_BAD_REQUEST;
}
