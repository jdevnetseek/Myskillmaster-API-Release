<?php

namespace App\Http\Requests;

use App\Models\LessonSchedule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\EnsureNoDuplicatedSchedules;
use Illuminate\Http\Request;
use App\Rules\EnsureNoDuplicateScheduleOnPreviousLesson;

class StoreMasterLessonRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        $regexAcceptCentavos = 'regex:/^\d+(\.\d{1,2})?$/';
        return [
            'title'                 => ['required', 'string', 'max:255'],
            'description'           => ['required', 'string', 'max:500'],
            'lesson_price'          => ['required', 'numeric', $regexAcceptCentavos, 'gt:0'],
            'timezone'              => ['required', 'string', 'timezone'],
            'lesson_schedules'      => [
                'required',
                'array',
                new EnsureNoDuplicatedSchedules($this->timezone)
            ],
            'lesson_schedules.*.schedule_start' => [
                'required',
                'date_format:Y-m-d H:i:s',
                new EnsureNoDuplicateScheduleOnPreviousLesson(
                    $request->input('schedules_start.*.schedule_start'),
                    $request->input('lesson_schedules.*.duration_in_hours'),
                    $request->input('timezone'),
                ),
            ],
            'lesson_schedules.*.schedule_end' => 'required|date_format:Y-m-d H:i:s',
            'lesson_schedules.*.duration_in_hours' => [
                'required',
                'integer'
            ],
            'lesson_schedules.*.dows' => 'required|array',
            'lesson_schedules.*.recurrence' => 'required|string|max:100',
            'lesson_schedules.*.slots'    => ['sometimes', 'integer'],
            'category_id'           => ['required', 'exists:categories,id'],
            'place_id'              => ['sometimes', 'exists:places,id'],
            'is_remote_supported'   => ['required', 'boolean'],
            'cover_photo'           => ['required', 'array'],
            'cover_photo.*'         => ['image', 'max:10240'],
            'tags'                  => ['required', 'array'],
            'address_or_link'       => ['required', 'string', 'max:500'],
            'suburb'    => ['required', 'string', 'max:255'],
            'state'     => ['required', 'string', 'max:255'],
            'postcode'  => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'lesson_schedules.*.duration_in_hours.required' => 'The lesson schedule duration in hours field is required.',
            'lesson_schedules.*.duration_in_hours.integer' => 'The duration in hours must be an integer.',
            'lesson_schedules.*.slots.integer' => 'The lesson schedule slot must be an integer.',
            'lesson_schedules.*.schedule_start.date_format' => 'The lesson schedule does not match the format Y-m-d H:i:s.',
            'cover_photo.*.max' => 'The cover photo may not be greater than 10MB.',
            'cover_photo.*.image' => 'The cover photo must be an image.',
            'lesson_price.min' => 'The lesson price must be equal to zero or negative number.',
            'address_or_link.max' => 'The exact address or meeting link may not be greater than 500 characters.',
            'address_or_link.required' => 'The exact address or meeting link field is required.',
            'lesson_price.gt' => 'The lesson price must be greater than zero.',
        ];
    }
}
