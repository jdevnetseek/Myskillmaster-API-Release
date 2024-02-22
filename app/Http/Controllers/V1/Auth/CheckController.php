<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;
use App\Enums\ErrorCodes;
use App\Enums\UsernameType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Support\ValidatesEmail;
use App\Support\ValidatesPhone;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Notifications\OneTimePassword;
use App\Rules\ValidEmailOrPhoneNumber;
use App\Enums\AuthenticationType as AuthType;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckController extends Controller
{
    use ValidatesEmail;
    use ValidatesPhone;

    /**
     * Check if email exist
     *
     * @param Request $request
     * @return void
     */
    public function checkEmail(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email']
        ]);

        /** @var User */
        $user = User::query()
            ->where('email', $data['email'])
            ->withBlocked()
            ->first();

        if (!$user) {
            return $this->respondWithError(ErrorCodes::USERNAME_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if ($user->isBlocked()) {
            return $this->respondWithError(ErrorCodes::ACCOUNT_BLOCKED, Response::HTTP_UNAUTHORIZED);
        }

        // check if user password is set
        if (!$user->hasPassword()) {
            return $this->respondWithError(ErrorCodes::PASSWORD_NOT_SUPPORTED, Response::HTTP_UNAUTHORIZED);
        }

        // check if user email is verified
        // disable for now
        // if (!$user->isEmailVerified()) {
        //     return $this->respondWithError(ErrorCodes::UNVERIFIED_EMAIL, 401);
        // }

        return response()->json([
            'data' => ['email' => $data['email']]
        ]);
    }

    /**
     * Check if username exist
     *
     * @param Request $request
     * @return void
     */
    public function checkUsername(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', new ValidEmailOrPhoneNumber]
        ]);

        /** @var User */
        $user = User::query()
            ->hasUsername($data['username'])
            ->withBlocked()
            ->first();

        // Check if username is a valid email
        $usesEmail = $this->isEmail($data['username']);

        /**
         * Prepare the metadata, so we can inform the mobile what to do if a user
         * does not exists.
         */
        $metadata = [
            'username_type' => $usesEmail ? UsernameType::EMAIL : UsernameType::PHONE_NUMBER,
            'auth_type'     => $usesEmail ? AuthType::PASSWORD : AuthType::OTP,
        ];

        // Let's Check if user exists.
        if (!$user) {
            return $this->respondWithError(
                ErrorCodes::USERNAME_NOT_FOUND,
                Response::HTTP_NOT_FOUND,
                null,
                $metadata
            );
        }

        // Let's check if users account was blocked.
        if ($user->isBlocked()) {
            return $this->respondWithError(
                ErrorCodes::ACCOUNT_BLOCKED,
                Response::HTTP_UNAUTHORIZED,
                null,
                $metadata
            );
        }

        // Return error when username type is email and user did not use email.
        if ($user->isEmailPrimary() && !$usesEmail) {
            return $this->respondWithError(
                ErrorCodes::AUTHENTICATION_EMAIL_REQUIRED,
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Return error when username type is phone and user did not use phone.
        if ($user->isPhonePrimary() && $usesEmail) {
            return $this->respondWithError(
                ErrorCodes::AUTHENTICATION_PHONE_NUMBER_REQUIRED,
                Response::HTTP_UNAUTHORIZED
            );
        }

        /**
         * Prepare the response data, and merge other information from our metadata
         * so mobiles will be able to identity what is the next flow.
         */
        $data = array_merge([
            'username'      => $data['username'],
            'has_password'  => $user->hasPassword()
        ], $metadata);

        // check if the user uses email to login
        // disable for now
        // if ($usesEmail) {
        // check if email is verified
        // if (!$user->isEmailVerified()) {
        //     return $this->respondWithError(ErrorCodes::UNVERIFIED_EMAIL, 401);
        // }
        // } else {
        // check if phone number is verified
        // if (!$user->isPhoneNumberVerified()) {
        //     return $this->respondWithError(ErrorCodes::UNVERIFIED_PHONE_NUMBER, 401);
        // }
        // }

        return JsonResource::make($data);
    }

    /**
     * Check if email is available or not
     *
     * @param Request $request
     * @return void
     */
    public function checkEmailAvailability(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email']
        ]);

        /** @var User */
        $user = User::query()
            ->where('email', $data['email'])
            ->withBlocked()
            ->first();

        if (!$user) {
            return response()->json([
                'data' => [
                    'email' => $data['email'],
                    'is_available' => true,
                ]
            ]);
        }

        return response()->json([
            'data' => [
                'email' => $data['email'],
                'is_available' => false,
            ]
        ]);
    }
}
