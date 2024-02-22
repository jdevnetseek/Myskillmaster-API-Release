<?php

namespace App\Http\Requests;

use App\Enums\CancellationReason;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;

class StudentLessonCancellationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'cancellation_reason' => ['required', 'string', new EnumValue(CancellationReason::class)],
            'cancellation_remarks' => ['string',  'max:500', 'nullable'],
        ];
    }
}
