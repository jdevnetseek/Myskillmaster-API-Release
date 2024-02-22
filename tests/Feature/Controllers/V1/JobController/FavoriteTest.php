<?php

namespace Tests\Feature\Controllers\V1\JobController;

use App\Models\Job;
use Tests\TestCase;
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
    public function userCanFavoriteAJobPost()
    {
        $user = User::factory()->create();

        $job = Job::factory()->create();

        $response = $this->actingAs($user)->postJson(route('jobs.favorite', [ $job ]));

        $response->assertOk();

        $response->assertJsonStructure([
            'http_status',
            'success'
        ]);

        $this->assertDatabaseHas('favorites', [
            'favoriteable_type' => (new Job())->getMorphClass(),
            'favoriteable_id'   => $job->id,
            'user_id'           => $user->id
        ]);
    }

    /** @test */
    public function unauthenicatedUserCannotFavoriteJob()
    {
        $job = Job::factory()->create();

        $response = $this->postJson(route('jobs.favorite', [ $job ]));

        $response->assertUnauthorized();
    }
}
