<?php

namespace App\Http\Resources;

use App\Http\Resources\Enrollment\LessonMasterResource;
use App\Http\Resources\Enrollment\LessonResource;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentPastLessonResource extends JsonResource
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
            'reference_code' => $this->reference_code,
            'student_to_learn' => $this->to_learn,
            'status' => $this->status,

            // flags
            'is_master' => $this->master_id == optional(auth()->user())->id,
            'is_cancelled_by_student' => $this->isCancelledByStudent(),
            'is_cancelled_by_master' => $this->isCancelledByMaster(),

            // relationships
            'schedule'  => LessonScheduleResource::make($this->whenLoaded('schedule')),
            'lesson' => LessonResource::make($this->whenLoaded('lesson')),
            'master_profile' => LessonMasterProfileResource::make($this->whenLoaded('master')),


            // timestamps
            'student_cancelled_at' => $this->student_cancelled_at,
            'master_cancelled_at' => $this->master_cancelled_at,
            'is_attendance_confirmed' => $this->isAttendanceConfirmed(),
            'paid_at' => $this->paid_at,
            'refunded_at' => $this->refunded_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
