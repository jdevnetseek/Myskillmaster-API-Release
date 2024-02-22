<?php

namespace Tests\Feature\Controllers\V1\GetReportCategoriesController;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\ReportCategories;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetReportsCategoriesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test
     *  @group reportUser
     */
    public function authenticatedUserShouldBeAbleToGetCategoryList()
    {
        create(ReportCategories::class);
        create(ReportCategories::class);

        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );
        $token = $user->createToken(config('app.name'))->plainTextToken;

        $this->json('GET', route('report.categories'), [], ['Authorization' => "Bearer $token"])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'label']]
            ]);
    }

    /** @test
     *  @group reportUser
     */
    public function unauthenticatedUser()
    {
        $this->json('GET', route('report.categories'))->assertStatus(401);
    }
}
