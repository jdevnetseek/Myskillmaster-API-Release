<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\LessonSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class EnsureNoDuplicateScheduleOnPreviousLesson implements Rule
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
        try {
            foreach ($this->duration as $key => $duration) {
                $scheduleStart = $this->parseScheduleStart($value);
                $scheduleEnd = $scheduleStart->copy()->addHours($this->duration);

                return !$this->hasOverlappingSchedules($scheduleStart, $scheduleEnd);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
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

    protected function hasOverlappingSchedules($scheduleStart, $scheduleEnd)
    {
        return LessonSchedule::whereHas('masterLesson', function ($query) {
            $query->where('user_id', auth()->user()->id);
        })
            ->where(function ($query) use ($scheduleStart, $scheduleEnd) {
                $query->whereBetween('schedule_start', [$scheduleStart, $scheduleEnd])
                    ->orWhereBetween('schedule_end', [$scheduleStart, $scheduleEnd])
                    ->orWhere(function ($query) use ($scheduleStart, $scheduleEnd) {
                        $query->where('schedule_start', '<', $scheduleStart)
                            ->where('schedule_end', '>', $scheduleEnd);
                    });
            })
            ->exists();
    }

    public function message()
    {
        return trans('validation.overlapping_schedule');
    }
}
