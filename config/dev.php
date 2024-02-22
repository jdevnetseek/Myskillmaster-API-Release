<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dev Console Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable all dev console features
    |
    */
    'enable_devconsole' => env('ENABLE_DEVCONSOLE', true),

    /*
    |--------------------------------------------------
    | Dev Console Credentials
    |--------------------------------------------------
    |
    | This option defines the credentials that can
    | be use to access dev console pages
    |
    */

    'dev_console_username' => env('DEV_CONSOLE_USERNAME', 'dev@appetiser.com.au'),
    'dev_console_password' => env('DEV_CONSOLE_PASSWORD', 'AwesomeAppetiser'),

    /*
    |--------------------------------------------------------------------------
    | Telescope Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable all Telescope watchers regardless
    | of their individual configuration, which simply provides a single
    | and convenient way to enable or disable Telescope data storage.
    |
    */
    'telescope_enabled' => env('TELESCOPE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Horizon Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable all Horizon fuctionality regardless
    | of their individual configuration, which simply provides a single
    | and convenient way to enable or disable Horizon fuctionality.
    |
    */
    'horizon_enabled' => env('HORIZON_ENABLED', true),
];
