<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'body'            => $this->body,
            'author_id'       => $this->author_id,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'responses_count' => $this->when(isset($this->responses_count), $this->responses_count),
            'author'          => UserResource::make($this->whenLoaded('author')),
            'responses'       => CommentResource::collection($this->whenLoaded('responses'))
        ];
    }
}
