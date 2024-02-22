<?php

namespace Tests\Feature\Controllers\V1\Auth\MasterProfileController;

use App\Models\LessonEnrollment;
use App\Models\LessonSchedule;
use App\Models\MasterLesson;
use App\Models\Plan;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Database\Seeders\PlanTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Env;
use Laravel\Cashier\Subscription;

class DestroyTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    private $route = 'api/v1/auth/master-profile';

    public function setUp(): void
    {
        parent::setUp();

        Env::enablePutenv();

        $this->seedPlans();
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['class' =>  PlanTableSeeder::class]);
    }

    /** @test */
    public function user_should_able_to_delete_their_master_profile_information()
    {
        $master = User::factory()->hasMasterProfile()->create();

        $plan = Plan::first();

        (new SubscriptionService)->createSubscription($master, $plan, 'tok_visa_debit', $master->full_name);

        $this->actingAs($master)
            ->deleteJson($this->route)
            ->assertOk()
            ->assertJson([
                'message' => 'Master profile deleted successfully',
            ]);

        $this->assertDatabaseMissing('master_profiles', [
            'user_id' => $master->id,
        ]);

        $subscription = Subscription::where('user_id', $master->id)->active()->first();

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $master->id,
            'stripe_status' => 'active',
            'ends_at' =>  $subscription->ends_at
        ]);
    }

    /** @test */
    public function user_should_not_able_to_delete_their_master_profile_information_if_they_have_ongoing_or_up_coming_lessons()
    {
        $master = User::factory()->hasMasterProfile()->create();

        // Create a master lesson owned by the authenticated user
        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $master->id,
        ]);

        // Create a lesson schedule for the lesson enrollment
        $lessonSchedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $masterLesson->id,
            'duration_in_hours' => 1,
            'schedule_start' => now()->subHour(),
            'schedule_end' => now()->addHour(),
        ]);

        // Create a lesson enrollment with an ongoing schedule
        LessonEnrollment::factory()->create([
            'master_id' => $master->id,
            'lesson_id' => $masterLesson->id,
            'schedule_id' => $lessonSchedule->id,
            'master_rated_at' => null,
        ]);

        $this->actingAs($master)
            ->deleteJson($this->route)
            ->assertForbidden()
            ->assertJson([
                'message' => 'You cannot delete your profile while you have ongoing or upcoming lessons. Please refund your students first.',
            ]);

        $this->assertDatabaseHas('master_profiles', [
            'user_id' => $master->id,
        ]);
    }


    /** @test */
    public function user_should_not_able_to_delete_their_master_profile_information_if_they_have_pending_payouts()
    {
        $testConnectId = 'acct_1MshUFQvIJ3d0Lhv';

        $master = User::factory()->payoutsEnabled()
            ->hasMasterProfile()
            ->create([
                'stripe_connect_id' => $testConnectId
            ]);

        $plan = Plan::first();

        (new SubscriptionService)->createSubscription($master, $plan, 'tok_visa_debit', $master->full_name);

        // Create a master lesson owned by the authenticated user
        $masterLesson = MasterLesson::factory()->create([
            'user_id' => $master->id
        ]);

        // Create a lesson schedule for the lesson enrollment
        $schedule = LessonSchedule::factory()->create([
            'master_lesson_id' => $masterLesson->id,
        ]);

        // Create a lesson enrollment for the master lesson
        LessonEnrollment::factory()->create([
            'schedule_id' => $schedule->getKey(),
            'lesson_id' =>  $masterLesson->getKey(),
            'master_id' => $master->getKey(),
        ]);


        $this->actingAs($master)
            ->deleteJson($this->route)
            ->assertForbidden()
            ->assertJson([
                'message' => 'You cannot delete your profile while you have pending payouts. Please wait for your payouts to be transferred to your bank account.',
            ]);


        $this->assertDatabaseHas('master_profiles', [
            'user_id' => $master->id,
        ]);
    }
}
