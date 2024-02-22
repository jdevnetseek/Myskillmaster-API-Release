<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    protected $categories = [
        'Electronic Devices',
        'Electronic Accessories',
        'TV & Home Appliances',
        'Health & Beauty',
        'Babies & Toy',
        'Groceries & Pets',
        'Home & Living',
        'Women\'s Fashion',
        'Men\'s Fashion',
        'Fashion Accessories',
        'Sports & Lifestyle',
        'Automotive & Motorcycle',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->categories as $category ) {
            Category::firstOrCreate([ 'label' => $category, 'type' => CategoryType::PRODUCT ]);
        }
    }
}
