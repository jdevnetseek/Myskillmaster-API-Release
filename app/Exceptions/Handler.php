<?php

namespace App\Exceptions;

use Throwable;
use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Support\Response\ReturnsReponse;
use Illuminate\Auth\Access\AuthorizationException;
use App\Lib\Oauth\Exceptions\InvalidTokenException;
use App\Exceptions\HttpException as AppHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    use ReturnsReponse;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        AppHttpException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException) {
            /**
             * Use the response with error code when dev provided a code/reason
             * from response policy.
             */
            if ($exception->getCode()) {
                return $this->respondWithError(
                    $exception->getCode(),
                    Response::HTTP_FORBIDDEN,
                    $exception->getMessage()
                );
            }
        }

        if ($exception instanceof InvalidTokenException) {
            return $this->handleInvalidTokenException($exception);
        }

        if ($exception instanceof AppHttpException && $request->wantsJson()) {
            return response()->json([
                'message'     => $exception->getMessage() ?: ErrorCodes::getDescription($exception->getErrorCode()),
                'error_code'  => $exception->getErrorCode(),
                'http_status' => $exception->getStatusCode(),
                'success'     => false
            ], $exception->getStatusCode(), $exception->getHeaders());
        }

        return parent::render($request, $exception);
    }

    private function handleInvalidTokenException(Throwable $e)
    {
        if (app()->isProduction()) {
            return response()->json(['message' => 'Invalid token.'], Response::HTTP_UNAUTHORIZED);
        } else {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }
}
