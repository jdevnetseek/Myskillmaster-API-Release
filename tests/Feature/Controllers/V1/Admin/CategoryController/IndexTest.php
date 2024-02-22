<?php

namespace Tests\Feature\Controllers\V1\Admin\CategoryController;

use App\Models\Category;

class IndexTest extends BaseTest
{
    /** @test */
    public function admin_should_be_able_to_get_categories()
    {
        $user = $this->createAdminUser();

        $totalCategories = 7;
        Category::factory()->lessonType()->count($totalCategories)->create();

        $this->actingAs($user)
            ->getJson($this->endpoint())
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'total' => $totalCategories
                ]
            ]);
    }

    /** @test */
    public function admin_should_be_able_to_search_categories_by_label()
    {
        $user = $this->createAdminUser();

        $searchValue = 'test';
        $expectedCategories = Category::factory()
            ->lessonType()
            ->sequence(
                ['label' => $searchValue . ' ' . $this->faker->word],
                ['label' => $this->faker->word  . ' ' . $searchValue],
                ['label' => $searchValue],
            )
            ->count(3)
            ->create();

        Category::factory()->lessonType()->count(7)->create();

        $this->actingAs($user)
            ->getJson($this->endpoint(['filter' => ['search' => $searchValue]]))
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'total' => $expectedCategories->count()
                ]
            ]);
    }

    protected function endpoint(array $params = []): string
    {
        return $this->endpoint . '?' . http_build_query($params);
    }
}
