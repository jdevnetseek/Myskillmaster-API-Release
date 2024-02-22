<?php

namespace Database\Factories;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Enums\ReportCategoryType;
use App\Model;
use Faker\Generator as Faker;
use App\Models\ReportCategories;

$factory->define(ReportCategories::class, function (Faker $faker) {
    return [
        'label' => $faker->name
    ];
});

$factory->state(ReportCategories::class, 'lessonType', [
    'type' => ReportCategoryType::LESSONS,
]);
