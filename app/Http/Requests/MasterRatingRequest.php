<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MasterRatingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'reference_code' => 'required|exists:lesson_enrollments,reference_code',
            'rating' => 'required|numeric|min:1|max:5'
        ];
    }
}
