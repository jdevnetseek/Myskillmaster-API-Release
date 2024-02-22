<?php

namespace App\Http\Requests;

use App\Rules\UnassignedMedia;
use App\Support\ValidatesPhone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name'  => ['sometimes', 'string', 'max:255'],
            'last_name'   => ['sometimes', 'string', 'max:255'],
            'place_id'    => ['sometimes', 'exists:places,id'],
            'birthdate'   => ['sometimes', 'date', 'date_format:Y-m-d'],
            'avatar'      => ['nullable', new UnassignedMedia],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'First Name',
            'last_name'  => 'Last Name',
        ];
    }
}
