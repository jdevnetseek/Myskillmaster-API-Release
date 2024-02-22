<?php

namespace App\Http\Controllers\V1\Admin;

use App\Enums\Role;
use App\Models\User;
use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\V1\Auth\AuthController as BaseAuthController;

class AuthController extends BaseAuthController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware(['user.role' => Role::ADMIN . '|' . Role::SUPER_ADMIN])
            ->except('login');
    }

    /**
     * Authenticate user using email and password
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        /** @var User */
        $user = User::query()
            ->withBlocked()
            ->hasUsername($request->input('email'))
            ->role([Role::ADMIN, Role::SUPER_ADMIN])
            ->first();

        if (!$user) {
            return $this->respondWithError(ErrorCodes::INVALID_CREDENTIALS, Response::HTTP_UNAUTHORIZED);
        }

        if ($user->isBlocked()) {
            return $this->respondWithError(ErrorCodes::ACCOUNT_BLOCKED, Response::HTTP_UNAUTHORIZED);
        }

        if ($request->has('password') && ! Hash::check($request->input('password'), $user->password)) {
            return $this->respondWithError(ErrorCodes::INVALID_CREDENTIALS, Response::HTTP_UNAUTHORIZED);
        }

        /** @var NewAccessToken */
        $newAccessToken = $user->createToken($request->header('user-agent'));

        return $this->respondWithToken($newAccessToken->plainTextToken, UserResource::make($user));
    }
}
