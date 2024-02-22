<?php

namespace Tests\Feature\Controllers\V1\Auth\AuthController;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticatedUserCanLogout()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );
        $token = $user->createToken(config('app.name'))->plainTextToken;

        $this->json('POST', route('auth.logout'), [], ['Authorization' => "Bearer $token"])->assertOk();

        $this->assertGuest('web');
    }

    /** @test */
    public function unauthenticatedUserCannotLogout()
    {
        $this->json('POST', route('auth.logout'))->assertStatus(401);
    }
}
