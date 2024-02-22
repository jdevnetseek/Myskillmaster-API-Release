<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PageSettings extends Settings
{
    public ?string $privacy;

    public ?string $terms_of_service;

    public static function group(): string
    {
        return 'page-settings';
    }
}
