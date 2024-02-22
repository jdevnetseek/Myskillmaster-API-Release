<?php

namespace App\Support\Faker;

trait BypassCodeValidator
{
    /**
     * Checks if we ignore certain code in debug mode.
     *
     * @param ?string $code
     * @param int $digits
     * @return boolean
     */
    public function isUsingBypassCode(?string $code, int $digits = 5)
    {
        /** on debug mode, allow bypass for token validation */
        if (config('app.use_bypass_code') === true && $code === str_pad('0', $digits, '0')) {
            return true;
        }

        return false;
    }
}
