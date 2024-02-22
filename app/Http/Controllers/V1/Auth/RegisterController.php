<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;
use App\Enums\UsernameType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Actions\SendVerificationCode;
use App\Http\Requests\RegisterUserRequest;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param \App\Http\Requests\RegisterUserRequest
     * @return \App\Http\Resources\UserResource
     */
    public function __invoke(RegisterUserRequest $request, SendVerificationCode $sendVerificationCode)
    {
        $user = new User();

        $usesEmailAuthentication = $request->has('email') && $request->has('password');

        if ($usesEmailAuthentication) {
            $user->email            = $request->input('email');
            $user->password         = Hash::make($request->input('password'));
            $user->username         = $request->input('username');
            $user->primary_username = UsernameType::EMAIL;
        } else {
            $user->phone_number             = $request->input('phone_number');
            $user->primary_username         = UsernameType::PHONE_NUMBER;
        }

        $user->fill($request->only(['first_name', 'last_name', 'place_id']));

        $user->save();

        if ($request->hasFile('avatar')) {
            $user->setAvatar($request->file('avatar'));
        }

        $sendVerificationCode->execute($user);

        /** @var NewAccessToken */
        $newAccessToken = $user->createToken($request->header('user-agent', config('app.name')));

        return $this->respondWithToken($newAccessToken->plainTextToken, new UserResource($user));
    }
}
