<?php

namespace Tests\Feature\Controllers\V1\PostController;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function listOfPost()
    {
        $user = User::factory()->create();

        Post::factory()->times(10)->create();

        $response = $this->actingAs($user)->getJson(route('posts.index'));

        $response->assertOk();
        $response ->assertJsonStructure([
            'data' => [
               [
                    'id',
                    'body',
                    'author_id',
                    'created_at',
                    'updated_at',
               ]
            ],
        ]);
    }
}
