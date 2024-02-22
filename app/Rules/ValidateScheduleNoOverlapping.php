<?php

namespace App\Rules;

use App\Models\LessonSchedule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ValidateScheduleNoOverlapping implements Rule
{
    protected $duration;
    protected $schedule;
    protected $timezone;

    public function __construct($schedule, $duration, $timezone)
    {
        $this->duration = $duration;
        $this->schedule = $schedule;
        $this->timezone = $timezone;
    }

    public function passes($attribute, $value)
    {
        if (!$this->validateTimezone()) {
            return false;
        }

        $scheduleStart = $this->parseScheduleStart($value);
        $scheduleEnd = $scheduleStart->copy()->addHours($this->duration);

        return !$this->hasOverlappingSchedules($scheduleStart, $scheduleEnd);
    }

    protected function validateTimezone()
    {
        $validator = Validator::make([
            'timezone' => $this->timezone,
        ], [
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        return !$validator->fails();
    }

    protected function parseScheduleStart($value)
    {
        return Carbon::parse($value, $this->timezone)->setTimezone('UTC');
    }

    protected function hasOverlappingSchedules($start, $end)
    {
        return LessonSchedule::whereHas('masterLesson', function ($query) {
            $query->where('user_id', auth()->user()->id);
        })
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($query) use ($start, $end) {
                    $query->where('schedule_start', '>=', $start)
                        ->where('schedule_start', '<', $end);
                })
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('schedule_end', '>', $start)
                            ->where('schedule_end', '<=', $end);
                    })
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('schedule_start', '<', $start)
                            ->where('schedule_end', '>', $end);
                    });
            })
            ->exists();
    }

    public function message()
    {
        return trans('validation.overlapping_schedule');
    }
}
