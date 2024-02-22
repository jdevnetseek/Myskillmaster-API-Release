<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportCategories;
use App\Enums\ReportCategoryType;
use App\Models\Report;

class ReportCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Report::query()->delete();
        ReportCategories::query()->delete();

        $productReports = collect([
            'Master unavailable',
            'Incorrect address',
            'Inappropriate behaviour',
            'Safety concerns',
            'Falsely listed lesson',
            'False information',
            'Bully or harassment',
            'Stealing',
            'Something else'
        ]);

        $productReports->each(function ($category) {
            ReportCategories::firstOrCreate([
                'label' => $category, 'type' => ReportCategoryType::LESSONS
            ]);
        });
    }
}
