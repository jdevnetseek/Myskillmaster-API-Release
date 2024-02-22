<?php

namespace Tests\Feature\Controllers\V1\PostController;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function userCanDeletePost()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $post = Post::factory()->create(['author_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson(route('posts.destroy', $post));

        $response->assertOk();

        $response->assertJsonStructure([
            'http_status',
            'success'
        ]);

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    /**
     * @test
     */
    public function userCannotDeleteOtherUserPost()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $post = Post::factory()->create();

        $response = $this->actingAs($user)->deleteJson(route('posts.destroy', $post));

        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function unauthorizedUserCannotUpdatePost()
    {
        $post = Post::factory()->create();

        $response = $this->deleteJson(route('posts.destroy', $post));

        $response->assertUnauthorized();
    }
}
