<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'          => ['sometimes', 'string', 'max:255'],
            'description'    => ['sometimes', 'string'],
            'category_id'    => ['sometimes', 'exists:categories,id'],
            'subcategory_id' => ['sometimes', 'exists:categories,id'], // @todo Check subcategory is under category
            'price_offer'    => ['sometimes', 'numeric'],
            'suburb'         => ['sometimes', 'string'],
            'latitude'       => ['required_with:longitude', 'numeric', 'min:-90', 'max:90'],
            'longitude'      => ['required_with:latitude', 'numeric', 'min:-180', 'max:180'],
            'photos'         => ['array']
        ];
    }
}
