<?php

namespace Tests\Feature\Controllers\V1\ProfileController;

use Database\Seeders\CountriesTableSeeder;
use Database\Seeders\PlacesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class BaseTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlaces();
    }

    protected function updateProfileRoute(): string
    {
        return 'api/v1/auth/profile';
    }

    protected function updateAvatarRoute(): string
    {
        return $this->updateProfileRoute() . '/avatar';
    }

    protected function seedPlaces()
    {
        $this->artisan('db:seed', [
            'class' => CountriesTableSeeder::class,
        ]);

        $this->artisan('db:seed', [
            'class' => PlacesTableSeeder::class,
        ]);
    }
}
