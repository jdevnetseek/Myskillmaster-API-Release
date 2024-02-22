<?php

namespace Tests\Feature\Controllers\V1\Lesson\MasterLessonController;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

abstract class BaseTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedPlans();
    }

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->hasMasterProfile()->create($attributes);
    }

    private function seedPlans()
    {
        $this->artisan('db:seed', ['--class' => 'CountriesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlacesTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoriesTableSeeder']);
    }
}
