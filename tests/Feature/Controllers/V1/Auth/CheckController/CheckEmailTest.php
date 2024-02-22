<?php

namespace Tests\Feature\Controllers\V1\Auth\CheckController;

use Tests\TestCase;
use App\Models\User;
use App\Enums\ErrorCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registeredUserCanCheckAccountExistViaEmail()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->json('POST', route('auth.checkEmail'), [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['email'],
            ])
            ->assertJson([
                'data' => ['email' => $user->email]
            ]);

        $this->assertGuest();
    }

    /** @disabled */
    public function unverifiedEmailShouldNotAbleToLogin()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->json('POST', route('auth.checkEmail'), [
            'email' => $user->email,
        ])
            ->assertStatus(401)
            ->assertJson([
                'error_code' => ErrorCodes::UNVERIFIED_EMAIL
            ]);
    }

    /** @test */
    public function unregisteredEmailShouldReturnNotFound()
    {
        $this->json('POST', route('auth.checkEmail'), [
            'email' => 'unregistered@email.com',
        ])->assertStatus(404);
    }

    /** @test */
    public function itShouldValidateEmail()
    {
        // email is required
        $this->json('POST', route('auth.checkEmail'))
            ->assertStatus(422)
            ->assertJsonStructure([
                'errors' => ['email'],
            ]);

        // invalid email format
        $this->json('POST', route('auth.checkEmail'), [
            'email' => 'not_an_email'
        ])->assertStatus(422)
            ->assertJsonStructure([
                'errors' => ['email'],
            ]);

        // should not accept a phone_number
        $this->json('POST', route('auth.checkEmail'), [
            'email' => '+639453200575'
        ])->assertStatus(422)
            ->assertJsonStructure([
                'errors' => ['email'],
            ]);
    }
}
