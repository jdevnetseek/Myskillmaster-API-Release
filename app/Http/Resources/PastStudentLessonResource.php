<?php

namespace App\Http\Resources;

use App\Http\Resources\MediaResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PastStudentLessonResource extends JsonResource
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
            'id' => $this->getKey(),
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_remote_supported'   => $this->is_remote_supported,
            'is_enrolled'           => $this->isEnrolledToPastLesson(),
            'is_reportable'         => $this->isReportableByUser(auth()->user()),
            'is_owner'              => $this->user_id == optional(auth()->user())->id,
            'place' => PlaceResource::make($this->whenLoaded('place')),
            'suburd' => $this->suburb,
            'state' => $this->state,
            'postcode' => $this->postcode,
            'tags'     => $this->tags->pluck('name'),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'cover_photo' => MediaResource::collection($this->whenLoaded('cover')),
        ];
    }
}
