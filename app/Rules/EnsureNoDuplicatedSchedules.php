<?php

namespace App\Rules;

use App\Models\LessonSchedule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class EnsureNoDuplicatedSchedules implements Rule
{

    public $timezone;

    public function __construct($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $overlappingIndices = [];
        foreach ($value as $index => $schedule) {
            if (LessonSchedule::hasConflict($schedule['schedule_start'], $schedule['schedule_end'], $schedule['dows'], $this->timezone)->first()) {
                $overlappingIndices[$index] = true;
                $errors[$attribute . '.' . $index . '.schedule_start'] = ['This schedule overlaps with another lesson schedule.'];
            }
        }

        if (!empty($overlappingIndices)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute field has overlapping schedules.';
    }
}
