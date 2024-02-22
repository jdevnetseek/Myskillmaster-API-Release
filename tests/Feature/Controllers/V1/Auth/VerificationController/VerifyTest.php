<?php

namespace Tests\Feature\Controllers\V1\Auth\VerificationController;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerifyTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );
        $token = $user->createToken(config('app.name'))->plainTextToken;

        return [$user, $token];
    }

    /** @test */
    public function unverifiedUserCanVerifyEmail()
    {
        list($user, $token) = $this->authenticate();

        $this->json(
            'POST',
            route('verification.verify'),
            ['via' => 'email', 'token' => $user->email_verification_code],
            ['Authorization' => "Bearer $token"]
        )->assertOk();


        tap($user->fresh(), function ($user) {
            $this->assertNotNull($user->email_verified_at);
        });
    }

    /** @test */
    public function unverifiedUserCanVerifyPhoneNumber()
    {
        list($user, $token) = $this->authenticate();

        $this->json(
            'POST',
            route('verification.verify'),
            ['via' => 'phone_number', 'token' => $user->phone_number_verification_code],
            ['Authorization' => "Bearer $token"]
        )->assertOk();


        tap($user->fresh(), function ($user) {
            $this->assertNotNull($user->phone_number_verified_at);
        });
    }

    /** @test */
    public function tokenShouldBeValidated()
    {
        list($user, $token) = $this->authenticate();

        // token is required
        $this->json('POST', route('verification.verify'), [
            'token' => '',
            'via' => 'email'
        ], ['Authorization' => "Bearer $token"])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['token']]);

        // token must be equal to user email_verification_token
        $this->json('POST', route('verification.verify'), [
            'token' => Str::random(5), // some random token
            'via' => 'email'
        ], ['Authorization' => "Bearer $token"])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['token']]);
    }

    /** @test */
    public function viaShouldBeValidated()
    {
        list($user, $token) = $this->authenticate();

        // via is required
        $this->json('POST', route('verification.verify'), [
            'token' => $user->email_verification_code,
            'via' => ''
        ], ['Authorization' => "Bearer $token"])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['via']]);

        $this->json('POST', route('verification.verify'), [
            'token' => Str::random(5),
            'via' => 'facebook' // via not exist
        ], ['Authorization' => "Bearer $token"])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['via']]);
    }
}
