<?php

namespace Tests\Feature\Controllers\V1\SubscriptionController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Database\Seeders\PlanTableSeeder;
use App\Models\Plan;
use App\Models\User;
use Tests\TestCase;

class SubscribeTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public $planID;
    public $stripeToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlans();

        $plan = Plan::inRandomOrder()->first();
        $this->planID = $plan->getKey();

        $paymentMethod = [
            'tok_visa', 'tok_visa_debit', 'tok_mastercard',
            'tok_mastercard_debit', 'tok_mastercard_prepaid', 'tok_amex',
            'tok_discover', 'tok_diners', 'tok_jcb', 'tok_unionpay'
        ];

        $this->stripeToken = $this->faker->randomElement($paymentMethod);
    }

    /** @test */
    public function user_should_be_able_to_subscribe_to_any_plan()
    {
        $user = User::factory()->create();

        $apiUrl = 'api/v1/user/subscribe';

        $this->actingAs($user)
            ->postJson($apiUrl, $this->payload())
            ->assertSuccessful();

        // Assert
        $isSubscribe = $user->subscriptions($this->planID)->exists();
        $this->assertTrue($isSubscribe);
    }

    /**
     * @test
     * @dataProvider validations
     */
    public function form_must_be_validated($payload, $key)
    {
        $user   = User::factory()->create();
        $apiUrl = 'api/v1/user/subscribe';

        $response = $this->actingAs($user)->postJson($apiUrl, $payload);

        $response->assertJsonValidationErrors($key)
            ->assertUnprocessable();
    }

    public function validations()
    {
        $payload = $this->payload();

        return [
            'name.required'    => [Arr::except($payload, 'name'), 'name'],
            'source.required'  => [Arr::except($payload, 'source'), 'source'],
            'plan.required'    => [Arr::except($payload, 'plan'), 'plan'],
            'plan.numeric'     => [Arr::set($payload, 'plan', Str::random(1)), 'plan'],
            'plan.exists'      => [Arr::set($payload, 'plan', -1), 'plan'],
        ];
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['class' =>  PlanTableSeeder::class]);
    }

    public function payload()
    {
        return [
            'name'   => 'John Doe',
            'source' => $this->stripeToken,
            'plan'   => $this->planID
        ];
    }
}
