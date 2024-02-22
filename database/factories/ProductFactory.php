<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title'          => $this->faker->word,
            'description'    => $this->faker->sentence,
            'price_in_cents' => $this->faker->numberBetween(1000, 10000),
            'currency'       => config('cashier.currency'),
            'category_id'    => Category::factory()->create(['type' => CategoryType::PRODUCT ])->getKey(),
            'places_id'      => $this->faker->uuid,
            'places_address' => $this->faker->streetAddress
        ];
    }
}
