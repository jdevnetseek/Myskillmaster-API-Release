<?php

namespace App\Http\Controllers\V1\Auth\AccountSettings;

use App\Enums\ErrorCodes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed']
        ]);

        $user = $request->user();

        if ($request->input('old_password') === $request->input('new_password')) {
            return $this->respondWithError(ErrorCodes::USING_OLD_PASSWORD, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!Hash::check($request->input('old_password'), $user->password)) {
            return $this->respondWithError(ErrorCodes::INVALID_CREDENTIALS, Response::HTTP_UNAUTHORIZED);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return UserResource::make($user->refresh());
    }
}
