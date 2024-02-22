<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMasterLessonRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $regexAcceptCentavos = 'regex:/^\d+(\.\d{1,2})?$/';
        return [
            'title'              => ['sometimes', 'required', 'string', 'max:255'],
            'description'        => ['sometimes', 'required', 'string', 'max:500'],
            'duration_in_hours'  => ['sometimes', 'required', 'integer'],
            'lesson_price'       => ['sometimes', 'required', 'numeric', $regexAcceptCentavos, 'gt:0'],
            'available_days'     => ['sometimes', 'required', 'array'],
            'category_id'        => ['sometimes', 'required', 'exists:categories,id'],
            'place_id'           => ['sometimes', 'required', 'exists:places,id'],
            'is_remote_supported' => ['sometimes', 'required', 'boolean'],
            'cover_photo'        => ['sometimes', 'required', 'array'],
            'cover_photo.*'      => ['sometimes', 'image', 'max:10240'],
            'tags'               => ['sometimes', 'required', 'array'],
            'state'   => ['sometimes', 'string', 'max:255'],
            'suburb' => ['sometimes', 'string', 'max:255'],
            'postcode' => ['sometimes', 'string', 'max:255'],
            'address_or_link'    => ['sometimes', 'required', 'string', 'max:500'],
        ];
    }
}
