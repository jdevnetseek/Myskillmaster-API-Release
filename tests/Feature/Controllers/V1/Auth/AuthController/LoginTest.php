<?php

namespace Tests\Feature\Controllers\V1\Auth\AuthController;

use Tests\TestCase;
use App\Models\User;
use App\Enums\ErrorCodes;
use App\Enums\UsernameType;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registeredUserCanLoginViaEmail()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => now()]),
            ['*']
        );

        $response = $this->json('POST', route('auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['access_token', 'token_type', 'expires_in'],
            ]);

        $this->assertAuthenticated();
        $this->assertAuthenticatedAs($user);
    }

    /** @skip */
    public function registeredUserCanLoginViaPhoneNumber()
    {
        $user = Sanctum::actingAs(
            User::factory()->create([
                'primary_username' => UsernameType::PHONE_NUMBER,
                'phone_number_verified_at' => now()
            ]),
            ['*']
        );

        $response = $this->json('POST', route('auth.login'), [
            'username' => $user->phone_number,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['access_token', 'token_type', 'expires_in'],
            ]);

        $this->assertAuthenticated();
        $this->assertAuthenticatedAs($user);
    }

    /** @disabled */
    public function unverifiedUserEmailCannotLogIn()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->json('POST', route('auth.login'), [
            'username' => $user->email,
            'password' => 'password'
        ])->assertStatus(401)
            ->assertJson([
                'error_code' => ErrorCodes::UNVERIFIED_EMAIL
            ]);
    }

    /** @disabled */
    public function unverifiedUserPhoneNumberCannotLogIn()
    {
        $user = User::factory()->create([
            'phone_number_verified_at' => null,
        ]);

        $this->json('POST', route('auth.login'), [
            'username' => $user->phone_number,
            'password' => 'password'
        ])->assertStatus(401)
            ->assertJson([
                'error_code' => ErrorCodes::UNVERIFIED_PHONE_NUMBER
            ]);
    }

    /** @test */
    public function unregisteredUsersCannotLogIn()
    {
        $this->json('POST', route('auth.login'), [
            'email' => 'some.random@email.com',
            'password' => 'password'
        ])->assertStatus(401);
    }

    /** @disabled */
    public function wrongUserPasswordCannotLogIn()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $this->json('POST', route('auth.login'), [
            'email' => $user->email,
            'password' => 'th!$_!$_n()t_ah_p@ssw0rd',
        ])->assertStatus(401);
    }
}
