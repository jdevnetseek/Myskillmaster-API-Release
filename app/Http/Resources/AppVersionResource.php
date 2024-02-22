<?php

namespace App\Http\Resources;

use App\Enums\UpgradeType;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Settings\Traits\InteractsWithPlatformSettings;

class AppVersionResource extends JsonResource
{
    use InteractsWithPlatformSettings;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $settings = $this->getPlatformSettings($this->platform);

        return [
            'id'            => $this->id,
            'platform'      => $this->platform,
            'version'       => $this->version,
            'upgrade_guide' => $this->upgrade_guide ?: UpgradeType::DEFAULT,
            'title'         => $this->title,
            'message'       => $this->message,
            'store_url'     => $this->store_url ?: $settings->store_url,
        ];
    }
}
