<?php

namespace Tests\Feature\Controllers\V1\SubscriptionController;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\Subscription\SubscriptionService;
use Database\Seeders\PlanTableSeeder;
use App\Models\Plan;
use Tests\TestCase;

class UnsubscribeTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public $stripeToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlans();

        $paymentMethod = [
            'tok_visa', 'tok_visa_debit', 'tok_mastercard',
            'tok_mastercard_debit', 'tok_mastercard_prepaid', 'tok_amex',
            'tok_discover', 'tok_diners', 'tok_jcb', 'tok_unionpay'
        ];

        $this->stripeToken = $this->faker->randomElement($paymentMethod);
    }

    /** @test */
    public function test_user_can_cancel_subscription()
    {
        // Arrange
        $user = User::factory()->create();
        $plan = Plan::inRandomOrder()->first();

        (new SubscriptionService)->createSubscription($user, $plan, $this->stripeToken, $user->full_name);

        $apiUrl = 'api/v1/user/unsubscribe';

        $this->actingAs($user)
            ->postJson($apiUrl, [])
            ->assertSuccessful()
            ->assertJsonStructure(['message', 'http_status', 'success']);

        $this->assertFalse($user->subscribed($plan->id));
    }

    /** @test */
    public function test_guest_user_cannot_unsubscribe()
    {

        // Arrange
        $user = User::factory()->create();
        $plan = Plan::inRandomOrder()->first();

        (new SubscriptionService)->createSubscription($user, $plan, $this->stripeToken, $user->full_name);

        $apiUrl = 'api/v1/user/unsubscribe';

        $this->postJson($apiUrl, [])->assertUnauthorized();
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['class' =>  PlanTableSeeder::class]);
    }
}
