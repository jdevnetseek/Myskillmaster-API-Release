<?php

namespace Tests\Feature\Controllers\V1\PostController;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function userCanPost()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $payload = [
            'body'  => $this->faker->paragraph,
            'photo' => UploadedFile::fake()->image('photo1.png')
        ];

        $response = $this->actingAs($user)
            ->postJson(route('posts.store'), $payload);

        $response->assertCreated();

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
    public function unauthenticatedUserCannotAddPost()
    {
        $response = $this->postJson(route('posts.store'), []);
        $response->assertUnauthorized();
    }
}
