<?php

return [

    'providers' => [

        'google' => [
            'client_ids' => [
                env('GOOGLE_CLIENT_ID_1'),
                env('GOOGLE_CLIENT_ID_2'),
                env('GOOGLE_CLIENT_ID_3'),
                env('GOOGLE_CLIENT_ID_4'),
                env('GOOGLE_CLIENT_ID_5'),
            ]
        ],
        'facebook' => [
            'client_id' => env('FACEBOOK_APP_ID'),
            'client_secret' => env('FACEBOOK_APP_SECRET')
        ],
        'apple' => [
            'client_ids' => [
                env('APPLE_CLIENT_ID_1'),
                env('APPLE_CLIENT_ID_2'),
                env('APPLE_CLIENT_ID_3'),
            ]
        ],
    ]
];
