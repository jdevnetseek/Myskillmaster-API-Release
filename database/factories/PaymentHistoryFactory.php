<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentHistory>
 */
class PaymentHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $subTotal = $this->faker->numberBetween(1, 100);
        $tax = $this->faker->numberBetween(1, 10);

        return [
            'user_id' => fn () => User::factory()->create()->id,
            'stripe_id' => $this->faker->lexify('????????'),
            'subtotal' => $subTotal,
            'tax' => $tax,
            'total' => $subTotal + $tax,
        ];
    }
}
