<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            [
                'name' => 'Basic Plan',
                'slug' => 'basic-plan',
                'stripe_plan' => config('app.env') !== 'staging' ? 'price_1NDJZjE1uenK5vsDhYpbnaLC' : 'price_1MjBVcJc9qrLVvzwM3xF8Aor',
                'price' => 499,
                'number_of_lessons' => 10,
                'is_recommended_plan' => false,
                'is_recommended_for_trial' => false,
                'description' => 'Offers up to 10 classes you can upload',
                'included' => [
                    'Offers up to 10 classes you can upload',
                ]
            ],
            [
                'name' => 'Pro Plan',
                'slug' => 'pro-plan',
                'stripe_plan' => config('app.env') !== 'staging' ? 'price_1NDJc1E1uenK5vsDEcHmnaD1' : 'price_1MjBWJJc9qrLVvzwRrZ7aIT7',
                'price' => 1499,
                'number_of_lessons' => 20,
                'is_recommended_plan' => true,
                'is_recommended_for_trial' => false,
                'description' => 'Offers up to 20 classes you can upload',
                'included' => [
                    'Offers up to 20 classes you can upload',
                ]
            ],
            [
                'name' => 'Maestro Plan',
                'slug' => 'master-plan',
                'stripe_plan' => config('app.env') !== 'staging' ? 'price_1NDJd2E1uenK5vsDgovV8skQ' : 'price_1MjBXgJc9qrLVvzwV5ofPoa4',
                'price' => 2499,
                'number_of_lessons' => -1,
                'is_recommended_plan' => false,
                'is_recommended_for_trial' => true,
                'description' => 'Offers limitless classes you can upload',
                'included' => [
                    'Unlimited classes',
                ]
            ]
        ];

        foreach ($plans as $plan) {
            $data = Plan::where('slug', $plan['slug'])->first();

            if ($data !== null) {
                $data->update($plan);
            } else {
                PLan::create($plan);
            }
        }
    }
}
