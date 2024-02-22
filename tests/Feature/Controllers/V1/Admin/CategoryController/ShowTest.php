<?php

namespace Tests\Feature\Controllers\V1\Admin\CategoryController;

use App\Models\Category;
use Illuminate\Testing\Fluent\AssertableJson;

class ShowTest extends BaseTest
{
    /** @test */
    public function admin_should_be_able_to_get_category_details()
    {
        $user = $this->createAdminUser();

        $category = Category::factory()->lessonType()->create();

        $response =   $this->actingAs($user)
            ->getJson($this->endpoint($category->getKey()))
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has(
                    'data',
                    fn ($json) =>
                    $json->whereAllType($this->expectedResponseDataType())
                )->etc()
            );
    }

    public function test_should_return_404_when_admin_tries_to_view_non_existing_category()
    {
        $user = $this->createAdminUser();

        $nonExistingId = 0;
        $this->actingAs($user)
            ->getJson($this->endpoint($nonExistingId))
            ->assertNotFound();
    }

    /** @test */
    public function default_user_should_not_be_able_to_use_admin_route_to_view_category_details()
    {
        $user = $this->createDefaultUser();

        $category = Category::factory()->lessonType()->create();

        $this->actingAs($user)
            ->getJson($this->endpoint($category->getKey()))
            ->assertForbidden();
    }

    protected function endpoint($id): string
    {
        return $this->endpoint . "/$id";
    }
}
