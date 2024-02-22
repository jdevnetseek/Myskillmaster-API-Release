<?php

namespace App\Http\Requests;

use App\Rules\UsernameExist;
use App\Rules\ValidEmailOrPhoneNumber;
use App\Rules\ValidResetPasswordToken;
use Illuminate\Foundation\Http\FormRequest;

class CheckResetPasswordTokenRequest extends FormRequest
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
        ];
    }
}
