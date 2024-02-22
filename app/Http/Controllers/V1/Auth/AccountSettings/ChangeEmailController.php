<?php

namespace App\Http\Controllers\V1\Auth\AccountSettings;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ChangeRequest;
use Illuminate\Http\Response;
use App\Support\CodeGenerator;
use App\Mail\VerifyChangeEmail;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ChangeEmailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verification-token');
    }

    /**
     * When users send a request to change his email, a verication code
     * will be send and will be used to verify before he/she can update
     * his/her old email.
     *
     * @param Request $request
     * @return void
     */
    public function change(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users'
            ]
        ]);

        /** @var User */
        $user = $request->user();

        $newEmail = $request->input('email');

        if ($user->isEmailPrimary()) {
            rescue(function () use ($user, $newEmail) {
                $code = CodeGenerator::make();

                $user->changeRequestFor('email', $newEmail, $code);

                Mail::to($newEmail)
                    ->send(new VerifyChangeEmail($user, $code));
            });
        } else {
            $user->email = $newEmail;
            $user->save();
        }

        return UserResource::make($user->fresh('avatar'));
    }

    /**
     * Once the token was verified and valid, the changes will be applied.
     *
     * @param Request $request
     * @return void
     */
    public function verify(Request $request)
    {
        $request->validate([
            'token' => [
                'required'
            ]
        ]);

        /** @var User */
        $user =  $request->user();

        /** @var ChangeRequest */
        $changeRequest = $user->getChangeRequestFor('email');

        if (is_null($changeRequest)) {
            abort(Response::HTTP_NOT_FOUND, 'No request for change email.');
        }

        if (!$changeRequest->isTokenValid($request->input('token'))) {
            abort(Response::HTTP_BAD_REQUEST, 'The verification code was invalid.');
        }

        $user->applyChangeRequest($changeRequest);
        $user->verifyEmailNow();

        return UserResource::make($user->refresh('avatar'));
    }
}
