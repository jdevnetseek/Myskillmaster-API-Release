<?php
namespace App\Services\Subscription\Exceptions;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Exceptions\HttpException;

class NoActiveSubscription extends HttpException
{
    protected $errorCode  = ErrorCodes::NO_SUBSCRIPTION_FOUND;

    protected $message    = 'Upgrade to one of our master plans to access exclusive features and take your experience to the next level';

    protected $statusCode = Response::HTTP_NOT_FOUND;
}