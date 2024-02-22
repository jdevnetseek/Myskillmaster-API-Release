<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LessonRequest>
 */
class LessonEnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'reference_code' => $this->faker->uuid,
            'student_id' => \App\Models\User::factory(),
            'schedule_id' => \App\Models\LessonSchedule::factory(),
            'lesson_id' => \App\Models\MasterLesson::factory(),
            'master_id' => \App\Models\User::factory(),
            'lesson_price' => $this->faker->randomFloat(2, 0, 100),
            'to_learn' => $this->faker->sentence,
            'master_rated_at' => $this->faker->dateTime,
        ];
    }
}
