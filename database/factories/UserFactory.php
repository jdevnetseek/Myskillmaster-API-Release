<?php

namespace Database\Factories;

use App\Models\MasterProfile;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name'        => $this->faker->firstName(),
            'last_name'         => $this->faker->lastName(),
            'email'             => Str::random() . $this->faker->unique()->safeEmail,
            'phone_number'      => '639' . mt_rand(1000000000, 9999999999),
            'birthdate'         => $this->faker->date(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    public function payoutsEnabled()
    {
        return $this->state(function (array $attributes) {
            return [
                'payouts_enabled' => true,
            ];
        });
    }
}
