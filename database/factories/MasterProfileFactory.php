<?php

namespace Database\Factories;

use App\Models\MasterProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MasterProfile>
 */
class MasterProfileFactory extends Factory
{
    protected $model = MasterProfile::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->create()->getKey(),
            'about' => $this->faker->paragraph(100),
            'work_experiences' => $this->faker->paragraph(10)
        ];
    }
}
