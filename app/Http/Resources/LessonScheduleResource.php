<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'master_lesson_id' => $this->master_lesson_id,
            'schedule_start' => $this->schedule_start,
            'schedule_end' => $this->schedule_end,
            'dows' => $this->dows,
            'recurrence' => $this->recurrence,
            'frequency' => $this->frequency,
            'period' => $this->period,
            'label' => $this->label,
            'slots' => $this->slots,
            'lesson_duration' => $this->duration_in_hours,
            'is_available_for_enrollment' => $this->hasAvailableSlotsForEnrollment(),
            'number_of_students_enrolled' => $this->numberOfStudentsEnrolled(),
        ];
    }
}
