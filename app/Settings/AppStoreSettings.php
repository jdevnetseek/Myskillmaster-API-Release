<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppStoreSettings extends Settings
{
    public ?string $store_url;

    public static function group(): string
    {
        return 'app-store';
    }
}
