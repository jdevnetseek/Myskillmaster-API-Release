<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $raw = file_get_contents(resource_path('json/countries.json'));
        $countries = json_decode($raw, true);

        foreach ($countries as $country) {
            Country::firstOrCreate(
                ['iso3' => $country['iso3']],
                [
                    'name' => $country['name'],
                    'iso2' => $country['iso2'],
                    'iso3' => $country['iso3'],
                    'dial_code' => $country['dial_code'],
                    'flag' => $country['flag'],
                ]
            );
        }
    }
}
