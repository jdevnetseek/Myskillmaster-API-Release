<?php

namespace Tests\Feature\Controllers\V1\JobController;

use App\Models\Job;
use Tests\TestCase;
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
    public function userCanReportJob()
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

        $job = Job::factory()->create();

        $response = $this->actingAs($user)->postJson(route('jobs.report', [$job]), $payload);

        $response->assertOk();

        $this->assertDatabaseHas('reports', [
            'reportable_type' => (new Job())->getMorphClass(),
            'reportable_id'   => $job->getKey(),
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

        $job = Job::factory()->create();

        $response = $this->postJson(route('jobs.report', [$job]), $payload);

        $response->assertUnauthorized();
    }
}
