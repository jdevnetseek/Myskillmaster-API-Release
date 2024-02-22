<?php

namespace App\Exceptions\MasterProfile;

use App\Enums\ErrorCodes;
use App\Exceptions\HttpException;
use Illuminate\Http\Response;

class MasterProfileException extends HttpException
{
    protected $errorCode  = ErrorCodes::MASTER_PROFILE_ERROR;

    protected $statusCode = Response::HTTP_FORBIDDEN;

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
