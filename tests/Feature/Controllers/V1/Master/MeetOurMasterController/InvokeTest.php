<?php

namespace Tests\Feature\Controllers\V1\Master\MeetOurMasterController;

use App\Models\MasterProfile;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlans();
    }

    /**
     * A basic feature test example.
     *
     * @test
     * @return void
     */
    public function user_can_view_list_of_random_masters()
    {
        $users = User::factory()
            ->has(MasterProfile::factory())
            ->count(5)
            ->create();

        $plan = Plan::inRandomOrder()->first();

        foreach ($users as $user) {
            $this->createSubscription($user, $plan->name, $plan->stripe_plan);
        }

        $totalMaster = 5;

        $response = $this->getJson($this->endpoint(['page' => 1, 'limit' => 3]))
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'total' => $totalMaster
                ]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'full_name',
                        'first_name',
                        'last_name',
                        'username',
                        'email',
                        'created_at',
                        'updated_at',
                        'blocked_at',
                        'onboarded_at',
                        'primary_username',
                        'place_id',
                        'email_verified',
                        'verified',
                        'has_master_profile',
                        'is_subscribed',
                        'avatar_permanent_url',
                        'avatar_permanent_thumb_url',
                        'mine',
                        'master_details',
                        'master_interests'
                    ],
                ],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
            ]);

        // Assert that the response has 3 masters
        $response->assertJsonCount(3, 'data');

        // Assert that the masters returned are random
        $masterIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotEquals($masterIds, range(1, 5));
    }

    /** @test */
    public function should_include_categories_of_lessons_that_the_master_created()
    {
        $users = User::factory()
            ->has(MasterProfile::factory())
            ->hasLessons(2)
            ->count(5)
            ->create();

        $plan = Plan::inRandomOrder()->first();

        foreach ($users as $user) {
            $this->createSubscription($user, $plan->name, $plan->stripe_plan);
        }

        $data = $this->getJson($this->endpoint())
            ->assertOk()
            ->getData()
            ->data;

        // check if lesson_categories is the same with the returned in user profile
        collect($data)->each(function ($userData) {
            $storedLessonsCategoryIds = User::find(data_get($userData, 'id'))->lessons()->pluck('category_id')->toArray();

            $returnedLessonCategories = collect(data_get($userData, 'posted_lesson_categories'));

            $this->assertTrue($returnedLessonCategories->isNotEmpty());

            // check if the category id response is in the user lesson
            $this->assertAllLessonCategoriesAreReturned(
                $storedLessonsCategoryIds,
                $returnedLessonCategories
            );
        });
    }

    protected function createSubscription(User $user, string $plan_name, string $plan): Subscription
    {
        return $user->subscriptions()->create([
            'name' => $plan_name,
            'stripe_id' => 'fake_id',
            'stripe_plan' => $plan,
            'stripe_status' => 'active',
            'quantity' => 1,
            'trial_ends_at' => null,
            'ends_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assertAllLessonCategoriesAreReturned($expectedLessonsCategoryIds, $returnedLessonCategories)
    {
        // trarverse the return lesson categories and check if it exist in expectedLessonsCategoryIds
        $returnedLessonCategories->each(function ($returnedCategory) use ($expectedLessonsCategoryIds) {
            $this->assertTrue(in_array($returnedCategory->id, $expectedLessonsCategoryIds));
        });
    }

    private function endpoint(array $params = []): string
    {
        return 'api/v1/masters?' . http_build_query($params);
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
    }
}
