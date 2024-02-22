<?php

namespace Tests\Feature\Controllers\V1\Lesson\LessonController;

use App\Models\Category;
use App\Models\MasterLesson;
use App\Models\MasterProfile;
use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PopularTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlans();
    }

    /**
     * @test
     */
    public function user_can_view_all_popular_lessons()
    {
        $parameters = '?page=2&limit=3&include[]=cover&include[]=place&include[]=user.masterProfile';
        $apiUrl = "api/v1/popular/lessons/{$parameters}";
        $user   = User::factory()->create();

        $user->setMasterProfile([
            'about' => $this->faker->paragraph(100),
            'work_experience' => $this->faker->paragraph(10)
        ]);

        $places   = Place::inRandomOrder()->first();
        $category = Category::first();

        $lessons = MasterLesson::factory()->count(3)->create([
            'user_id' => $user->id,
            'place_id' => $places->id,
            'category_id' => $category->id
        ]);

        $response = $this->actingAs($user)
            ->getJson($apiUrl)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'title',
                        'slug',
                        'description',
                        'duration_in_hours',
                        'lesson_price',
                        'available_days',
                        'is_remote_supported',
                        'active',
                        'is_owner',
                        'active',
                        'place',
                        'cover_photo',
                        'master_profile',
                        'created_at',
                    ],
                ],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
            ]);
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['--class' => 'CountriesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlacesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoriesTableSeeder']);
    }
}
