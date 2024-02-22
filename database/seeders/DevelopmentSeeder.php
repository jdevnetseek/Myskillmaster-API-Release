<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedACL();
        $this->seedUsers();
        $this->call([
            CountriesTableSeeder::class,
            PlacesTableSeeder::class,
            CategoriesTableSeeder::class,
        ]);
    }

    private function seedACL()
    {
        Artisan::call('app:acl:sync');
    }

    private function seedUsers()
    {
        User::factory()->times(rand(10, 100))->create();
    }
}
