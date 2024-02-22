<?php

namespace Tests\Feature\Controllers\V1\Lesson\MasterLessonController;

use App\Enums\Plan as EnumsPlan;
use App\Models\Plan;
use App\Models\MasterLesson;
use App\Models\Place;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlans();
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['--class' => 'CountriesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlacesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoriesTableSeeder']);
    }

    /**
     * @test
     */
    public function authenticated_master_can_view_all_lesson_created()
    {
        $apiUrl = "api/v1/lessons";
        $user   = User::factory()->create();

        $plan = Plan::where('slug', EnumsPlan::PRO_PLAN)->first();
        //Arrange 
        (new SubscriptionService)->createSubscription($user, $plan, 'tok_visa_debit', $user->full_name);

        $places = Place::inRandomOrder()->first();

        $lesson = MasterLesson::factory(3)->create([
            'user_id' => $user->id,
            'place_id' => $places->id,
        ]);

        $this->actingAs($user)
            ->getJson($apiUrl, [])
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
                ]
            ]);

        $this->assertCount(3, $lesson);
    }

    /**
     * @test
     */
    public function unauthenticated_master_cannot_view_all_lesson_created()
    {
        $apiUrl = "api/v1/lessons";
        $user   = User::factory()->create();

        $places = Place::inRandomOrder()->first();

        $lesson = MasterLesson::factory(3)->create([
            'user_id' => $user->id,
            'place_id' => $places->id,
        ]);

        $this->getJson($apiUrl, [])
            ->assertUnauthorized();
    }
}
