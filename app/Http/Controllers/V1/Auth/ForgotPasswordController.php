<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;
use App\Support\Helper;
use App\Rules\UsernameExist;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Rules\ValidEmailOrPhoneNumber;
use App\Http\Resources\PasswordResetResource;
use NotificationChannels\Twilio\TwilioChannel;
use App\Notifications\PasswordReset as PasswordResetNotification;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
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
     * Create a new password reset instance for reseting password.
     * And send password reset email to user
     *
     * @param \Illuminate\Http\Request
     * @return \App\Http\Resources\PasswordResetResource
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'bail', new ValidEmailOrPhoneNumber, 'bail', new UsernameExist],
        ]);

        $user = User::hasUsername($request->email)->first();
        // Delete old password reset
        // $user->passwordReset()->delete();
        PasswordReset::where('user_id', $user->id)->delete();
        // Create new one
        $passwordReset = $user->passwordReset()->create();

        $user->notify(new PasswordResetNotification($this->getVia($request->email)));

        return response()->json(['data' => [
            'email'   => $request->email,
            'expires_at' => $passwordReset->expires_at,
            'created_at' => $passwordReset->created_at,
        ]], 201);
    }

    /**
     * Auto detect where to send the reset token
     *
     * @param string $username
     * @return array
     */
    private function getVia(string $username): array
    {
        if (Helper::isEmail($username)) {
            return ['mail'];
        } else {
            return [TwilioChannel::class];
        }
    }
}
