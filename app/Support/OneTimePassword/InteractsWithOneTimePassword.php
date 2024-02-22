<?php

namespace App\Support\OneTimePassword;

use \App\Notifications\OneTimePassword;
use NotificationChannels\Twilio\TwilioChannel;
use \Illuminate\Notifications\AnonymousNotifiable;

trait InteractsWithOneTimePassword
{
    /**
     * Sends a one time password to the destination provided.
     *
     * @param string $destination
     * @param string $channel
     * @return void
     */
    protected function sendOneTimePassword(string $destination, string $channel = TwilioChannel::class)
    {
        $anonymousNotifiable = new AnonymousNotifiable();
        $anonymousNotifiable->route($channel, $destination);

        $otp = OneTimePasswordManager::for($destination)
            ->generate();

        $anonymousNotifiable->notify(new OneTimePassword($otp['code']));
    }

    /**
     * Checks if the destinations and the one time password is valid.
     *
     * @param string $destination
     * @param string $otp
     * @return boolean
     */
    protected function hasValidOneTimePassword(string $destination, string $otp)
    {
        return OneTimePasswordManager::for($destination)
            ->hasValidOtp($otp);
    }

    /**
     * Invalidate the one time password.
     *
     * @param string $destination
     * @return void
     */
    protected function invalidateOneTimePasswordFor(string $destination)
    {
        OneTimePasswordManager::for($destination)
            ->invalidate();
    }
}
