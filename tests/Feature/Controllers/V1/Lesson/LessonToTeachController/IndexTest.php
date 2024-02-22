<?php

namespace Tests\Feature\Controllers\V1\Lesson\LessonToTeachController;

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
    public function master_should_only_see_lessons_to_teach_that_he_own()
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

        $response = $this->actingAs($master)->getJson('/api/v1/lessons/master/to-teach');

        $response->assertOk();
        $response->assertJsonStructure([
            "data" => [
                "*" => [
                    'id',
                    'master_lesson_id',
                    'schedule_start',
                    'schedule_end',
                    'slots',
                    'duration_in_hours',
                    'number_of_students_enrolled',
                    'lesson',
                    'master_profile',
                    'students_enrolled'
                ]
            ]
        ]);
    }

    /** @test */
    public function unauthenticated_user_should_not_be_able_to_view_list_of_lesson_to_teach()
    {
        $response = $this->getJson('/api/v1/lessons/master/to-teach');

        $response->assertUnauthorized();
    }

    /** @test */
    public function master_should_only_see_lessons_to_teach_that_are_not_cancelled()
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
        $lessonEnrollmentOne = LessonEnrollment::factory()->create([
            'student_id' => $student->getKey(),
            'schedule_id' => $lessonSchedule->getKey(),
            'lesson_id' =>  $masterLesson->id,
            'master_id' => $master->getKey(),
            'lesson_price' => $this->faker->randomFloat(2, 0, 100),
            'student_cancelled_at' => now(),
        ]);

        // Create a lesson enrollment for the master lesson
        $lessonEnrollmentTwo = LessonEnrollment::factory()->create([
            'student_id' => $student->getKey(),
            'schedule_id' => $lessonSchedule->getKey(),
            'lesson_id' =>  $masterLesson->id,
            'master_id' => $master->getKey(),
            'lesson_price' => $this->faker->randomFloat(2, 0, 100),
        ]);

        $response = $this->actingAs($master)->getJson('/api/v1/lessons/master/to-teach');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }


    private function seedTableSeeder()
    {
        $this->artisan('db:seed', ['--class' => 'CountriesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlacesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoriesTableSeeder']);
    }
}
