<?php

namespace Tests\Feature\Controllers\V1\PostController;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnfavoriteTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function userCanUnfavoriteAJobPost()
    {
        $user = User::factory()->create();

        /** @var Post */
        $post = Post::factory()->create();
        $post->favoriteAsUser($user);

        $this->assertDatabaseHas('favorites', [
            'favoriteable_type' => (new Post())->getMorphClass(),
            'favoriteable_id'   => $post->id,
            'user_id'           => $user->id
        ]);

        $response = $this->actingAs($user)->postJson(route('posts.unfavorite', [ $post ]));

        $response->assertOk();

        $response->assertJsonStructure([
            'http_status',
            'success'
        ]);

        $this->assertDatabaseMissing('favorites', [
            'favoriteable_type' => (new Post())->getMorphClass(),
            'favoriteable_id'   => $post->id,
            'user_id'           => $user->id
        ]);
    }

    /** @test */
    public function unauthenicatedUserCannotFavoriteJob()
    {
        $post = Post::factory()->create();

        $response = $this->postJson(route('posts.unfavorite', [ $post ]));

        $response->assertUnauthorized();
    }
}
