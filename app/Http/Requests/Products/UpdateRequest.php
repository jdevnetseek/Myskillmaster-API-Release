<?php

namespace App\Http\Requests\Products;

use App\Enums\CategoryType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'       => ['sometimes', 'string'],
            'description' => ['sometimes', 'string'],
            'price'       => ['sometimes', 'numeric', 'min:0.1'],
            'category_id' => ['sometimes', Rule::exists('categories', 'id')->where('type', CategoryType::PRODUCT)],
            'places_id'   => ['sometimes'],
            'photos'      => ['sometimes', 'array']
        ];
    }
}
