<?php

namespace App\Models\Traits;

use App\Support\OneTimePassword\InteractsWithOneTimePassword;

trait ManagesOneTimePassword
{
    use InteractsWithOneTimePassword {
        sendOneTimePassword as sendOtp;
        hasValidOneTimePassword as hasValidOtp;
    }

    /**
     * Send a one time password notification.
     *
     * @return void
     */
    public function sendOneTimePassword()
    {
        $this->sendOtp($this->otpDestination(), $this->otpChannel());
    }

    /**
     * Validates if the one time password is correct.
     *
     * @param string $otp
     * @return boolean
     */
    public function hasValidOneTimePassword(string $otp) : bool
    {
        return $this->hasValidOtp($this->otpDestination(), $otp);
    }

    /**
     * Invalidates the generated one time password.
     *
     * @return void
     */
    public function invalidateOneTimePassword() : void
    {
        $this->invalidateOneTimePasswordFor($this->otpDestination());
    }

    /**
     * The otp channel to be used when sending the otp
     *
     * @return string
     */
    abstract protected function otpChannel() : string;

    /**
     * Declare where the otp is sent.
     *
     * @return string
     */
    abstract protected function otpDestination() : string;
}
