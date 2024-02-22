<?php

namespace Tests\Feature\Controllers\V1\SubscriptionController;

use App\Enums\Plan as EnumsPlan;
use App\Models\MasterProfile;
use App\Models\Plan;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Database\Seeders\PlanTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FreeTrialTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public $freePlan;
    public $stripeToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlans();
    }

    /** @test */
    public function user_should_have_60_days_free_trial_before_pre_launch_date()
    {
        $user = User::factory()->has(MasterProfile::factory())->create();
        $user->onboard();

        $officialLaunchDate = Carbon::now()->addMonth(3)->format('Y-m-d');
        // set pre-launch date to 30 days from now
        $preLaunchDate = Carbon::now()->addDays(30)->format('Y-m-d');

        config([
            'app.pre_launch_date' => $preLaunchDate,
            'app.official_launch_date' => $officialLaunchDate
        ]);

        $trialPlan = Plan::where('slug', EnumsPlan::MASTER_PLAN)->first();

        $this->actingAs($user)
            ->postJson(
                "api/v1/user/subscribe/free-trial/{$trialPlan->getKey()}",
                $this->payload()
            )
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Thank you for subscribing to our service! Your subscription is now active and you can start using all of the features included in your plan.'
            ]);

        $trialEndsAt = $user->trialEndsAt($trialPlan->name);
        $trialDays   = $trialEndsAt->diffInDays(Carbon::now(), true);

        // assert that subscription was created with 60-day free trial
        $this->assertTrue($user->subscribed($trialPlan->name));
        $this->assertEquals(1, $user->subscriptions()->count());
        $this->assertEquals(59, $trialDays);

        $activePlan = $user->activeSubscription()->first();

        // Assert that the plan has been changed to Master Plan
        $this->assertEquals($trialPlan->stripe_plan, $activePlan->stripe_plan);
    }

    /** @test */
    public function user_should_have_60_days_free_trial_before_the_official_launch_date()
    {
        $user = User::factory()->has(MasterProfile::factory())->create();
        $user->onboard();

        $preLaunchDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $officialLaunchDate = Carbon::now()->addMonth(30)->format('Y-m-d');

        config([
            'app.pre_launch_date' => $preLaunchDate,
            'app.official_launch_date' => $officialLaunchDate
        ]);

        $availFreeTrialOnMasterPlan = Plan::where('slug', EnumsPlan::MASTER_PLAN)->first();

        $response = $this->actingAs($user)
            ->postJson(
                "api/v1/user/subscribe/free-trial/{$availFreeTrialOnMasterPlan->getKey()}",
                $this->payload()
            );

        $response->assertOk();
        $response->assertJsonFragment([
            'message' => 'Thank you for subscribing to our service! Your subscription is now active and you can start using all of the features included in your plan.'
        ]);

        // assert that subscription was created with 60-day free trial
        $trialEndsAt = $user->trialEndsAt($availFreeTrialOnMasterPlan->name);
        $remainingDays   = $trialEndsAt->diffInDays(Carbon::now(), true);

        $this->assertTrue($user->subscribed($availFreeTrialOnMasterPlan->name));
        $this->assertEquals(1, $user->subscriptions()->count());
        $this->assertEquals(59, $remainingDays);

        $activePlan = $user->activeSubscription()->first();

        // Assert that the plan has been changed to Master Plan
        $this->assertEquals($availFreeTrialOnMasterPlan->stripe_plan, $activePlan->stripe_plan);
    }

    /** @test */
    public function user_should_have_30_days_free_trial_after_official_launch_date()
    {
        $user = User::factory()->has(MasterProfile::factory())->create();
        $user->onboard();

        // set official launch date to 7 days ago
        $officialLaunchDate = Carbon::now()->subDays(7)->format('Y-m-d');

        config(['app.official_launch_date' => $officialLaunchDate]);

        $trialPlan =  Plan::where('slug', EnumsPlan::MASTER_PLAN)->first();

        $this->actingAs($user)
            ->postJson(
                "api/v1/user/subscribe/free-trial/{$trialPlan->getKey()}",
                $this->payload()
            )
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Thank you for subscribing to our service! Your subscription is now active and you can start using all of the features included in your plan.'
            ]);

        $trialEndsAt = $user->trialEndsAt($trialPlan->name);
        $remainingDays   = $trialEndsAt->diffInDays(Carbon::now(), true);
        $this->assertEquals(29, $remainingDays);

        $activePlan = $user->activeSubscription()->first();
        // Assert that the plan has been changed to Master Plan
        $this->assertEquals($trialPlan->stripe_plan, $activePlan->stripe_plan);
    }

    /** @test */
    public function user_should_have_30_days_free_trial_on_the_official_launch_date()
    {
        $user = User::factory()->has(MasterProfile::factory())->create();
        $user->onboard();

        // set official launch date now
        $officialLaunchDate = Carbon::now()->format('Y-m-d');

        config(['app.official_launch_date' => $officialLaunchDate]);

        $trialPlan =  Plan::where('slug', EnumsPlan::MASTER_PLAN)->first();

        $this->actingAs($user)
            ->postJson(
                "api/v1/user/subscribe/free-trial/{$trialPlan->getKey()}",
                $this->payload()
            )
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Thank you for subscribing to our service! Your subscription is now active and you can start using all of the features included in your plan.'
            ]);

        $trialEndsAt = $user->trialEndsAt($trialPlan->name);
        $remainingDays   = $trialEndsAt->diffInDays(Carbon::now(), true);
        $this->assertEquals(29, $remainingDays);

        $activePlan = $user->activeSubscription()->first();
        // Assert that the plan has been changed to Master Plan
        $this->assertEquals($trialPlan->stripe_plan, $activePlan->stripe_plan);
    }

    /** @test */
    public function user_free_trial_form_validation()
    {
        $user = User::factory()->has(MasterProfile::factory())->create();
        $user->onboard();

        $preLaunchDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $officialLaunchDate = Carbon::now()->addMonth(30)->format('Y-m-d');

        config([
            'app.pre_launch_date' => $preLaunchDate,
            'app.official_launch_date' => $officialLaunchDate
        ]);

        $trialPlan = Plan::where('slug', EnumsPlan::MASTER_PLAN)->first();

        $this->actingAs($user)
            ->postJson(
                "api/v1/user/subscribe/free-trial/{$trialPlan->getKey()}",
                []
            )
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'source']);
    }

    /** @test */
    public function user_should_not_be_able_free_trial_if_not_yet_onboarded()
    {
        $user = User::factory()->has(MasterProfile::factory())->create();

        $preLaunchDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $officialLaunchDate = Carbon::now()->addMonth(30)->format('Y-m-d');

        config([
            'app.pre_launch_date' => $preLaunchDate,
            'app.official_launch_date' => $officialLaunchDate
        ]);

        $trialPlan = Plan::where('slug', EnumsPlan::MASTER_PLAN)->first();

        $response = $this->actingAs($user)
            ->postJson(
                "api/v1/user/subscribe/free-trial/{$trialPlan->getKey()}",
                $this->payload()
            )
            ->assertForbidden();
    }

    /** @test */
    public function user_should_not_avail_free_trial_if_already_subscribed_to_other_plan()
    {
        $user = User::factory()->has(MasterProfile::factory())->create();
        $user->onboard();

        $preLaunchDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $officialLaunchDate = Carbon::now()->addMonth(30)->format('Y-m-d');

        config([
            'app.pre_launch_date' => $preLaunchDate,
            'app.official_launch_date' => $officialLaunchDate
        ]);

        $trialPlan = Plan::where('slug', EnumsPlan::MASTER_PLAN)->first();

        // Let subscribe the user first
        (new SubscriptionService)->createSubscription($user, $trialPlan, 'tok_visa', $user->full_name);

        $this->actingAs($user)
            ->postJson("api/v1/user/subscribe/free-trial/{$trialPlan->getKey()}", $this->payload())
            ->assertForbidden();
    }

    /** @test */
    public function user_cannot_avail_other_plan_for_free_trial()
    {
        $user = User::factory()->has(MasterProfile::factory())->create();
        $user->onboard();

        $notTrialPlan = Plan::where('slug', EnumsPlan::PRO_PLAN)->first();
        $preLaunchDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $officialLaunchDate = Carbon::now()->addMonth(3)->format('Y-m-d');

        config([
            'app.pre_launch_date' => $preLaunchDate,
            'app.official_launch_date' => $officialLaunchDate
        ]);

        $this->actingAs($user)
            ->postJson("api/v1/user/subscribe/free-trial/{$notTrialPlan->getKey()}", $this->payload())
            ->assertForbidden();
    }

    private function payload()
    {
        return [
            'name' => 'John Doe',
            'source' => 'tok_visa'
        ];
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['class' =>  PlanTableSeeder::class]);
    }
}
