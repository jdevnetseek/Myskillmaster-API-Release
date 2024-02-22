<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Place;
use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlacesTableSeeder extends Seeder
{
    private $resourcePath = 'json/places.json';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $places = $this->data();

        foreach ($places as $place) {
            // if state does not exist, create it
            $state = $this->getOrCreateState(
                data_get($place, 'state'),
                data_get($place, 'country')
            );

            // if place exist, just get the model
            $city = data_get($place, 'city');
            $place = $this->getPlaceByCity($city);

            $place->fill([
                'city' => $city,
                'category' => 'city',
                'state_id' => $state->getKey(),
                'country_id' => $state->country?->getKey(),
            ]);

            $place->save();
        }
    }

    private function getPlaceByCity(string $city): Place
    {
        return Place::whereCity($city)->first() ?? new Place;
    }

    private function getOrCreateState(array $state, string $country): State
    {
        $country = Country::whereName($country)->first();

        return State::firstOrCreate(
            [
                'name' => data_get($state, 'name'),
                'country_id' => $country->getKey(),
            ],
            [
                'short_name' => data_get($state, 'short_name'),
            ]
        );
    }

    private function data(): array
    {
        return json_decode(
            file_get_contents(resource_path($this->resourcePath)),
            true
        );
    }
}
