<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PlayStoreSettings extends Settings
{
    public ?string $store_url;

    public static function group(): string
    {
        return 'play-store';
    }
}
