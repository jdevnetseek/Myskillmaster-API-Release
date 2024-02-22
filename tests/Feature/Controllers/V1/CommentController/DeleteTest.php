<?php

namespace Tests\Feature\Controllers\V1\CommentController;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function userCanDeleteComment()
    {
        $user = User::factory()->create();

        $model = Job::factory()
            ->create()
            ->commentAsUser($user, 'Hello World');

        $response = $this->actingAs($user)->deleteJson(route('comments.destroy', $model));

        $response->assertOk();

        $response->assertJsonStructure([
            'http_status',
            'success'
        ]);

        $this->assertSoftDeleted('comments', ['id' => $model->id]);
    }

    /**
     * @test
     */
    public function userCannotDeleteOtherUserComment()
    {
        $user = User::factory()->create();

        $model = Job::factory()
            ->create()
            ->commentAsUser(User::factory()->create(), 'Hello World');

        $response = $this->actingAs($user)->deleteJson(route('comments.destroy', $model));

        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function unauthorizeUserCannotUpdateComment()
    {
        $model = Job::factory()
            ->create()
            ->commentAsUser(User::factory()->create(), 'Hello World');

        $response = $this->deleteJson(route('comments.destroy', $model));

        $response->assertUnauthorized();
    }
}
