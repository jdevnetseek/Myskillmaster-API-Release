<?php

namespace App\Http\Requests\Category;

use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
                'required',
                'string',
                'max:255',
                Rule::unique('categories')
                    ->where('type', $this->input('type', CategoryType::LESSON))
            ],
            'icon' => ['sometimes', 'required', 'image'],
        ];
    }
}
