<?php

namespace Tests\Feature\Controllers\V1\Lesson\MasterLessonController;

use App\Models\MasterLesson;
use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlans();
    }

    /** @test */
    public function authenticated_master_or_student_can_view_lesson_details()
    {
        $user   = User::factory()->create();

        $places = Place::inRandomOrder()->first();

        $lesson = MasterLesson::factory()->create([
            'user_id' => $user->id,
            'place_id' => $places->id,
        ]);

        $apiUrl = "api/v1/lessons/{$lesson->slug}";

        $response = $this->actingAs($user)
            ->getJson($apiUrl, [])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'title',
                    'slug',
                    'description',
                    'duration_in_hours',
                    'lesson_price',
                    'is_remote_supported',
                    'active',
                    'is_owner',
                    'is_enrolled',
                    'active',
                    'place',
                    'category',
                    'tags',
                    'cover_photo',
                    'master_profile',
                    'created_at',
                ]
            ]);

        $this->assertEquals($lesson->id, $response['data']['id']);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_lesson_details()
    {
        $places = Place::inRandomOrder()->first();

        $lesson = MasterLesson::factory()->create([
            'place_id' => $places->id,
        ]);

        $apiUrl = "api/v1/lessons/{$lesson->slug}";

        $this->getJson($apiUrl, [])
            ->assertUnauthorized();
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['--class' => 'CountriesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlacesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoriesTableSeeder']);
    }
}
