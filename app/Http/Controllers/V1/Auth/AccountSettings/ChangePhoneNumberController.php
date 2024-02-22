<?php

namespace App\Http\Controllers\V1\Auth\AccountSettings;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ChangeRequest;
use Illuminate\Http\Response;
use App\Support\CodeGenerator;
use App\Rules\ValidPhoneNumber;
use App\Rules\UniquePhoneNumber;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Lang;
use NotificationChannels\Twilio\Twilio;
use NotificationChannels\Twilio\TwilioSmsMessage;

class ChangePhoneNumberController extends Controller
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
    public function change(Request $request, Twilio $twilio)
    {
        $request->validate([
            'phone_number' => [
                'required',
                new ValidPhoneNumber,
                new UniquePhoneNumber(User::withBlocked())
            ]
        ]);

        /** @var User */
        $user =  $request->user();

        $newPhoneNumber = $request->input('phone_number');

        if ($user->isPhonePrimary()) {
            rescue(function () use ($user, $newPhoneNumber, $twilio) {
                $code = CodeGenerator::make();

                $user->changeRequestFor('phone_number', $newPhoneNumber, $code);
                // Send verification code
                $content = Lang::get('Your phone number verification code is :code', ['code' => $code]);

                $twilio->sendMessage(new TwilioSmsMessage($content), $newPhoneNumber);
            });
        } else {
            $user->phone_number = $newPhoneNumber;
            $user->save();
        }

        return UserResource::make($user->refresh('avatar'));
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
        $changeRequest = $user->getChangeRequestFor('phone_number');

        if (is_null($changeRequest)) {
            abort(Response::HTTP_NOT_FOUND, 'No request for change phone number.');
        }

        if (!$changeRequest->isTokenValid($request->input('token'))) {
            abort(Response::HTTP_BAD_REQUEST, 'The verification code was invalid.');
        }

        $user->applyChangeRequest($changeRequest);
        $user->verifyPhoneNumberNow();

        return UserResource::make($user->refresh('avatar'));
    }
}
