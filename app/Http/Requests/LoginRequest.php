<?php

namespace App\Http\Requests;

use App\Support\ValidatesEmail;
use App\Support\ValidatesPhone;
use Illuminate\Validation\Rule;
use App\Rules\ValidEmailOrPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\RequiredIf;

class LoginRequest extends FormRequest
{
    use ValidatesPhone;
    use ValidatesEmail;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', new ValidEmailOrPhoneNumber],
            'password' => [
                'bail',
                Rule::requiredIf($this->isEmail($this->get('email') ?? ''))
            ],
            'otp'      => [
                'bail',
                Rule::requiredIf(
                    $this->isPhone($this->get('email') ?? '') &&
                        !config('app.use_bypass_code')
                )
            ]
        ];
    }
}
