<?php

namespace App\Http\Requests;

use App\Rules\UsernameExist;
use Illuminate\Validation\Rule;
use App\Rules\ValidEmailOrPhoneNumber;
use App\Rules\ValidResetPasswordToken;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'email' => ['required', new ValidEmailOrPhoneNumber, new UsernameExist],
            'token' => ['required', 'bail', new ValidResetPasswordToken($this->email ?? '')],
            'password' => [
                'required',
                'string',
                'min:8'
            ],
            'password_confirmation' => [
                'required',
                'string',
                'min:8',
                'required_with:password',
                'same:password'
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.exists' => trans('passwords.user_password_reset'),
            'password' => trans('passwords.password'),
            'token.required' => 'The verification code is required.',
        ];
    }
}
