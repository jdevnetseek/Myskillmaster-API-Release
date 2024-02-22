<?php

namespace Tests\Feature\Controllers\V1\ProfileController;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;

class UpdateTest extends BaseTest
{
    use WithFaker;

    /**
     * What to test
     *  - user should be able to update their location (state, city, country?)
     */

    /** @test */
    public function user_should_be_able_to_update_their_first_name()
    {
        $user = User::factory()->create();

        $firstName = $this->faker->firstName();

        $this->actingAs($user)
            ->putJson($this->updateProfileRoute(), ['first_name' => $firstName])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('data.first_name', $firstName)
                    ->etc()
            );

        $this->assertTrue($user->fresh()->first_name == $firstName);
    }

    /** @test */
    public function user_should_be_able_to_update_their_last_name()
    {
        $user = User::factory()->create();

        $lastName = $this->faker->lastName();

        $this->actingAs($user)
            ->putJson($this->updateProfileRoute(), ['last_name' => $lastName])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('data.last_name', $lastName)
                    ->etc()
            );

        $this->assertTrue($user->fresh()->last_name == $lastName);
    }

    /** @test */
    public function user_should_be_able_to_update_their_birthdate()
    {
        $user = User::factory()->create();

        $birthdate = $this->faker->date();

        $this->actingAs($user)
            ->putJson($this->updateProfileRoute(), ['birthdate' => $birthdate])
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('data.birthdate', $birthdate)
                    ->etc()
            );

        $this->assertTrue($user->fresh()->birthdate == $birthdate);
    }

    /** @test */
    public function user_should_be_able_to_set_their_location_from_available_options()
    {
        $user = User::factory()->create();

        $placeId = \App\Models\Place::inRandomOrder()->first()->getKey();

        $this->actingas($user)
            ->putJson(
                $this->updateProfileRoute(),
                ['place_id' => $placeId]
            )
            ->assertOk()
            ->assertJson([
                'data' => [
                    'place_id' => $placeId,
                ],
            ]);
    }

    /** @test */
    public function user_should_not_be_able_to_set_an_invalid_location()
    {
        $user = User::factory()->create();

        $this->actingas($user)
            ->putJson(
                $this->updateProfileRoute(),
                ['place_id' => 0]
            )
            ->assertUnprocessable()
            ->assertInvalid([
                'place_id'
            ]);

            $this->actingas($user)
            ->putJson(
                $this->updateProfileRoute(),
                ['place_id' => 'invalid']
            )
            ->assertUnprocessable()
            ->assertInvalid([
                'place_id'
            ]);
    }

    /** @test */
    public function user_should_not_be_able_to_remove_their_first_name()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson($this->updateProfileRoute(), ['first_name' => ''])
            ->assertUnprocessable()
            ->assertInvalid(['first_name']);
    }

    /** @test */
    public function user_should_not_be_able_to_remove_their_last_name()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson($this->updateProfileRoute(), ['last_name' => ''])
            ->assertUnprocessable()
            ->assertInvalid(['last_name']);
    }

     /** @test */
     public function user_should_not_be_able_to_set_invalid_birthdate()
     {
         $user = User::factory()->create();

         $this->actingAs($user)
             ->putJson($this->updateProfileRoute(), ['birthdate' => '01/01/2000'])
             ->assertUnprocessable()
             ->assertInvalid(['birthdate']);
     }

    /** @test */
    public function unauthenticated_request_should_return_forbidden_response()
    {
        $this->putJson($this->updateProfileRoute(), ['last_name' => $this->faker->lastName()])
            ->assertUnauthorized();
    }

    protected function updateProfileRoute(): string
    {
        return 'api/v1/auth/profile';
    }
}
