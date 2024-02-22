<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\ValidUser;
use App\Rules\ValidPhoneNumber;
use App\Support\ValidatesPhone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    use ValidatesPhone;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->route('user')) {
            $userId = $this->route('user')->id;
        } else {
            $userId = $this->route('id');
        }

        return [
            'first_name' => [
                'required',
                'string',
                'max:255',
                new ValidUser($userId)
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
                new ValidUser($userId)
            ],
            'phone_number' => [
                'sometimes',
                'required',
                new ValidPhoneNumber,
                "unique:users,phone_number,{$userId},id",
                new ValidUser($userId)
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                "unique:users,email,{$userId},id",
                new ValidUser($userId)
            ]
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->phone_number) {
            $this->merge([
                'phone_number' => $this->cleanPhoneNumber($this->phone_number)
            ]);
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'full_name.sometimes' => 'Full Name must not be empty.',
            'full_name.required' => 'Full Name must not be empty.',
            'full_name.string' => 'Full Name must not be empty.',
        ];
    }
}
