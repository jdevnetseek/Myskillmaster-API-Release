<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Validation\Rule;
use App\Support\Faker\BypassCodeValidator;

class ValidResetPasswordToken implements Rule
{
    use BypassCodeValidator;

    /**
     * PasswordReset email
     *
     * @var string
     */
    private $username;

    /**
     * The message if this validation fail
     *
     * @var string
     */
    private $message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $username)
    {
        $this->username = $username;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $user = User::hasUsername($this->username)->first();

        if (!$user) {
            return false;
        }

        $pr = $user->passwordReset;

        if ($this->isUsingBypassCode($value)) {
            return true;
        }

        if ($pr && $pr->token == $value && $pr->expires_at->greaterThan(Carbon::now())) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('passwords.token');
    }
}
