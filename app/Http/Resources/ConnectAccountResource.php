<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConnectAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @link http://url.comhttps://stripe.com/docs/api/accounts/object
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'business_type'     => $this->business_type,
            'capabilities'      => $this->capabilities,
            'company'           => $this->company,
            'country'           => $this->country,
            'email'             => $this->email,
            'individual'        => $this->individual,
            'metadata'          => $this->metadata,
            'requirements'      => $this->requirements,
            'tos_acceptance'    => $this->tos_acceptance,
            'type'              => $this->type,
            'object'            => $this->object,
            'business_profile'  => $this->business_profile,
            'charges_enabled'   => $this->charges_enabled,
            'created'           => $this->created,
            'default_currency'  => $this->default_currency,
            'details_submitted' => $this->details_submitted,
            'external_accounts' => $this->external_accounts,
            'payouts_enabled'   => $this->payouts_enabled,
            'settings'          => $this->settings
        ];
    }
}
