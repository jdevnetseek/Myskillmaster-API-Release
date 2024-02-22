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

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function userCanUpdatePost()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $post = Post::factory()->create(['author_id' => $user->id]);

        $payload = [
            'body'  => 'this is my updated post'
        ];

        $response = $this->actingAs($user)
            ->putJson(route('posts.update', $post), $payload);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'body',
                'author_id',
                'created_at',
                'updated_at',
                'photo'
            ]
        ]);

        $this->assertDatabaseHas('posts', Arr::only($payload, 'body'));
    }

    /**
     * @test
     */
    public function userCannotUpdateOtherUserPost()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $post = Post::factory()->create();

        $payload = [
            'body'  => 'this is my updated post'
        ];

        $response = $this->actingAs($user)
            ->putJson(route('posts.update', $post), $payload);

        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function unauthorizedUserCannotUpdatePost()
    {
        $post = Post::factory()->create();

        $payload = [
            'body'  => 'this is my updated post'
        ];

        $response = $this->putJson(route('posts.update', $post), $payload);

        $response->assertUnauthorized();
    }
}
