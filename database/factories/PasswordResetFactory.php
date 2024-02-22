<?php

namespace Database\Factories;

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\PasswordReset;
use Faker\Generator as Faker;

$factory->define(PasswordReset::class, function (Faker $faker) {
    return [
        'user_id' => 1,
    ];
});
