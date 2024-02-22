<?php

namespace Tests\Feature\Controllers\V1\Lesson\LessonController;

use App\Models\MasterLesson;
use App\Models\Plan;
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

        $this->seedTableSeeder();
    }

    /** @test  */
    public function it_returns_an_empty_array_when_no_search_terms_is_provided()
    {
        $user   = User::factory()->create();
        $plan   = Plan::where('slug', 'pro-plan')->first();

        //Arrange create user subscription
        (new SubscriptionService)->createSubscription($user, $plan, 'tok_visa_debit', $user->full_name);

        $response = $this->getJson('api/v1/search/lessons', ['filter' => ['search' => '']]);

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    /** @test */
    public function search_should_return_a_lists_of_master_lesson_based_on_search_term()
    {
        $user   = User::factory()->create();
        $plan   = Plan::where('slug', 'pro-plan')->first();

        //Arrange create user subscription
        (new SubscriptionService)->createSubscription($user, $plan, 'tok_visa_debit', $user->full_name);

        // Create some Master Lesson records with titles that contain the search term
        MasterLesson::factory()->create(['user_id' => $user->getKey(), 'title' => 'Lesson 1']);
        MasterLesson::factory()->create(['user_id' => $user->getKey(), 'title' => 'Lesson 2']);
        MasterLesson::factory()->create(['user_id' => $user->getKey(), 'title' => 'Others']);

        $response = $this->getJson('api/v1/search/lessons?filter[search]=Lesson');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['title' => 'Lesson 1'])
            ->assertJsonFragment(['title' => 'Lesson 2']);
    }

    /** @test */
    public function it_paginates_the_results()
    {
        $user   = User::factory()->create();
        $plan   = Plan::where('slug', 'pro-plan')->first();

        //Arrange create user subscription
        (new SubscriptionService)->createSubscription($user, $plan, 'tok_visa_debit', $user->full_name);

        // create 20 master lessons records with titles that contain the search term
        MasterLesson::factory()->count(20)->create(['user_id' => $user->getKey(), 'title' => 'Lessons']);

        $response = $this->getJson('api/v1/search/lessons?filter[search]=lesson&page=1');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    private function seedTableSeeder()
    {
        $this->artisan('db:seed', ['--class' => 'CountriesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlacesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoriesTableSeeder']);
    }
}
