<?php

namespace App\Http\Requests;

use App\Enums\UsernameType;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;

class VerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => ['required', 'bail'],
            'via'   => ['required', 'bail', new EnumValue(UsernameType::class)],
        ];
    }

    /**
     * Check if the token/code matches
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            switch ($this->via) {
                case UsernameType::EMAIL:
                    if (!$this->user()->hasEmail()) {
                        $validator->errors()->add(
                            'email',
                            trans('validation.verification.email_not_found')
                        );
                    } else {
                        if (!$this->user()->isValidEmailVerificationCode($this->token)) {
                            $validator->errors()->add(
                                'token',
                                trans('validation.verification.email_token_not_match')
                            );
                        }
                    }
                    break;

                case UsernameType::PHONE_NUMBER:
                    if (!$this->user()->hasPhoneNumber()) {
                        $validator->errors()->add(
                            'phone_number',
                            trans('validation.verification.phone_number_not_found')
                        );
                    } else {
                        if (!$this->user()->isValidPhoneVerificationCode($this->token)) {
                            $validator->errors()->add(
                                'token',
                                trans('validation.verification.phone_number_token_not_match')
                            );
                        }
                    }
                    break;
            }
        });
    }
}
