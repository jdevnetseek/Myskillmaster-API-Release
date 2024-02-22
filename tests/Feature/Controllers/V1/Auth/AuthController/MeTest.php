<?php

namespace Tests\Feature\Controllers\V1\Auth\AuthController;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticatedUserCanGetUserDetails()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );
        $token = $user->createToken(config('app.name'))->plainTextToken;

        $this->json('GET', route('auth.me'), [], ['Authorization' => "Bearer $token"])
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'full_name', 'email', 'created_at', 'updated_at'],
            ])
            ->assertJson([
                'data' => [
                    'id'         => $user->id,
                    'full_name' => $user->full_name,
                    'email'      => $user->email,
                ]
            ]);
    }

    /** @test */
    public function unauthenticatedUserCannotGetUserDetails()
    {
        $this->json('GET', route('auth.me'))->assertStatus(401);
    }
}
