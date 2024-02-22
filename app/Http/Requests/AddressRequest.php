<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
     /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'full_name'    => 'nullable',
            'phone_number' => 'nullable',
            'line1'        => 'sometimes',
            'line2'        => 'nullable',
            'city'         => 'sometimes',
            'state'        => 'sometimes',
            'postal_code'  => 'sometimes',
            'country_id'   => 'sometimes',
        ];
    }
}
