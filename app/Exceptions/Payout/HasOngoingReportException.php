<?php

namespace App\Exceptions\Payout;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Exceptions\HttpException;

class HasOngoingReportException extends HttpException
{
    protected $errorCode  = ErrorCodes::PAYOUT_USER_HAS_ONGOING_REPORT;

    protected $statusCode = Response::HTTP_FORBIDDEN;
}
