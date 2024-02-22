<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'id'         => $this->id,
            'body'       => $this->body,
            'author_id'  => $this->author_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Optional properties
            'comments_count'     => $this->all_comments_count | 0,
            'favorites_count'    => $this->favorites_count | 0,
            'is_favorite'        => $this->is_favorite ?? false,

            // Relationship
            'author'    => UserResource::make($this->whenLoaded('author')),
            'photo'     => MediaResource::make($this->whenLoaded('photo'))
        ];
    }
}
