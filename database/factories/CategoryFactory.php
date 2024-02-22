<?php

namespace Database\Factories;

use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'label'        => $this->faker->words(3, true),
            'description'  => $this->faker->paragraph,
            'type'         => CategoryType::GENERAL,
        ];
    }

    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function productType()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => CategoryType::PRODUCT,
            ];
        });
    }

    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function lessonType()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => CategoryType::LESSON,
            ];
        });
    }
}
