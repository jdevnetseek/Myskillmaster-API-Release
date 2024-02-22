<?php

namespace App\Support\OneTimePassword;

use App\Support\CodeGenerator;
use Illuminate\Support\Facades\Cache;

class OneTimePasswordManager
{
    protected $identifier;

    protected $expiresAt;

    protected $digits;

    /*
    |--------------------------------------------------------------------------
    | One Time Password Manager
    |--------------------------------------------------------------------------
    | A simple implementation to generate one time password.
    | We will take advantage of the Cache storage since the expiration feature
    | is already implemented.
    */

    /**
     * Creates an instance of the class
     *
     * @param integer|string $identifier
     * @param integer $ttl
     * @param integer $digits
     */
    public function __construct($identifier, int $ttl = 5, int $digits = 5)
    {
        $this->identifier = $identifier;

        $this->setTtl($ttl);
        $this->setDigits($digits);
    }


    /**
     * Creates an instance of the class.
     *
     * @param $identifier
     * @return self
     */
    public static function for($identifier) : self
    {
        return new self($identifier);
    }

    /**
     * Set the time to expire.
     *
     * @param int $ttl
     * @return self
     */
    public function setTtl(int $ttl) : self
    {
        $this->expiresAt = now()->addMinutes($ttl);

        return $this;
    }

    /**
     * Set the number of digits to be generated.
     *
     * @param int $digits
     * @return self
     */
    public function setDigits(int $digits) : self
    {
        $this->digits     = $digits;

        return $this;
    }

    /**
     * Generate an otp for the current identifier.
     * This invalidates the old otp.
     *
     * @return array
     */
    public function generate() : array
    {
        $otp = $this->generateCode($this->digits);

        Cache::put($this->getKey(), $otp, $this->expiresAt);

        return [
            'code'       => $otp,
            'expires_at' => $this->expiresAt
        ];
    }

    /**
     * Invalidates the otp for current identifier.
     *
     * @return void
     */
    public function invalidate()
    {
        Cache::delete($this->getKey());
    }

    /**
     * Validates if the otp belongs to current identifier.
     *
     * @param string $value
     * @return boolean
     */
    public function hasValidOtp(string $value)
    {
        return Cache::get($this->getKey()) === $value;
    }

    /**
     * Generate a random Numeric Code
     *
     * @param integer $digits
     * @return string
     */
    public function generateCode(int $digits = 5) : string
    {
        return CodeGenerator::make($digits);
    }

    /**
     * Get the unique key to be used when storing the otp.
     *
     * @return string
     */
    private function getKey() : string
    {
        return $this->identifier . ':otp';
    }
}
