<?php

namespace App\Support\Response;

use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

trait ReturnsReponse
{
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $user, $statusCode = 200)
    {
        return response()->json(['data' => [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => now()->addMinutes(config('jwt.ttl')),
            'user' => $user
        ]], $statusCode)->header('Authorization', $token);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($errorCode, $statusCode = 400, $message = null, $metadata = [])
    {
        $payload = [
            'message'    => $message ?: ErrorCodes::getDescription($errorCode),
            'error_code' => $errorCode,
        ];

        if (filled($metadata)) {
            $payload = array_merge($payload, ['meta' => $metadata]);
        }

        return response()->json($payload, $statusCode);
    }

    /**
     * return a empty data response.
     *
     * @param integer $statusCode
     * @return void
     */
    protected function respondWithEmptyData($statusCode = 200)
    {
        return response()->json([], $statusCode);
    }

    protected function respondCardError($errorCode, $statusCode = 400, $errorType, $message = null)
    {
        switch ($errorType) {
            case 'invalid_expiry_month':
            case 'invalid_expiry_year':
                $payload = [
                    'message' => $message,
                    'errors' => [
                        'card_expiry_date' => [$message],
                    ],
                    'error_code' => $errorCode,
                ];
                break;
            case 'incorrect_number':
            case 'card_declined':
            case 'expired_card':
                $payload = [
                    'message' => $message,
                    'errors' => [
                        'card_number' => [$message],
                    ],
                    'error_code' => $errorCode,
                ];
                break;
            case 'invalid_cvc':
            case 'incorrect_cvc':
                $payload = [
                    'message' => $message,
                    'errors' => [
                        'card_cvc' => [$message ? $message : 'Invalid card cvc code'],
                    ],
                    'error_code' => $errorCode,
                ];
                break;
            case 'processing_error':
                $payload = [
                    'message' => $message,
                    'error_code' => $errorCode,
                ];
                break;
        }

        return response()->json($payload, $statusCode);
    }
}
