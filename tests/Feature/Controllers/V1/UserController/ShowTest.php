<?php

namespace Tests\Feature\Controllers\V1\UserController;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticatedUserCanViewUserDetails()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );
        $user2 = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $this->actingAs($user)
            ->getJson(route('users.show', ['user' => $user->id]))
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'first_name', 'last_name', 'email']
            ]);

        $this->actingAs($user)
            ->getJson(route('users.show', ['user' => $user2->id]))
            ->assertOk();
    }

    /** @test */
    public function unauthenticatedUserCannotViewUserDetails()
    {
        $user = User::factory()->create();

        $this->getJson(route('users.show', ['user' => $user->id]))
            ->assertStatus(401);
    }
}
