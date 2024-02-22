<?php

namespace App\Rules;

use App\Support\ValidatesEmail;
use App\Support\ValidatesPhone;
use Illuminate\Contracts\Validation\Rule;

class ValidEmailOrPhoneNumber implements Rule
{
    use ValidatesEmail;
    use ValidatesPhone;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->isEmail($value) || $this->isPhone($value)) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.valid_email_or_phone_number');
    }
}
