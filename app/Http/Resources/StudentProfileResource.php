<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,

            'avatar_permanent_url' => route(
                'user.avatar.show',
                [
                    'id' => $this->id, 'timestamp' => strval(optional($this->updated_at)->timestamp)
                ]
            ),
            'avatar_permanent_thumb_url' => route(
                'user.avatar.showThumb',
                ['id' => $this->id, 'timestamp' => strval(optional($this->updated_at)->timestamp)]
            ),

            'address' => PlaceResource::make($this->whenLoaded('address')),
            'avatar' => $this->whenLoaded('avatar'),

            'to_learn' => optional($this)->to_learn,
        ];
    }
}
