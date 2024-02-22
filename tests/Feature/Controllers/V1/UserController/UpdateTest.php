<?php

namespace Tests\Feature\Controllers\V1\UserController;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function authenticatedUserCanUpdateUserDetails()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $this->actingAs($user)
            ->putJson(route('users.update', ['user' => $user->id]), [
                'first_name' => $fn = $this->faker()->firstName(),
                'last_name' => $ln = $this->faker()->lastName(),
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'first_name', 'last_name', 'email']
            ])
            ->assertJson([
                'data' => ['id' => $user->id, 'first_name' => $fn, 'last_name' => $ln]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => $fn,
            'last_name' => $ln,
        ]);

        tap($user->fresh(), function ($user) use ($fn) {
            $this->assertEquals($user->first_name, $fn);
        });
    }

    /** @test */
    public function fullNameIsRequiredToUpdateUserDetails()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $this->actingAs($user)->putJson(route('users.update', ['user' => $user->id]), [
            'first_name' => '',
            'last_name' => '',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['first_name', 'last_name'],
            ]);
    }

    /** @test */
    public function unauthenticatedUserCannotUpdateUserDetails()
    {
        $user = User::factory()->create();

        $this->putJson(route('users.update', ['user' => $user->id]), [
            'first_name' => $this->faker()->firstName(),
            'last_name' => $this->faker()->lastName(),
        ])
            ->assertStatus(401);
    }

    /** @test */
    public function otherUserCannotUpdateOtherUserDetails()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );
        $user2 = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $this->actingAs($user2)
            ->putJson(route('users.update', ['user' => $user->id]), [
                'first_name' => $this->faker()->firstName(),
                'last_name' => $this->faker()->lastName(),
            ])
            ->assertStatus(422); // Now returns validation error
    }

    /** @test */
    public function itShouldMarkEmailAsUnverifiedIfChanged()
    {
        $user = Sanctum::actingAs(
            User::factory()->create([
                'email_verified_at' => now(),
                'phone_number_verified_at' => null
            ]),
            ['*']
        );

        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->isEmailVerified());
        $this->assertTrue($user->isVerified());

        $this->actingAs($user)->putJson(route('users.update', ['user' => $user->id]), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $this->faker()->safeEmail(),
        ])->assertOk();

        tap($user->fresh(), function ($user) {
            $this->assertNull($user->email_verified_at);
            $this->assertFalse($user->isEmailVerified());
            $this->assertFalse($user->isVerified());
        });
    }

    /** @test */
    public function itShouldMarkPhoneNumberAsUnverifiedIfChanged()
    {
        $user = Sanctum::actingAs(
            User::factory()->create([
                'phone_number_verified_at' => now(),
                'email_verified_at' => null
            ]),
            ['*']
        );

        $this->assertNotNull($user->phone_number_verified_at);
        $this->assertTrue($user->isPhoneNumberVerified());
        $this->assertTrue($user->isVerified());

        $this->actingAs($user)->putJson(route('users.update', ['user' => $user->id]), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone_number' => '639' . random_int(10000000, 99999999),
        ])->assertOk();

        tap($user->fresh(), function ($user) {
            $this->assertNull($user->phone_number_verified_at);
            $this->assertFalse($user->isPhoneNumberVerified());
            $this->assertFalse($user->isVerified());
        });
    }
}
