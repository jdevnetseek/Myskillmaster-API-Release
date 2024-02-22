<?php

namespace App\Http\Requests\Category;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'label' => [
                'sometimes',
                'required',
                'max:255',
                Rule::unique('categories')
                    ->where('type', $this->category->type)
                    ->whereNot('id', $this->category->getKey())
            ],
            'keywords' => ['sometimes', 'array'],
            'icon' => ['sometimes', 'image'],
        ];
    }
}
