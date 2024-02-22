<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Requests\CheckResetPasswordTokenRequest;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
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
     * Update user password and remove all password_reset associated to user
     *
     * @param ResetPasswordRequest $request
     * @return void
     */
    public function __invoke(ResetPasswordRequest $request)
    {
        DB::transaction(function () use ($request) {
            // Change user password
            $user = User::hasUsername($request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Remove all password reset entry for this user
            $user->passwordReset()->delete();
        });

        return response()->json([
            'message' => trans('passwords.reset'),
        ]);
    }

    /**
     * Check reset password token
     *
     * @param CheckResetPasswordTokenRequest $request
     */
    public function checkToken(CheckResetPasswordTokenRequest $request)
    {
        $user = User::hasUsername($request->email)->first();
        $passwordReset = $user->passwordReset;
        $passwordReset->makeVisible(['token']);

        return response()->json(['data' => [
            'email'   => $request->email,
            'token'      => $passwordReset->token,
            'expires_at' => $passwordReset->expires_at,
            'created_at' => $passwordReset->created_at,
        ]]);
    }

    public function getVerifiedAccount(Request $request)
    {
        $user = User::where('email', $request->get('email'))->first();

        return response()->json([
            'is_email_verified' => $user->isEmailVerified(),
            'is_phone_verified' => $user->isPhoneNumberVerified(),
            'verified_account' => $user->verifiedAccount
        ]);
    }
}
