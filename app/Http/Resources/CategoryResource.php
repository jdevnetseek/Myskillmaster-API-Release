<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'label'         => $this->label,
            'slug'          => $this->slug,
            'type'          => $this->type,
            'category'      => CategoryResource::collection($this->whenLoaded('category')),
            'subcategories' => CategoryResource::collection($this->whenLoaded('subcategories')),
            'icon_url'      => $this->relationLoaded('icon') ? $this->icon?->getFullUrl() : null,
        ];
    }
}
