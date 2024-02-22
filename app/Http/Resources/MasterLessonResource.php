<?php

namespace App\Http\Resources;

use App\Http\Requests\AddressRequest;
use Spatie\Tags\Tag;

use Illuminate\Http\Resources\Json\JsonResource;

class MasterLessonResource extends JsonResource
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
            'id'                => $this->id,
            'user_id'           => $this->user_id,
            'title'             => $this->title,
            'slug'              => $this->slug,
            'description'       => $this->description,
            'duration_in_hours' => $this->duration_in_hours,
            'lesson_price'      => $this->lesson_price,
            'currency'          => $this->currency,
            'lesson_schedules'    => LessonScheduleResource::collection($this->whenLoaded('schedules')),
            'is_remote_supported'   => $this->is_remote_supported,
            'active'                => $this->active,
            'is_owner'              => $this->user_id == optional(auth()->user())->id,
            'is_enrolled'           => $this->isEnrolled(),
            'is_reportable'         => $this->isReportableByUser(auth()->user()),
            'tags'                  => $this->tags->pluck('name'),
            'suburb'                => $this->suburb,
            'postcode'              => $this->postcode,
            'state'                 => $this->state,
            'place'                 => PlaceResource::make($this->whenLoaded('place')),
            'category'              => CategoryResource::make($this->whenLoaded('category')),
            'cover_photo'           => MediaResource::collection($this->whenLoaded('cover')),
            'master_profile'        => UserResource::make($this->whenLoaded('user')),
            'created_at'            => $this->created_at,
        ];
    }
}
