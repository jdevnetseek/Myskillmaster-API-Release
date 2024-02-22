<?php

namespace Tests\Feature\Controllers\V1\Master\MasterRatingController;

use App\Models\LessonEnrollment;
use App\Models\LessonSchedule;
use App\Models\MasterLesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test 
     * authenticated student can rate a master when lesson enrollment is completed
     */
    public function authenticated_student_can_rate_a_master_when_lesson_enrollment_is_completed()
    {
        $student = User::factory()->create();
        $master = User::factory()
            ->hasMasterProfile()
            ->has(MasterLesson::factory())
            ->create();

        $schedule = $master->masterLesson->schedules()->create([
            'schedule_start' => Carbon::now()->subDay(1)->format('Y-m-d H:i:s'),
            'duration_in_hours' => 1,
            'timezone' => 'Asia/Manila'
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

        $response = $this->actingAs($student)
            ->postJson($this->endpoint(), [
                'rating' => 5,
                'reference_code' => $enrollment->reference_code,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('ratings', [
            'user_id' => $student->getKey(),
            'rateable_id' => $master->getKey(),
            'rateable_type' => User::class,
            'rating' => 5,
        ]);

        $this->assertDatabaseHas('lesson_enrollments', [
            'id' => $enrollment->getKey(),
            'master_rated_at' => now(),
            'is_student_attended' => true,
        ]);
    }

    /** @test */
    public function authenticated_student_cannot_rate_a_master_again()
    {
        $student = User::factory()->create();
        $master = User::factory()
            ->hasMasterProfile()
            ->has(MasterLesson::factory())
            ->create();

        $schedule = $master->masterLesson->schedules()->create([
            'schedule_start' => Carbon::now()->subDay(1)->format('Y-m-d H:i:s'),
            'duration_in_hours' => 1,
            'timezone' => 'Asia/Manila'
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
            ->postJson($this->endpoint(), [
                'rating' => 5,
                'reference_code' => $enrollment->reference_code,
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'You have already rated this master',
        ]);

        $this->assertDatabaseMissing('ratings', [
            'user_id' => $student->getKey(),
            'rateable_id' => $master->getKey(),
            'rateable_type' => User::class,
            'rating' => 5,
        ]);
    }

    /** @test */
    public function authenticated_student_cannot_rate_a_master_when_lesson_enrollment_is_not_completed()
    {
        $student = User::factory()->hasMasterProfile()->create();
        $master = User::factory()->create();

        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $master->getKey(),
        ]);

        $schedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $masterLesson->getKey(),
        ]);

        $enrollment = LessonEnrollment::factory()
            ->for($master, 'master')
            ->for($student, 'student')
            ->create([
                'schedule_id' => $schedule->getKey(),
                'lesson_id' => $masterLesson->getKey(),
                'lesson_price' => 100,
                'to_learn' => 'I want to learn this',
                'master_rated_at' => null,
                'is_student_attended' => null,
            ]);

        $response = $this->actingAs($student)
            ->postJson($this->endpoint(), [
                'rating' => 5,
                'reference_code' => $enrollment->reference_code,
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'You can only rate a master after the lesson is completed',
        ]);

        $this->assertDatabaseMissing('ratings', [
            'user_id' => $student->getKey(),
            'rateable_id' => $master->getKey(),
            'rateable_type' => User::class,
            'rating' => 5,
        ]);

        $this->assertDatabaseHas('lesson_enrollments', [
            'id' => $enrollment->getKey(),
            'master_rated_at' => null,
            'is_student_attended' => null,
        ]);
    }

    /** @test */
    public function authenticated_student_cannot_rate_a_master_when_lesson_enrollment_reference_code_is_not_valid()
    {
        $response = $this->actingAs($this->queryBuilder()['student'])
            ->postJson($this->endpoint(), [
                'rating' => 5,
                'reference_code' => 'invalid-reference-code',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The selected reference code is invalid.',
        ]);
    }

    /** @test */
    public function authenticated_student_cannot_rate_a_master_when_lesson_enrollment_reference_code_is_not_provided()
    {
        $response = $this->actingAs($this->queryBuilder()['student'])
            ->postJson($this->endpoint(), [
                'rating' => 5,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The reference code field is required.',
        ]);
    }

    /** @test */
    public function authenticated_student_cannot_rate_a_master_when_rating_is_not_provided()
    {
        $response = $this->actingAs($this->queryBuilder()['student'])
            ->postJson($this->endpoint(), [
                'reference_code' => $this->queryBuilder()['enrollment']->reference_code,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The rating field is required.',
        ]);
    }

    /** @test */
    public function authenticated_student_cannot_rate_a_master_when_rating_is_not_numeric()
    {
        $response = $this->actingAs($this->queryBuilder()['student'])
            ->postJson($this->endpoint(), [
                'rating' => 'invalid-rating',
                'reference_code' => $this->queryBuilder()['enrollment']->reference_code,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The rating must be a number.',
        ]);
    }

    /** @test */
    public function authenticated_student_cannot_rate_a_master_when_rating_is_not_greater_than_5()
    {
        $response = $this->actingAs($this->queryBuilder()['student'])
            ->postJson($this->endpoint(), [
                'rating' => 6,
                'reference_code' => $this->queryBuilder()['enrollment']->reference_code,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The rating may not be greater than 5.',
        ]);
    }

    /** @test */
    public function authenticated_student_cannot_rate_a_master_when_rating_is_lesser_than_1()
    {
        $response = $this->actingAs($this->queryBuilder()['student'])
            ->postJson($this->endpoint(), [
                'rating' => 0,
                'reference_code' => $this->queryBuilder()['enrollment']->reference_code,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The rating must be at least 1.',
        ]);
    }

    private function queryBuilder()
    {
        $student = User::factory()->create();
        $master = User::factory()
            ->hasMasterProfile()
            ->has(MasterLesson::factory())
            ->create();

        $schedule = $master->masterLesson->schedules()->create([
            'schedule_start' => Carbon::now()->subDay(1)->format('Y-m-d H:i:s'),
            'duration_in_hours' => 1,
            'timezone' => 'Asia/Manila'
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

        return [
            'student' => $student,
            'master' => $master,
            'schedule' => $schedule,
            'enrollment' => $enrollment,
        ];
    }

    private function endpoint(): string
    {
        return 'api/v1/rate/master';
    }
}
