<?php

namespace Database\Factories;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Location;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Factory;


class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $longitude = $this->faker->longitude;
        $latitude  = $this->faker->latitude;

        return [
            'longitude'   => $longitude,
            'latitude'    => $latitude,
            'coordinates' => DB::raw("ST_SRID(Point(${longitude}, ${latitude}), 4326)")
        ];
    }
}
