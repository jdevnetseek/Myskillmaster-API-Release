<?php

namespace Tests\Feature\Controllers\V1\JobController;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function authorCanDeleteJobPost()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $job = Job::factory()->create(['author_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson(route('jobs.destroy', $job));

        $response->assertOk();

        $response->assertJsonStructure([
            'http_status',
            'success'
        ]);

        $this->assertSoftDeleted('job_offers', ['id' => $job->id]);
    }

    /**
     * @test
     */
    public function userCannotDeleteOtherJobPost()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $otherUser = User::factory()->create();

        $job = Job::factory()->create(['author_id' => $otherUser->id]);

        $response = $this->actingAs($user)
            ->deleteJson(route('jobs.destroy', $job));

        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function unauthenticatedUserCannotDeleteOtherJobPost()
    {
        $job = Job::factory()->create();

        $response = $this->deleteJson(route('jobs.destroy', $job));

        $response->assertUnauthorized();
    }
}
