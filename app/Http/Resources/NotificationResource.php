<?php

namespace App\Http\Resources;

use App\Support\Helper;
use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $type = Helper::getClassName($this->type, true);

        return [
            'id'            => $this->id,
            'type'          => $type,
            'notifiable_id' => $this->notifiable_id,
            'actor_id'      => Arr::get($this->data, 'actor_id'),
            'message'       => Arr::get($this->data, 'message'),
            'read_at'       => $this->read_at,
            'read'          => !!$this->read_at,
            'created_at'    => $this->created_at,

            'notifiable' => UserResource::make($this->whenLoaded('notifiable')),
            'actor'      => UserResource::make($this->whenLoaded('actor')),

            $this->mergeWhen($this->type == 'comment', [
                'comment_id' => Arr::get($this->data, 'comment_id'),
                'post_id'    => Arr::get($this->data, 'post_id'),
            ])
        ];
    }
}
