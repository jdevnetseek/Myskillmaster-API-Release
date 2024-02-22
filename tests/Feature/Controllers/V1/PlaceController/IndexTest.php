<?php

namespace Tests\Feature\Controllers\V1\PlaceController;

use Database\Seeders\CountriesTableSeeder;
use Database\Seeders\PlacesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlaces();
    }

    public function test_return_all_available_places()
    {
        $this->getJson('api/v1/places')
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has(
                    'data.0',
                    fn ($json) => $json->whereAllType($this->expectedResponseDataType())
                )
                ->etc()
            );
    }

    private function seedPlaces()
    {
        $seeders = [
            CountriesTableSeeder::class,
            PlacesTableSeeder::class,
        ];

        foreach ($seeders as $seederClass) {
            $this->artisan('db:seed', ['class' => $seederClass]);
        }
    }

    private function expectedResponseDataType(): array
    {
        return [
            'id' => 'integer',
            'city' => 'string',

            'state' => 'array',
            'state.name' => 'string',
            'state.short_name' => 'string',

            'country' => 'array',
            'country.name' => 'string',

            'formatted_address' => 'string',
        ];
    }
}
