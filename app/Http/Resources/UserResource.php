<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the user into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                         => $this->id,
            'full_name'                  => $this->full_name,
            'first_name'                 => $this->first_name,
            'last_name'                  => $this->last_name,
            'username'                   => $this->username,
            'email'                      => $this->email,
            'birthdate'                  => $this->birthdate,
            'created_at'                 => $this->created_at,
            'updated_at'                 => $this->updated_at,
            'blocked_at'                 => $this->blocked_at,
            'onboarded_at'               => $this->onboarded_at,
            'primary_username'           => $this->primary_username,
            'place_id'                   => $this->place_id,
            'is_subscribed'              => $this->is_subscribed,
            'can_request_payout'         => $this->canRequestPayout(),

            // Computed attributes
            'email_verified'             => $this->isEmailVerified(),
            'verified'                   => $this->isVerified(),
            'has_master_profile'         => $this->hasMasterProfile(),
            'avatar_permanent_url'       => route('user.avatar.show', ['id' => $this->id, 'timestamp' => strval(optional($this->updated_at)->timestamp)]),
            'avatar_permanent_thumb_url' => route('user.avatar.showThumb', ['id' => $this->id, 'timestamp' => strval(optional($this->updated_at)->timestamp)]),
            'mine'                       => $this->id == optional(auth()->user())->id,

            // Relationship
            'avatar'                     => MediaResource::make($this->whenLoaded('avatar')),
            'address'                    => PlaceResource::make($this->whenLoaded('address')),
            'master_details'             => MasterProfileResource::make($this->whenLoaded('masterProfile')),

            'master_interests'           => LessonPreferenceResource::collection($this->whenLoaded('lessonPreferences')),

            'posted_lesson_categories' => CategoryResource::collection($this->whenLoaded('distinctLessonCategories')),
            'average_rating'           => $this->masterAverageRating(),
            'no_of_reviews'            => $this->masterNoOfReviews(),
        ];
    }
}
