<?php

namespace Tests\Feature\Controllers\V1\PostController;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function showPost()
    {
        $user = User::factory()->create();

        $job = Post::factory()->create([
            'author_id' => $user->id
        ]);

        $response = $this->actingAs($user)->getJson(route('posts.show', [ $job ]));

        $response->assertOk();
    }
}
