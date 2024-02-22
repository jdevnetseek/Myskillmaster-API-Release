<?php

namespace Tests\Feature\Controllers\V1\Auth\Connect\BalanceController;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class InvokeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function master_should_be_able_to_get_their_total_balance()
    {
        $testConnectId = 'acct_1MshUFQvIJ3d0Lhv';

        $master = User::factory()->payoutsEnabled()->create([
            'stripe_connect_id' => $testConnectId
        ]);

        $this->actingAs($master)
            ->getJson($this->endpoint())
            ->assertOk()
            ->assertJsonStructure([
                'data' => $this->expecteResponseData()
            ])
            ->assertJson(fn (AssertableJson $json) =>
                $json->whereAllType($this->expectedResponseDataTypes())
                    ->etc()
            );
    }

    /** @test */
    public function master_should_not_be_able_to_get_their_balance_if_their_dont_have_connect_id()
    {
        $master = User::factory()->create();

        $this->actingAs($master)
            ->getJson($this->endpoint())
            ->assertForbidden();
    }

    protected function expectedResponseDataTypes(): array
    {
        $data = [
            'current_week' => 'array',
            'current_week.total_lessons' => 'integer',
            'current_week.total_earnings' => 'integer|float|double',

            'available.amount' => 'integer|float|double',
            'available.currency' => 'string',

            'available.amount' => 'integer|float|double',
            'available.currency' => 'string',
        ];

        return Arr::dot($data, 'data.');
    }

    protected function expecteResponseData(): array
    {
        return [
            'current_week' => [
                'total_lessons',
                'total_earnings',
            ],
            'available' => [
                'amount',
                'currency',
            ],
            'pending' => [
                'amount',
                'currency',
            ],
        ];
    }

    private function endpoint(): string
    {
        return 'api/v1/auth/connect/balance';
    }
}
