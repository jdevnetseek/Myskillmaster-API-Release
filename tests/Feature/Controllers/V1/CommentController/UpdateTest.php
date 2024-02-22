<?php

namespace Tests\Feature\Controllers\V1\CommentController;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function userCanUpdateComment()
    {
        $user = User::factory()->create();

        $model = Job::factory()
            ->create()
            ->commentAsUser($user, 'Hello World');

        $payload = [
            'body' => 'Foo Bar'
        ];

        $this->assertDatabaseMissing('comments', ['body' => $payload['body']]);

        $response = $this->actingAs($user)->putJson(route('comments.update', [ $model ]), $payload);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'body',
                'author_id',
                'created_at',
                'updated_at'
            ]
        ]);

        $this->assertDatabaseHas('comments', ['body' => $payload['body']]);
    }

    /**
     * @test
     */
    public function userCannotUpdateOtherUserComment()
    {
        $user = User::factory()->create();

        $model = Job::factory()
            ->create()
            ->commentAsUser(User::factory()->create(), 'Hello World');

        $payload = [
            'body' => 'Foo Bar'
        ];

        $this->assertDatabaseMissing('comments', ['body' => $payload['body']]);

        $response = $this->actingAs($user)->putJson(route('comments.update', [ $model ]), $payload);

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

        $payload = [
            'body' => 'Foo Bar'
        ];

        $response = $this->putJson(route('comments.update', [ $model ]), $payload);

        $response->assertUnauthorized();
    }
}
