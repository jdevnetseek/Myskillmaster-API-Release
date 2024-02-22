<?php

namespace Tests\Feature\Controllers\V1\JobController;

use App\Models\Job;
use Tests\TestCase;
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

        /** @var Job */
        $job = Job::factory()->create();
        $job->favoriteAsUser($user);

        $this->assertDatabaseHas('favorites', [
            'favoriteable_type' => (new Job())->getMorphClass(),
            'favoriteable_id'   => $job->id,
            'user_id'           => $user->id
        ]);

        $response = $this->actingAs($user)->postJson(route('jobs.unfavorite', [ $job ]));

        $response->assertOk();

        $response->assertJsonStructure([
            'http_status',
            'success'
        ]);

        $this->assertDatabaseMissing('favorites', [
            'favoriteable_type' => (new Job())->getMorphClass(),
            'favoriteable_id'   => $job->id,
            'user_id'           => $user->id
        ]);
    }

    /** @test */
    public function unauthenicatedUserCannotFavoriteJob()
    {
        $job = Job::factory()->create();

        $response = $this->postJson(route('jobs.unfavorite', [ $job ]));

        $response->assertUnauthorized();
    }
}
