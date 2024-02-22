<?php

namespace App\Http\Resources;

use App\Http\Resources\Enrollment\LessonResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterPastLessonResource extends JsonResource
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
            'slots' => $this->slots,
            'duration_in_hours' => $this->duration_in_hours,
            'number_of_students_enrolled' => $this->numberOfStudentsEnrolled(),
            'is_attendance_confirmed' => $this->isAttendanceConfirmed(),

            // relationships
            'lesson' => LessonResource::make($this->whenLoaded('masterLesson')),
            'master_profile' => LessonMasterProfileResource::make($this->whenLoaded('masterProfile')),
            'students_enrolled' => StudentProfileResource::collection($this->whenLoaded('studentProfile')),

            //timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
