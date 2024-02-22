<?php

namespace App\Models\Traits;

use App\Enums\AddressType;
use App\Models\Category;
use App\Models\MasterLesson as ModelsMasterLesson;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

trait HasMasterLesson
{
    public function masterLesson(): HasOne
    {
        return $this->HasOne(ModelsMasterLesson::class);
    }

    public function createMasterLesson(array $data): ModelsMasterLesson
    {
        $data = collect($data);

        $lesson = $this->masterLesson()->create();
        $lesson->fill($data->toArray());

        $lesson->save();

        if ($data->has('lesson_schedules')) {
            $lessonScheduleArray = $data->get('lesson_schedules');
            foreach ($lessonScheduleArray as $key => $lessonSchedule) {
                $schedule = Carbon::parse($lessonSchedule['schedule_start'], $data['timezone']);
                $scheduleUtc = $schedule->setTimezone('UTC');
                $scheduleEndUtc = $scheduleUtc->copy()->addHours($lessonScheduleArray[$key]['duration_in_hours']);

                $lessonScheduleArray[$key]['schedule_start'] = Carbon::instance($scheduleUtc);
                $lessonScheduleArray[$key]['schedule_end'] = Carbon::instance($scheduleEndUtc);
                $lessonScheduleArray[$key]['dows'] = implode(',', $lessonScheduleArray[$key]['dows']);
            }
            $lesson->schedules()->createMany($lessonScheduleArray);
        }

        if ($data->has('cover_photo')) {
            $lesson->addCoverPhoto($data->get('cover_photo'));
        }

        if ($data->has('tags')) {
            $lesson->attachTags($data->get('tags'));
        }


        return $lesson->fresh();
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(ModelsMasterLesson::class);
    }

    public function lessonCount(): int
    {
        return $this->lessons()->count();
    }

    public function lessonCategories(): HasManyThrough
    {
        return $this->hasManyThrough(
            Category::class,
            ModelsMasterLesson::class,
            'user_id', // foreign key on master_lessons
            'id',
            secondLocalKey: 'category_id' // local key on master lessons
        );
    }

    public function distinctLessonCategories(): HasManyThrough
    {
        return $this->lessonCategories()->distinct('id');
    }
}
