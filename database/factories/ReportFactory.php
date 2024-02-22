<?php

namespace Database\Factories;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\MasterLesson;
use App\Models\User;
use App\Models\Report;
use Faker\Generator as Faker;
use App\Models\ReportCategories;

$factory->define(Report::class, function (Faker $faker) {
    return [
        'reportable_type' => (new User)->getMorphClass(),
        'reportable_id'   => User::factory()->create()->id,
        'reason_id'       => factory(ReportCategories::class)->create()->id,
        'reported_by'     => User::factory()->create()->id,
        'description'     => $faker->sentence
    ];
});

$factory->state(Report::class, 'lesson', [
    'reportable_type' => (new MasterLesson())->getMorphClass(),
    'reportable_id'   => MasterLesson::factory()->create()->id,
]);
