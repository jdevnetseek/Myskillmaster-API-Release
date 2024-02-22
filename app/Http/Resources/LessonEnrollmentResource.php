<?php

namespace App\Http\Resources;

use App\Http\Resources\Enrollment\LessonMasterResource;
use App\Http\Resources\Enrollment\LessonResource;
use App\Models\MasterLesson;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonEnrollmentResource extends JsonResource
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

            // flags
            'is_master' => $this->master_id == optional(auth()->user())->id,
            'is_cancelled_by_student' => $this->isCancelledByStudent(),
            'is_cancelled_by_master' => $this->isCancelledByMaster(),
            'is_refundable' => $this->isRefundable(),

            // timestamps
            'student_cancelled_at' => $this->student_cancelled_at,
            'master_cancelled_at' => $this->master_cancelled_at,
            'paid_at' => $this->paid_at,
            'refunded_at' => $this->refunded_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // amount data
            'lesson_price' => $this->lesson_price,
            'sub_total' => $this->sub_total,
            'application_fee_amount' => $this->application_fee_amount,
            'application_fee_rate' => $this->application_fee_rate,
            'grand_total' => $this->grand_total,
            'currency' => $this->currency,
            'analytics' => [
                'transaction_id' => $this->reference_code,
                'value' => $this->lesson_price,
                'currency' => "AUD",
                'items' => [[
                    'item_id' => $this->lesson->id,
                    'item_name' => $this->lesson->title,
                    'price' =>   $this->lesson_price,
                    'quantity' => 1,
                ]]
            ],

            // relationships
            'schedule'  => LessonScheduleResource::make($this->whenLoaded('schedule')),
            'lesson' => LessonResource::make($this->whenLoaded('lesson')),
            'master' => LessonMasterResource::make($this->whenLoaded('master')),
        ];
    }
}
