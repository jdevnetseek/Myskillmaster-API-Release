<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetLessonPreferenceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'category_ids' => ['array'],
            'category_ids.*' => [
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->whereType(CategoryType::LESSON);
                }),
            ],
        ];
    }

    public function messages()
    {
        return [
            'category_ids.*.exists' => __('validation.invalid_lesson_category'),
        ];
    }
}
