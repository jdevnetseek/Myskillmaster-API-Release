<?php

namespace Tests\Feature\Controllers\V1\JobController;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function userCanPostAJob()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $payload = [
            'title'          => 'the quick brown fox jumps over the lazy dog.',
            'description'    => $this->faker->paragraph,
            'price_offer'    => 999999,
            'category_id'    => Category::factory()->create()->getKey(),
            'subcategory_id' => Subcategory::factory()->create()->getKey(),
            'suburb'         => $this->faker->address,
            'photos' => [
                UploadedFile::fake()->image('photo1.png'),
                UploadedFile::fake()->image('photo2.png'),
                UploadedFile::fake()->image('photo3.png')
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson(route('jobs.store'), $payload);

        $response->assertCreated();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'price_offer',
                'category_id',
                'subcategory_id',
                'suburb',
                'photos',
                'author_id',
                'created_at'
            ]
        ]);

        $this->assertDatabaseHas('job_offers', Arr::only($payload, 'title'));
    }

    /**
     * @test
     */
    public function unauthenticatedUserCannotAddJobPost()
    {
        $response = $this->postJson(route('jobs.store'), []);
        $response->assertUnauthorized();
    }
}
