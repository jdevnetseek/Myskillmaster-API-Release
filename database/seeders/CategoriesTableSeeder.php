<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    protected $basePath = 'images/categories/lesson/';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = json_decode(
            file_get_contents(resource_path('json/lesson_categories.json')),
            true
        );

        // store the categories
        foreach ($categories as $category) {
            $label = data_get($category, 'label');

            $categoryModel = Category::firstOrCreate([
                'label' => $label,
                'type' => CategoryType::LESSON
            ]);

            // store the icon of category
            $this->addIcon($categoryModel, data_get($category, 'icon_filename'));
        }
    }

    protected function addIcon(Category $category, string $fileName): void
    {
        $file = resource_path($this->basePath . $fileName);

        if (! file_exists($file)) {
            // we will handle the default image in the model ?
            return;
        }

        $category->copyMedia($file)
            ->toMediaCollection($category->defaultCollectionName());
    }
}
