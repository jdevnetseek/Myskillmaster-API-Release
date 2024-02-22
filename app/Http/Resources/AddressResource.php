<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'type'          => $this->type,
            'line1'         => $this->line1,
            'line2'         => $this->line2,
            'city'          => $this->city,
            'state'         => $this->state,
            'postal_code'   => $this->postal_code,
            'country_id'    => $this->country_id,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'country'       => $this->whenLoaded('country'),
        ];
    }
}
