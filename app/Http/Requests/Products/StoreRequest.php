<?php

namespace App\Http\Requests\Products;

use App\Enums\CategoryType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'       => ['required', 'string'],
            'description' => ['required', 'string'],
            'price'       => ['required', 'min:0.50'],
            'currency'    => ['sometimes'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', CategoryType::PRODUCT)],
            'places_id'   => ['sometimes'],
            'photos'      => ['required', 'array'],
            'photos.*'    => ['image'],
        ];
    }
}
