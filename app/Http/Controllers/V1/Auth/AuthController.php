<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;
use App\Enums\ErrorCodes;
use App\Enums\UsernameType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Support\ValidatesEmail;
use App\Support\ValidatesPhone;
use Illuminate\Validation\Rule;
use App\Http\Requests\LoginRequest;
use Laravel\Sanctum\NewAccessToken;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Rules\ValidEmailOrPhoneNumber;

class AuthController extends Controller
{
    use ValidatesPhone;
    use ValidatesEmail;

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login']]);
        $this->middleware('throttle:60,1', ['except' => ['me']]);
    }

    /**
     * Authenticate user using email and password
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        /** @var User */
        $user = User::query()
            ->with('avatar')
            ->withBlocked()
            ->hasUsername($request->input('email'))
            ->first();

        if (!$user) {
            return $this->respondWithError(ErrorCodes::INVALID_CREDENTIALS, Response::HTTP_UNAUTHORIZED);
        }

        if ($user->isBlocked()) {
            return $this->respondWithError(ErrorCodes::ACCOUNT_BLOCKED, Response::HTTP_UNAUTHORIZED);
        }

        if ($user->isEmailPrimary() && $this->isPhone($request->input('email'))) {
            return $this->respondWithError(ErrorCodes::AUTHENTICATION_EMAIL_REQUIRED, Response::HTTP_UNAUTHORIZED);
        }

        if ($user->isPhonePrimary() && $this->isEmail($request->input('email'))) {
            return $this->respondWithError(ErrorCodes::AUTHENTICATION_PHONE_NUMBER_REQUIRED, Response::HTTP_UNAUTHORIZED);
        }

        if (!$request->has('password') && !$request->has('otp')) {
            return $this->respondWithError(ErrorCodes::AUTHENTICATION_REQUIRED, Response::HTTP_UNAUTHORIZED);
        }

        if ($request->has('password') && !Hash::check($request->input('password'), $user->password)) {
            return $this->respondWithError(ErrorCodes::INVALID_CREDENTIALS, Response::HTTP_UNAUTHORIZED);
        }

        if ($request->has('otp')) {
            // Invalidate the one time password once it is valid, else return an invalid otp error.
            if (!$user->invalidateIfValidOneTimePassword($request->input('otp'))) {
                return $this->respondWithError(ErrorCodes::INVALID_ONE_TIME_PASSWORD, Response::HTTP_UNAUTHORIZED);
            }
        }

        // check if the user use email to login
        // disable for now
        // if ($user->email == $data['email']) {
        //     // check if email is verified
        //     if (!$user->isEmailVerified()) {
        //         return $this->respondWithError(ErrorCodes::UNVERIFIED_EMAIL, 401);
        //     }
        // } else {
        //     // check if phone number is verified
        //     if (!$user->isPhoneNumberVerified()) {
        //         return $this->respondWithError(ErrorCodes::UNVERIFIED_PHONE_NUMBER, 401);
        //     }
        // }

        /** @var NewAccessToken */
        $newAccessToken = $user->createToken($request->header('user-agent'));

        return $this->respondWithToken($newAccessToken->plainTextToken, UserResource::make($user));
    }

    /**
     * Get the authenticated User.
     *
     * @return \App\Http\Resources\UserResource
     */
    public function me()
    {
        return new UserResource(auth()->user()->load('avatar'));
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
