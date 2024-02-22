<?php

namespace App\Http\Resources\Subscription;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'stripe_plan' => $this->stripe_plan,
            'price'       => $this->amount,
            'description'   => $this->description,
            'plan_features' => $this->included,
            'is_subscribed' => $this->is_subscribed,
            'avail_free_trial_before' => $this->avail_freeTrial_before,
            'on_free_trial_period'  => $this->on_free_trial,
            'is_recommended' => (bool)$this->is_recommended_plan,
            'recommended_for_trial' => (bool)$this->is_recommended_for_trial,
            'subscription_details' => $this->subscription,
            'created_at' => $this->created_at
        ];
    }
}
