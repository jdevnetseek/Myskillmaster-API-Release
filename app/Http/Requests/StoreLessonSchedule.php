<?php

namespace App\Http\Requests;

use App\Rules\EnsureNoDuplicatedSchedules;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\EnsureNoDuplicateScheduleOnPreviousLesson;
use Illuminate\Http\Request;


class StoreLessonSchedule extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(Request $request)
    {
        return [
            'master_lesson_id'  => ['required', 'integer', 'exists:master_lessons,id'],
            'timezone'              => ['required', 'string', 'timezone'],
            'lesson_schedules'      => ['required', 'array', new EnsureNoDuplicatedSchedules($this->timezone)],
            'lesson_schedules.*.schedule_start' => [
                'required',
                'date_format:Y-m-d H:i:s',
                new EnsureNoDuplicateScheduleOnPreviousLesson(
                    $request->input('schedules_start.*.schedule_start'),
                    $request->input('lesson_schedules.*.duration_in_hours'),
                    $request->input('timezone'),
                ),
            ],
            'lesson_schedules.*.duration_in_hours' => [
                'required',
                'integer'
            ],
            'lesson_schedules.*.duration_in_hours' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($value >= 24) {
                        $fail('The duration must be less than 24 hours.');
                    }
                },
            ],
            'slots'    => ['sometimes', 'integer']
        ];
    }

    public function messages()
    {
        return [
            'timezone.timezone' => 'The timezone field must be a valid timezone identifier.',
            'lesson_schedules.*.schedule_start.required' => 'The lesson schedule start field is required.',
            'lesson_schedules.*.duration_in_hours.required' => 'The lesson schedule duration in hours field is required.',
            'lesson_schedules.*.duration_in_hours.integer' => 'The duration in hours must be an integer.',
            'lesson_schedules.*.slots.integer' => 'The lesson schedule slot must be an integer.',
            'lesson_schedules.*.schedule_start.date_format' => 'The lesson schedule does not match the format Y-m-d H:i:s.',
        ];
    }
}
