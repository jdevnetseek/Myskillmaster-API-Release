<?php

namespace Tests\Feature\Controllers\V1\UserPostController;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function listOfPost()
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();

        Post::factory()->times(15)->create();
        Post::factory()->times(5)->create(['author_id' => $otherUser->id]);

        $response = $this->actingAs($user)->getJson(route('users.posts.index', $otherUser));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
               [
                    'id',
                    'body',
                    'author_id',
                    'created_at',
                    'updated_at',
               ]
            ],
            'meta' => [
                'total'
            ]
        ]);

        $this->assertEquals(data_get($response->decodeResponseJson(), 'meta.total'), 5);
    }
}
