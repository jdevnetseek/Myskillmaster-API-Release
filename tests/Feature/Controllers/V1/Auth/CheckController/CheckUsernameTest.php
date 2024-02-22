<?php

namespace Tests\Feature\Controllers\V1\Auth\CheckController;

use App\Enums\ErrorCodes;
use App\Enums\UsernameType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckUsernameTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registeredUserCanCheckAccountExistViaEmail()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $res = $this->json('POST', route('auth.checkUsername'), [
            'username' => $user->email,
        ]);

        $res->assertOk()
            ->assertJsonStructure([
                'data' => ['username'],
            ])
            ->assertJson([
                'data' => ['username' => $user->email]
            ]);

        $this->assertGuest();
    }

    /** @skip */
    public function registeredUserCanCheckAccountExistViaPhoneNumber()
    {
        $user = User::factory()->create([
            "phone_number" => '+63945' . rand(1000000, 9999999),
            'phone_number_verified_at' => now(),
            'primary_username' => UsernameType::PHONE_NUMBER,
        ]);

        $this->json('POST', route('auth.checkUsername'), [
            'username' => $user->phone_number,
        ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['username'],
            ])
            ->assertJson([
                'data' => ['username' => $user->phone_number]
            ]);

        $this->assertGuest();
    }

    /** @disabled */
    public function unverifiedPhoneNumberShouldNotAbleToLogin()
    {
        $user = User::factory()->create([
            "phone_number" => '+63945' . rand(1000000, 9999999),
            'phone_number_verified_at' => null
        ]);

        $this->json('POST', route('auth.checkUsername'), [
            'username' => $user->phone_number,
        ])
            ->assertStatus(401)
            ->assertJson([
                'error_code' => ErrorCodes::UNVERIFIED_PHONE_NUMBER
            ]);
    }

    /** @disabled */
    public function unverifiedEmailShouldNotAbleToLogin()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->json('POST', route('auth.checkUsername'), [
            'username' => $user->email,
        ])
            ->assertStatus(401)
            ->assertJson([
                'error_code' => ErrorCodes::UNVERIFIED_EMAIL
            ]);
    }

    /** @test */
    public function unregisteredUsernameShouldReturnNotFound()
    {
        // test unregistered number
        $this->json('POST', route('auth.checkUsername'), [
            'username' => 'invalid@email.com',
        ])->assertStatus(404);

        // test unregistered number
        $this->json('POST', route('auth.checkUsername'), [
            'username' => '+639451111111',
        ])->assertStatus(404);
    }

    /** @test */
    public function usernameIsRequired()
    {
        $this->json('POST', route('auth.checkUsername'))
            ->assertStatus(422)
            ->assertJsonStructure([
                'errors' => ['username'],
            ]);
    }
}
