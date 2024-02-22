<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'   => ['required', 'string', 'max:255'],
            'source' => ['required', 'string'],
            'plan'   => ['required', 'numeric', 'exists:plans,id'],
        ];
    }
}
