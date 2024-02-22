<?php

namespace Tests\Feature\Controllers\V1\Lesson\LessonToLearnController;

use App\Models\LessonEnrollment;
use App\Models\LessonSchedule;
use App\Models\MasterLesson;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FinishedLessonsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTableSeeder();
    }

    /** @test */
    public function  student_should_be_able_to_see_all_completed_lessons_that_have_not_yet_confirmed_attending()
    {
        $student = User::factory()->create();
        $master = User::factory()->create();

        $master = User::factory()
            ->hasMasterProfile()
            ->has(MasterLesson::factory())
            ->create();

        // $schedule = $master->masterLesson->schedules()->create([
        //     'schedule_start' => Carbon::now()->addDay(1)->format('Y-m-d H:i:s'),
        //     'duration_in_hours' => 1,
        //     'timezone' => 'Asia/Manila'
        // ]);

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
                'master_rated_at' => null,
                'is_student_attended' => null,
            ]);

        $response = $this->actingAs($student)->getJson('/api/v1/finished/lessons');

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
                    'paid_at',
                    'refunded_at',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
    }

    // add test to make sure that the rated lessons or mark as not attended won't be included in the list
    public function test_lessons_that_are_marked_as_not_attended_should_not_be_included_in_finished_lessons()
    {
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

        $expectedTotalEnrollments = 2;
        LessonEnrollment::factory()
            ->for($master, 'master')
            ->for($student, 'student')
            ->count($expectedTotalEnrollments)
            ->create([
                'schedule_id' => $schedule->getKey(),
                'lesson_id' => $master->masterLesson->getKey(),
                'lesson_price' => 100,
                'to_learn' => 'I want to learn this',
                'master_rated_at' => null,
                'is_student_attended' => null,
            ]);

        $notAttendedLessons = LessonEnrollment::factory()
            ->for($master, 'master')
            ->for($student, 'student')
            ->create([
                'schedule_id' => $schedule->getKey(),
                'lesson_id' => $master->masterLesson->getKey(),
                'lesson_price' => 100,
                'to_learn' => 'I want to learn this',
                'master_rated_at' => null,
                'is_student_attended' => false,
            ]);

        $response = $this->actingAs($student)->getJson('/api/v1/finished/lessons');

        $this->assertCount($expectedTotalEnrollments, data_get($response, 'data'));
    }

    public function test_lessons_that_are_rated_should_not_be_included_in_finished_lessons()
    {
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

        $expectedTotalEnrollments = 2;
        LessonEnrollment::factory()
            ->for($master, 'master')
            ->for($student, 'student')
            ->count($expectedTotalEnrollments)
            ->create([
                'schedule_id' => $schedule->getKey(),
                'lesson_id' => $master->masterLesson->getKey(),
                'lesson_price' => 100,
                'to_learn' => 'I want to learn this',
                'master_rated_at' => null,
                'is_student_attended' => null,
            ]);

        LessonEnrollment::factory()
            ->for($master, 'master')
            ->for($student, 'student')
            ->create([
                'schedule_id' => $schedule->getKey(),
                'lesson_id' => $master->masterLesson->getKey(),
                'lesson_price' => 100,
                'to_learn' => 'I want to learn this',
                'master_rated_at' => now(),
                'is_student_attended' => null,
            ]);

        $response = $this->actingAs($student)->getJson('/api/v1/finished/lessons');

        $this->assertCount($expectedTotalEnrollments, data_get($response, 'data'));
    }

    private function seedTableSeeder()
    {
        $this->artisan('db:seed', ['--class' => 'CountriesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlacesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoriesTableSeeder']);
    }
}
