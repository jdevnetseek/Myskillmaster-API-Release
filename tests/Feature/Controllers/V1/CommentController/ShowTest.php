<?php

namespace Tests\Feature\Controllers\V1\CommentController;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function showComment()
    {
        $user = User::factory()->create();

        $model = Job::factory()
            ->create()
            ->commentAsUser($user, 'Hello World');

        $response = $this->actingAs($user)->getJson(route('comments.show', [ $model ]));

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
    }
}
