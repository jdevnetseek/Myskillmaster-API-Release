<?php

namespace Tests\Feature\Controllers\V1\Lesson\LessonScheduleController;

use App\Models\LessonSchedule;
use App\Models\MasterLesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function lesson_owner_can_delete_lesson_schedule()
    {
        $user = User::factory()->create();
        $masterLesson   = MasterLesson::factory()->create(['user_id' => $user->id]);
        $lessonSchedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $masterLesson->id
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("api/v1/lesson/schedule/{$lessonSchedule->id}");

        $response->assertOk();
        $response->assertJson([
            'message' => 'Lesson schedule successfully deleted.'
        ]);

        $this->assertDatabaseMissing('lesson_schedules', [
            'id' => $lessonSchedule->id
        ]);
    }

    /**
     * @test
     */
    public function lesson_schedule_not_found()
    {
        $user = User::factory()->create();
        $masterLesson   = MasterLesson::factory()->create(['user_id' => $user->id]);
        $lessonSchedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $masterLesson->id
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("api/v1/lesson/schedule/999");

        $response->assertNotFound();
        $response->assertJson([
            'message' => 'No query results for model [App\\Models\\LessonSchedule] 999',
        ]);

        $this->assertDatabaseHas('lesson_schedules', [
            'id' => $lessonSchedule->id
        ]);
    }

    /**
     * @test
     */
    public function lesson_schedule_not_owned_by_user()
    {
        $user = User::factory()->create();
        $masterLesson   = MasterLesson::factory()->create();
        $lessonSchedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $masterLesson->id
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("api/v1/lesson/schedule/{$lessonSchedule->id}");

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'Unauthorized. Only the owner of the lesson can delete schedules.',
        ]);

        $this->assertDatabaseHas('lesson_schedules', [
            'id' => $lessonSchedule->id
        ]);
    }

    /**
     * @test
     */
    public function user_not_authenticated()
    {
        $masterLesson   = MasterLesson::factory()->create();
        $lessonSchedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $masterLesson->id
        ]);

        $response = $this->deleteJson("api/v1/lesson/schedule/{$lessonSchedule->id}");

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        $this->assertDatabaseHas('lesson_schedules', [
            'id' => $lessonSchedule->id
        ]);
    }

    /**
     * @test
     */
    public function user_not_authorized()
    {
        $user = User::factory()->create();
        $masterLesson   = MasterLesson::factory()->create();
        $lessonSchedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $masterLesson->id
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("api/v1/lesson/schedule/{$lessonSchedule->id}");

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'Unauthorized. Only the owner of the lesson can delete schedules.',
        ]);

        $this->assertDatabaseHas('lesson_schedules', [
            'id' => $lessonSchedule->id
        ]);
    }
}
