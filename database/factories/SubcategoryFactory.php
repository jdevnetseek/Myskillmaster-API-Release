<?php

namespace Database\Factories;

use App\Models\Category;
use App\Enums\CategoryType;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubcategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subcategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'parent_id'    => Category::factory()->create()->getKey(),
            'label'        => 'Sub:' . $this->faker->words(3, true),
            'description'  => $this->faker->paragraph,
            'type'         => CategoryType::GENERAL,
        ];
    }
}
