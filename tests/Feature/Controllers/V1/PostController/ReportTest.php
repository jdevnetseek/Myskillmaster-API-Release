<?php

namespace Tests\Feature\Controllers\V1\PostController;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\ReportCategories;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /** @test */
    public function userCanReportPost()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $payload = [
            'reason_ids'  => [factory(ReportCategories::class)->create()->id],
            'description' => $this->faker->sentence,
            'attachments' => [
                UploadedFile::fake()->image('imagetest.png'),
                UploadedFile::fake()->image('anotherimage.png')
            ]
        ];

        $post = Post::factory()->create();

        $response = $this->actingAs($user)->postJson(route('posts.report', [$post]), $payload);

        $response->assertOk();

        $this->assertDatabaseHas('reports', [
            'reportable_type' => (new Post())->getMorphClass(),
            'reportable_id'   => $post->getKey(),
            'reported_by'     => $user->id,
        ]);
    }

    /** @test */
    public function unauthorizeUserCannotReport()
    {
        $payload = [
            'reason_ids'  => [factory(ReportCategories::class)->create()->id],
            'description' => $this->faker->sentence,
            'attachments' => [
                UploadedFile::fake()->image('imagetest.png'),
                UploadedFile::fake()->image('anotherimage.png')
            ]
        ];

        $post = Post::factory()->create();

        $response = $this->postJson(route('posts.report', [$post]), $payload);

        $response->assertUnauthorized();
    }
}
