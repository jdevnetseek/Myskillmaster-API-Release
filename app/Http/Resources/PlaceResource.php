<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlaceResource extends JsonResource
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
            'city' => $this->city,
            'state' => optional($this->state)->only(['name', 'short_name']),
            'country' => optional($this->country)->only(['name']),
            'formatted_address' => $this->formattedAddress
        ];
    }
}
