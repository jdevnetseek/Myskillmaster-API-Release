<?php

namespace App\Http\Resources;

use App\Support\Helper;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupedNotificationResource extends JsonResource
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
        $pluralUser = Str::plural('user', $this->count);

        switch ($type) {
            case 'comment':
                $message = 'commented on your post';
                break;

            case 'like':
                $message = 'like your post';
                break;

            case 'follow':
                $message = 'started following you';
                break;
        }

        $message = "{$this->count} {$pluralUser} {$message}";

        return [
            'id'            => $this->id,
            'type'          => $type,
            'notifiable_id' => $this->notifiable_id,
            'actor_id'      => Arr::get($this->data, 'actor_id'),
            'message'       => $message,
            'created_at'    => $this->created_at,
            'count'    => $this->count,

            'notifiable' => UserResource::make($this->whenLoaded('notifiable')),
            'actor'      => UserResource::make($this->whenLoaded('actor')),

            $this->mergeWhen($this->type == 'comment', [
                'comment_id' => Arr::get($this->data, 'comment_id'),
                'post_id'    => Arr::get($this->data, 'post_id'),
            ])
        ];
    }
}
