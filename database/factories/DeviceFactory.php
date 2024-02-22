<?php

namespace Database\Factories;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Device;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Device::class, function (Faker $faker) {
    return [
        'user_id' => 1,
        'token' => Str::random(64),
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36',
        'valid_until' => now()->addMonth(),
    ];
});
