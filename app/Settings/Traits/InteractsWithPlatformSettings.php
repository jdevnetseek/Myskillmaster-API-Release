<?php

namespace App\Settings\Traits;

use App\Enums\Platform;
use App\Settings\AppStoreSettings;
use App\Settings\PlayStoreSettings;

trait InteractsWithPlatformSettings
{
    protected $platformSettings = [
        Platform::IOS     => AppStoreSettings::class,
        Platform::ANDROID => PlayStoreSettings::class
    ];

    public function getPlatformSettings(string $platform)
    {
        return app($this->platformSettings[$platform]);
    }
}
