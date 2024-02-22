<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => fn () => User::factory()->create()->id,
            'rateable_id' => fn () => User::factory()->create()->id,
            'rateable_type' => fn () => User::class,
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->sentence
        ];
    }
}
