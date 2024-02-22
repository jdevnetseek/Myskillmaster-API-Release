<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['required', 'string'],
            'category_id'    => ['required', 'exists:categories,id'],
            'subcategory_id' => ['required', 'exists:categories,id'], // @todo Check subcategory is under category
            'price_offer'    => ['required', 'numeric'],
            'suburb'         => ['required', 'string'],
            'photos'         => ['required', 'array'],
            'photos.*'       => ['image'],
            'latitude'       => ['required_with:longitude', 'numeric', 'min:-90', 'max:90'],
            'longitude'      => ['required_with:latitude', 'numeric', 'min:-180', 'max:180'],
        ];
    }
}
