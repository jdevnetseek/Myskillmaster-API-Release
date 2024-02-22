<?php

namespace Tests\Feature\Controllers\V1\JobController;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function userCanUpdateJobPost()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $job = Job::factory()->create(['author_id' => $user->id]);

        $payload = [
            'title'          => 'updated job title',
            'description'    => 'updated description',
            'category_id'    => Category::factory()->create()->getKey(),
            'subcategory_id' => Subcategory::factory()->create()->getKey(),
            'suburb'         => 'updated suburb'
        ];

        $this->assertDatabaseMissing('job_offers', $payload);

        $response = $this->actingAs($user)
            ->putJson(route('jobs.update', $job), $payload);

        $response->assertOk();

        $this->assertDatabaseHas('job_offers', $payload);
    }

    /**
     * @test
     */
    public function userCannotUpdateOtherUserJobPost()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $job = Job::factory()->create();

        $payload = [
            'title'          => 'updated job title',
            'description'    => 'updated description',
            'category_id'    => Category::factory()->create()->getKey(),
            'subcategory_id' => Subcategory::factory()->create()->getKey(),
            'suburb'         => 'updated suburb'
        ];


        $response = $this->actingAs($user)
            ->putJson(route('jobs.update', $job), $payload);

        $response->assertForbidden();

        $this->assertDatabaseMissing('job_offers', $payload);
    }

    /**
     * @test
     */
    public function unauthorizeUserCannotUpdateJob()
    {
        $job = Job::factory()->create();

        $payload = [
            'title'          => 'updated job title',
            'description'    => 'updated description',
            'category_id'    => Category::factory()->create()->getKey(),
            'subcategory_id' => Subcategory::factory()->create()->getKey(),
            'suburb'         => 'updated suburb'
        ];

        $response = $this->putJson(route('jobs.update', $job), $payload);

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('job_offers', $payload);
    }
}
