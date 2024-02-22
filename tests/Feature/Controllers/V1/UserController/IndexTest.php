<?php

namespace Tests\Feature\Controllers\V1\UserController;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IndexTest extends TestCase
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
    public function authenticatedUserCanViewUserList()
    {
        User::factory()->create();
        User::factory()->create();

        list($user, $token) = $this->authenticate();

        $this->json('GET', route('users.index'), [], ['Authorization' => "Bearer $token"])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'],
                ],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
            ]);
    }

    /** @test */
    public function unauthenticatedUserMustNotViewUserList()
    {
        User::factory()->create();

        $this->json('GET', route('users.index'))
            ->assertStatus(401);
    }
}
