<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
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
            'id'              => $this->id,
            'category_id'     => $this->category_id,
            'subcategory_id'  => $this->subcategory_id,
            'title'           => $this->title,
            'description'     => $this->description,
            'price_offer'     => $this->price_offer,
            'suburb'          => $this->suburb,
            'author_id'       => $this->author_id,

            // Optional properties
            'comments_count'  => $this->when(isset($this->all_comments_count), $this->all_comments_count),
            'favorites_count' => $this->when(isset($this->favorites_count), $this->favorites_count),
            'is_favorite'     => $this->when(isset($this->is_favorite), $this->is_favorite),
            'is_reported'     => $this->when(isset($this->is_reported), $this->is_reported),

            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,

            'distance'        => $this->when(isset($this->distance), $this->distance),
            'latitude'        => $this->when(isset($this->latitude), $this->latitude),
            'longitude'       => $this->when(isset($this->longitude), $this->longitude),

            // Relationship
            'photos'          => MediaResource::collection($this->whenLoaded('photos')),
            'author'          => UserResource::make($this->whenLoaded('author')),
            'category'        => JsonResource::make($this->whenLoaded('category')),
            'subcategory'     => JsonResource::make($this->whenLoaded('subcategory'))
        ];
    }
}
