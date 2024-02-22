<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reason_ids'    => ['bail', 'required', 'array'],
            'reason_ids.*'  => ['bail', 'required', 'exists:report_categories,id'],
            'description'   => ['bail', 'nullable', 'string'],
            'attachments'   => ['bail', 'nullable', 'array'],
            'attachments.*' => ['image']
        ];
    }
}
