<?php

namespace Tests\Feature\Controllers\V1\Lesson\LessonToLearnController;

use App\Models\Address;
use App\Models\LessonEnrollment;
use App\Models\LessonSchedule;
use App\Models\MasterLesson;
use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTableSeeder();
    }

    /** @test */
    public function student_should_only_see_lessons_to_learn_that_he_or_she_enrolled()
    {
        $student = User::factory()->create();
        $master = User::factory()->create();

        $place = Place::inRandomOrder()->first();

        // Create a master lesson owned by the authenticated user
        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $master->id,
            'place_id' => $place->id,
        ]);

        // Create a lesson schedule for the lesson enrollment
        $lessonSchedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $masterLesson->id,
        ]);

        // Create a lesson enrollment for the master lesson
        $lessonEnrollment = LessonEnrollment::factory()->create([
            'student_id' => $student->getKey(),
            'schedule_id' => $lessonSchedule->getKey(),
            'lesson_id' =>  $masterLesson->id,
            'master_id' => $master->getKey(),
            'lesson_price' => $this->faker->randomFloat(2, 0, 100),
        ]);

        $response = $this->actingAs($student)->getJson('/api/v1/lessons/student/to-learn');

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

    /** @test */
    public function unauthenticated_student_should_not_be_able_to_view_list_of_lesson_to_learn()
    {
        $response = $this->getJson('/api/v1/lessons/student/to-learn');

        $response->assertUnauthorized();
    }

    /** @test */
    public function student_should_only_see_lessons_to_learn_that_is_not_cancelled()
    {
        $student = User::factory()->create();
        $master = User::factory()->create();

        $place = Place::inRandomOrder()->first();

        // Create a master lesson owned by the authenticated user
        $masterLesson = MasterLesson::factory()
            ->has(Address::factory())
            ->create([
                'user_id' => $master->id,
                'place_id' => $place->id,
            ]);

        // Create a lesson schedule for the lesson enrollment
        $lessonSchedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $masterLesson->id,
        ]);

        // Create a lesson enrollment for the master lesson
        $cancelledLesson = LessonEnrollment::factory()->create([
            'student_id' => $student->getKey(),
            'schedule_id' => $lessonSchedule->getKey(),
            'lesson_id' =>  $masterLesson->id,
            'master_id' => $master->getKey(),
            'lesson_price' => $this->faker->randomFloat(2, 0, 100),
            'master_cancelled_at' => now(),
        ]);

        $activeLessons = LessonEnrollment::factory()->count(2)->create([
            'student_id' => $student->getKey(),
            'schedule_id' => $lessonSchedule->getKey(),
            'lesson_id' =>  $masterLesson->id,
            'master_id' => $master->getKey(),
            'lesson_price' => $this->faker->randomFloat(2, 0, 100),
        ]);

        $response = $this->actingAs($student)->getJson('/api/v1/lessons/student/to-learn');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }


    private function seedTableSeeder()
    {
        $this->artisan('db:seed', ['--class' => 'CountriesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlacesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoriesTableSeeder']);
    }
}
