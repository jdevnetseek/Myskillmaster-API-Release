<?php

namespace Tests\Feature\Controllers\V1\Lesson\MasterLessonController;

use App\Models\Category;
use App\Models\MasterLesson;
use App\Models\MasterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Notifications\MasterLessonDeletingNotification;
use Illuminate\Support\Testing\Fakes\MailFake;

class DestroyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function guest_user_cannot_delete_any_lesson()
    {
        $user = User::factory()
            ->has(MasterProfile::factory())
            ->create();

        $lesson = MasterLesson::factory()->has(Category::factory())->create([
            'user_id' => $user->getKey()
        ]);

        $this->deleteJson("api/v1/lessons/{$lesson->getKey()}")
            ->assertUnauthorized()
            ->assertJsonFragment([
                'message' => 'Unauthenticated.',
            ]);
    }

    /** @test */
    public function the_master_should_not_be_able_to_delete_another_lesson_that_is_not_his()
    {
        $otherUser = User::factory()->has(MasterProfile::factory())->create();

        $user = User::factory()->has(MasterProfile::factory())->create();

        $lesson = MasterLesson::factory()
            ->has(Category::factory())
            ->create([
                'user_id' => $user->getKey()
            ]);

        $response = $this->actingAs($otherUser)
            ->deleteJson("api/v1/lessons/{$lesson->getKey()}")
            ->assertForbidden()
            ->assertJsonFragment([
                'message' => 'Unauthorized. Only the owner can delete the lesson.',
                'error_code' => 'HTTP_FORBIDDEN'
            ]);
    }

    /** @test */
    public function user_can_delete_own_lesson()
    {
        $user = User::factory()->has(MasterProfile::factory())->create();

        $lesson = MasterLesson::factory()->has(Category::factory())->create([
            'user_id' => $user->getKey()
        ]);

        $this->actingAs($user)
            ->deleteJson("api/v1/lessons/{$lesson->getKey()}")
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Lesson deleted and email sent to enrolled students'
            ]);

        // Assert that the lesson was deleted
        $this->assertNull(MasterLesson::find($lesson->id));

        // Assert that an email was sent to each enrolled student
        //month 3
    }
}
