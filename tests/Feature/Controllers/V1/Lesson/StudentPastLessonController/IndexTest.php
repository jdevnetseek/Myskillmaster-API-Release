<?php

namespace Tests\Feature\Controllers\V1\Lesson\StudentPastLessonController;

use App\Models\LessonEnrollment;
use App\Models\LessonSchedule;
use App\Models\MasterLesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function student_should_be_able_to_see_all_past_lesson()
    {
        $this->withoutExceptionHandling();

        $student = User::factory()->create();
        $master = User::factory()->create();

        $master = User::factory()
            ->hasMasterProfile()
            ->has(MasterLesson::factory())
            ->create();

        $schedule_start = Carbon::now()->subDay(1)->setTime(14, 0, 0);

        $schedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $master->masterLesson->getKey(),
            'schedule_start' => $schedule_start->format('Y-m-d H:i:s'),
            'duration_in_hours' => $this->faker->numberBetween(1, 3),
            'schedule_end' => $schedule_start->copy()->addHours($this->faker->numberBetween(1, 3)),
        ]);

        $enrollment = LessonEnrollment::factory()
            ->for($master, 'master')
            ->for($student, 'student')
            ->create([
                'schedule_id' => $schedule->getKey(),
                'lesson_id' => $master->masterLesson->getKey(),
                'lesson_price' => 100,
                'to_learn' => 'I want to learn this',
                'master_rated_at' => now(),
                'is_student_attended' => true,
            ]);

        $response = $this->actingAs($student)
            ->getJson('/api/v1/student/past/lessons');

        $response->assertOk();
        $response->assertJsonStructure([
            "data" => [
                "*" => [
                    'reference_code',
                    'student_to_learn',
                    'status',
                    'is_master',
                    'is_cancelled_by_student',
                    'is_cancelled_by_master',
                    'schedule',
                    'lesson',
                    'master_profile',
                    'student_cancelled_at',
                    'master_cancelled_at',
                    'is_attendance_confirmed',
                    'paid_at',
                    'refunded_at',
                    'created_at',
                    'updated_at',
                ]
            ],
            "links",
            "meta"
        ]);
    }
}
