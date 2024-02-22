<?php

namespace Tests\Feature\Controllers\V1\Auth\Connect\AccountController;

use Tests\TestCase;
use App\Models\User;
use App\Enums\ErrorCodes;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Reader\Xls\ErrorCode;

class IndexText extends TestCase
{
    /**
     * @test
     */
    public function throwsNoStripeConnectAccount()
    {
        $user = $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $response = $this->actingAs($user)->get(route('connect.account.index'));
        $response->assertStatus(404);
        $response->assertJsonStructure([
            'message',
            'error_code',
            'http_status',
            'success'
        ]);

        $response->assertJson([
            'error_code'  => ErrorCodes::STRIPE_CONNECT_NOT_FOUND,
            'http_status' => Response::HTTP_NOT_FOUND,
        ]);
    }
}
