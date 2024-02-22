<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\EnsureNoDuplicatedSchedules;
use App\Rules\EnsureNoDuplicateScheduleOnPreviousLesson;
use Illuminate\Http\Request;

class ScheduleCheckerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(Request $request)
    {
        return [
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
            'lesson_schedules.*.duration_in_hours' => [
                'required',
                'integer'
            ],
        ];
    }

    public function messages()
    {
        return [
            'lesson_schedules.*.duration_in_hours.required' => 'The lesson schedule duration in hours field is required.',
            'lesson_schedules.*.duration_in_hours.integer' => 'The duration in hours must be an integer.',
            'lesson_schedules.*.schedule_start.date_format' => 'The lesson schedule does not match the format Y-m-d H:i:s.',
        ];
    }
}
