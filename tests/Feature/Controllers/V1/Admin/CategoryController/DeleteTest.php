<?php

namespace Tests\Feature\Controllers\V1\Admin\CategoryController;

use App\Models\Category;
use Illuminate\Http\Response;

class DeleteTest extends BaseTest
{
    /** @test */
    public function admin_should_be_able_to_delete_category()
    {
        $user = $this->createAdminUser();

        $category = Category::factory()->create();

        $this->actingAs($user)
            ->deleteJson($this->endpoint($category->getKey()))
            ->assertOk();

        $this->assertModelMissing($category);
    }

    /** @test */
    public function admin_should_not_be_able_to_delete_category_with_lessons()
    {
        $user = $this->createAdminUser();

        $category = Category::factory()
            ->lessonType()
            ->hasLessons(2)
            ->create();

        $this->actingAs($user)
            ->deleteJson($this->endpoint($category->getKey()))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    public function default_user_should_not_be_able_to_delete_category()
    {
        $user = $this->createDefaultUser();

        $category = Category::factory()->create();

        $this->actingAs($user)
            ->deleteJson($this->endpoint($category->getKey()))
            ->assertForbidden();
    }

    private function endpoint($id): string
    {
        return $this->endpoint . "/$id";
    }
}
