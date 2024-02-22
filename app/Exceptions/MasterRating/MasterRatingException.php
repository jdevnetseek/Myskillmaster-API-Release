<?php

namespace App\Exceptions\MasterRating;

use App\Enums\ErrorCodes;
use App\Exceptions\HttpException;
use Illuminate\Http\Response;

class MasterRatingException extends HttpException
{
    protected $errorCode  = ErrorCodes::MASTER_RATING_ERROR;

    protected $statusCode = Response::HTTP_BAD_REQUEST;

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
