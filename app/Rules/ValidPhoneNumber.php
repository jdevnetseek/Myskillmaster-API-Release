<?php

namespace App\Rules;

use App\Support\ValidatesPhone;
use Illuminate\Contracts\Validation\Rule;

class ValidPhoneNumber implements Rule
{
    use ValidatesPhone;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) : bool
    {
        return $this->isPhone($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.valid_phone_number');
    }
}
