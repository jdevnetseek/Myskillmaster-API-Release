<?php

namespace Tests\Feature\Controllers\V1\JobController;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function listOfJobs()
    {
        $user = User::factory()->create();

        Job::factory()->times(10)->create();

        $response = $this->actingAs($user)->getJson(route('jobs.index'));

        $response->assertOk();
        $response ->assertJsonStructure([
            'data' => [
               [
                    "id",
                    "category_id",
                    "subcategory_id",
                    "title",
                    "description",
                    "price_offer",
                    "suburb",
                    "author_id",
                    "created_at",
                    "is_favorite",
                    "updated_at"
               ]
            ],
        ]);
    }
}
