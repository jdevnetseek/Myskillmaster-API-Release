<?php

namespace Database\Factories;

use App\Models\LessonSchedule;
use App\Models\MasterLesson;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LessonSchedule>
 */
class LessonScheduleFactory extends Factory
{
    protected $model = LessonSchedule::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $schedule = Carbon::now()->addDays($this->faker->numberBetween(1, 7))->setTime(14, 0, 0);

        return [
            'master_lesson_id' => MasterLesson::factory(),
            'schedule_start' => $schedule->format('Y-m-d H:i:s'),
            'duration_in_hours' => $this->faker->numberBetween(1, 3),
            'schedule_end' => $schedule->copy()->addHours($this->faker->numberBetween(1, 3)),
        ];
    }
}
