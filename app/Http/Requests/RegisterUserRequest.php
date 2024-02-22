<?php

namespace App\Http\Requests;

use App\Rules\UnassignedMedia;
use App\Support\ValidatesPhone;
use App\Support\Faker\BypassCodeValidator;
use Illuminate\Foundation\Http\FormRequest;
use App\Support\OneTimePassword\InteractsWithOneTimePassword;

class RegisterUserRequest extends FormRequest
{
    use ValidatesPhone;
    use InteractsWithOneTimePassword;
    use BypassCodeValidator;

    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = false;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email'        => [
                'required',
                'email',
                'max:255',
                'unique:users'
            ],
            'first_name'   => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],
            'last_name'   => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],
            'username'   => [
                'nullable',
                'sometimes',
                'string',
                'max:255',
                'unique:users'
            ],
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
            ],
            'place_id'    => ['sometimes', 'exists:places,id'],
            'avatar'      => ['sometimes', 'nullable', 'image'],
        ];
    }
}
