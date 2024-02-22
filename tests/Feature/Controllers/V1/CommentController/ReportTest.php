<?php

namespace Tests\Feature\Controllers\V1\CommentController;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use App\Models\Comment;
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
    public function userCanReportComment()
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

        $comment =  Job::factory()
            ->create()
            ->commentAsUser(User::factory()->create(), 'Hello World');

        $response = $this->actingAs($user)->postJson(route('comments.report', [$comment]), $payload);

        $response->assertOk();

        $this->assertDatabaseHas('reports', [
            'reportable_type' => (new Comment())->getMorphClass(),
            'reportable_id'   => $comment->getKey(),
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

        $comment = Job::factory()
            ->create()
            ->commentAsUser(User::factory()->create(), 'Hello World');

        $response = $this->postJson(route('comments.report', [$comment]), $payload);

        $response->assertUnauthorized();
    }
}
