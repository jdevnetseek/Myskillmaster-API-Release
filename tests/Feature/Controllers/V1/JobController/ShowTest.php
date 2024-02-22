<?php

namespace Tests\Feature\Controllers\V1\JobController;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function showJobInfo()
    {
        $user = User::factory()->create();

        $job = Job::factory()->create([
            'author_id' => $user->id
        ]);

        $response = $this->actingAs($user)->getJson(route('jobs.show', [ $job ]));

        $response->assertOk();
    }
}
