<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'payment_method_id' => ['string', Rule::requiredIf(is_null($this->payment_token))],
            'payment_token' => ['string', Rule::requiredIf(is_null($this->payment_method_id))],
            'schedule_id' => [
                'required',
                Rule::exists('lesson_schedules', 'id')->where(function ($query) {
                    return $query->whereMasterLessonId($this->lesson->getKey());
                })
            ]
        ];
    }
}
