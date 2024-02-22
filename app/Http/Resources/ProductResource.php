<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\Traits\HasMappedResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    use HasMappedResource;

    /**
     * The list of Models class to be mapped to a resource
     *
     * @return array
     */
    protected function mappedResource() : array
    {
        return [
            User::class => UserResource::class
        ];
    }

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
            'title'           => $this->title,
            'description'     => $this->description,
            'price'           => $this->price,
            'price_in_cents'  => $this->price_in_cents,
            'formatted_price' => $this->formatted_price,
            'currency'        => $this->currency,
            'category_id'     => $this->category_id,
            'places_id'       => $this->places_id,
            'places_address'  => $this->places_address,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'deleted_at'      => $this->deleted_at,

            'distance'       => $this->when(isset($this->distance), $this->distance),
            'latitude'       => $this->when(isset($this->latitude), $this->latitude),
            'longitude'      => $this->when(isset($this->longitude), $this->longitude),

            // Relationship
            'photos'      => MediaResource::collection($this->whenLoaded('photos')),
            'seller'      => $this->whenLoaded('seller', function () {
                return $this->getMappedResource($this->seller);
            }),
            'category'    => CategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
