<?php

namespace Database\Factories;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\MasterLesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'state' => $this->faker->state,
            'suburb' => $this->faker->city,
            'postcode' => $this->faker->postcode,
            'model_type' => $id = MasterLesson::factory()->create(),
            'model_type' => $id,
            'type' => AddressType::LESSON
        ];
    }
}
