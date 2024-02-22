<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MasterProfileResource extends JsonResource
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
            'about' => $this->about,
            'work_experiences' => $this->work_experiences,
            'languages' => $this->languages?->pluck('name'),
            'portfolio' => MediaResource::collection($this->whenLoaded('portfolio')),
        ];
    }
}
