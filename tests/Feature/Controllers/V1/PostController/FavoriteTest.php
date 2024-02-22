<?php

namespace Tests\Feature\Controllers\V1\PostController;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function userCanFavoriteAPost()
    {
        $user = User::factory()->create();

        $post = Post::factory()->create();

        $response = $this->actingAs($user)->postJson(route('posts.favorite', [ $post ]));

        $response->assertOk();

        $response->assertJsonStructure([
            'http_status',
            'success'
        ]);

        $this->assertDatabaseHas('favorites', [
            'favoriteable_type' => (new Post())->getMorphClass(),
            'favoriteable_id'   => $post->id,
            'user_id'           => $user->id
        ]);
    }

    /** @test */
    public function unauthenicatedUserCannotFavoritePost()
    {
        $post = Post::factory()->create();

        $response = $this->postJson(route('posts.favorite', [ $post ]));

        $response->assertUnauthorized();
    }
}
