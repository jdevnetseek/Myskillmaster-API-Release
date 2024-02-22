<?php

namespace App\Http\Controllers\V1\Auth\AccountSettings;

use App\Models\User;
use App\Enums\ErrorCodes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class VerificationTokenController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        /** @var User */
        $user = $request->user();

        $request->validate([
            'password' => Rule::requiredIf($user->isEmailPrimary()),
            'otp'      => Rule::requiredIf($user->isPhonePrimary())
        ]);

        if (!$request->has('password') && !$request->has('otp')) {
            return $this->respondWithError(ErrorCodes::AUTHENTICATION_REQUIRED, Response::HTTP_UNAUTHORIZED);
        }

        if ($request->has('password') && !Hash::check($request->input('password'), $user->password)) {
            return $this->respondWithError(ErrorCodes::INVALID_CREDENTIALS, Response::HTTP_UNAUTHORIZED);
        } elseif ($request->has('otp') && !$user->invalidateIfValidOneTimePassword($request->input('otp'))) {
            return $this->respondWithError(ErrorCodes::INVALID_ONE_TIME_PASSWORD, Response::HTTP_UNAUTHORIZED);
        }

        $data = $user->generateVerificationToken();

        return response()->json([
            'data' => [
                'token'      => $data['token'],
                'expires_at' => $data['expires_at']
            ]
        ], Response::HTTP_OK);
    }
}
