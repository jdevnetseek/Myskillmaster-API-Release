<?php

namespace App\Http\Requests;

use App\Rules\ValidImage;
use Illuminate\Foundation\Http\FormRequest;

class MasterProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'about' => ['sometimes', 'required', 'max:500'],
            'work_experiences' => ['sometimes', 'required', 'max:500'],
            'languages' => ['sometimes', 'array'],
            'portfolio' => ['array'],
            'portfolio.*' => [new ValidImage],
        ];
    }
}
