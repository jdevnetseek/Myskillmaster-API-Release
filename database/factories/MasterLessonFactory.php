<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\LessonSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MasterLesson>
 */
class MasterLessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id'        => User::factory()->create()->getKey(),
            'title'          => $this->faker->word,
            'description'    => $this->faker->sentence,
            'lesson_price'   => $this->faker->numberBetween(1000, 10000),
            'currency'       => config('cashier.currency'),
            'duration_in_hours' => $this->faker->numberBetween(1, 3),
            'category_id'    => Category::factory()->create(['type' => CategoryType::LESSON])->getKey(),
            'place_id'      => $this->faker->uuid,
            'address_or_link' => $this->faker->url,
            'suburb' => $this->faker->city,
            'state' => $this->faker->state,
            'postcode' => $this->faker->postcode,
            'tags' => ['tag1', 'tag2'],
        ];
    }

    public function lessonSchedules(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'lesson_schedules' => LessonSchedule::factory()->count(3)->create([
                'master_lesson_id' => $attributes['id'],
            ]),
        ]);
    }
    public function remoteNotSupported(): Factory
    {
        return $this->state(fn (array $attributes) => ['is_remote_supported' => false]);
    }

    public function active(): Factory
    {
        return $this->state(fn (array $attributes) => ['active' => true]);
    }
}
