<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\VerifyEmail;
use App\Notifications\VerifyPhoneNumber;

class SendVerificationCode
{

    /**
     * The user to be verified
     *
     * @var User
     */
    public $user;

    /**
     * Execute this action
     *
     * @param string $via [email, phone_number]
     * @return void
     */
    public function execute(User $user, string $via = null)
    {
        $this->user = $user;

        if ($via == 'email') {
            return $this->sendCodeViaEmail();
        } elseif ($via == 'phone_number') {
            return $this->sendCodeViaPhoneNumber();
        } else {
            if (!empty($this->user->email)) {
                return $this->sendCodeViaEmail();
            } elseif (!empty($this->user->phone_number)) {
                return $this->sendCodeViaPhoneNumber();
            }
        }
    }

    /**
     * Send verification code to user via email
     *
     * @return void
     */
    private function sendCodeViaEmail()
    {
        if (!$this->user->isEmailVerified()) {
            $this->user->notify(new VerifyEmail());
        }
    }

    /**
     * Send verification code to user via sms
     *
     * @return void
     */
    private function sendCodeViaPhoneNumber()
    {
        if (!$this->user->isPhoneNumberVerified()) {
            $this->user->notify(new VerifyPhoneNumber());
        }
    }
}
