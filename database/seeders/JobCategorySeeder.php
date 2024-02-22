<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Database\Seeder;

class JobCategorySeeder extends Seeder
{
    protected $categories = [
        'Agriculture, Food and Natural Resources' => [
            'Agricultural Equipment Operators',
            'Agricultural Inspectors',
            'Agricultural Sciences Teachers, Postsecondary',
            'Animal Breeders',
            'Animal Scientists'
        ],
        'Arts, Audio/Video Technology and Communications' => [
            'Actors',
            'Art, Drama, and Music Teachers, Postsecondary',
            'Audio and Video Equipment Technicians',
            'Broadcast News Analysts',
            'Broadcast Technicians'
        ],
        'Education and Training' => [
            'Adult Basic and Secondary Education and Literacy Teachers and Instructors',
            'Anthropology and Archeology Teachers, Postsecondary',
            'Architecture Teachers, Postsecondary',
            'Archivists',
            'Area, Ethnic, and Cultural Studies Teachers, Postsecondary'
        ],
        'Government and Public Administration' => [
            'Air Crew Members',
            'Air Crew Officers',
            'Aircraft Launch and Recovery Officers',
            'Aircraft Launch and Recovery Specialists',
            'Appraisers and Assessors of Real Estate'
        ],
        'Hospitality and Tourism' => [
            'Amusement and Recreation Attendants',
            'Animal Trainers',
            'Athletes and Sports Competitors',
            'Baggage Porters and Bellhops',
            'Bakers'
        ],
        'Information Technology' => [
            'Business Intelligence Analysts',
            'Computer and Information Research Scientists',
            'Computer Network Architects',
            'Computer Network Support Specialists',
            'Computer Programmers'
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->categories as $category => $subcategories) {
            $categoryModel = Category::firstOrCreate([ 'label' => $category, 'type' => CategoryType::JOB ]);
            foreach ($subcategories as $subcategory) {
                $categoryModel->subcategories()->firstOrCreate([ 'label' => $subcategory]);
            }
        }
    }
}
