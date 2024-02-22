<?php

namespace Tests\Feature\Controllers\V1\Admin\CategoryController;

use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;

class UpdateTest extends BaseTest
{
    /** @test */
    public function admin_should_be_able_to_update_label_of_the_category()
    {
        $user = $this->createAdminUser();

        $category = Category::factory()->lessonType()->create();

        $data = [
            'label' => $this->faker->word(),
        ];

        $this->updateRequest($user, $category, $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'label' => data_get($data, 'label')
                ]
            ]);
    }

    /** @test */
    public function admin_should_not_be_able_to_remove_label_of_category()
    {
        $user = $this->createAdminUser();

        $category = Category::factory()->lessonType()->create();

        $this->updateRequest($user, $category, ['label' => ''])
            ->assertUnprocessable()
            ->assertInvalid([
                'label' => __('validation.required', ['attribute' => 'label'])
            ]);
    }

    /** @test */
    public function default_users_should_not_be_able_to_update_category()
    {
        $user = $this->createDefaultUser();

        $category = Category::factory()->lessonType()->create();

        $this->updateRequest($user, $category, ['label' => $this->faker->word])
            ->assertForbidden();
    }

    public function test_category_label_must_be_unique()
    {
        $user = $this->createAdminUser();

        $label = $this->faker->word;

        Category::factory()->lessonType()->create([
            'label' => $label,
        ]);

        $category = Category::factory()->lessonType()->create();

        $this->updateRequest($user, $category, ['label' => $label])
            ->assertUnprocessable()
            ->assertInvalid([
                'label' => __('validation.unique', ['attribute' => 'label'])
            ]);
    }

    public function test_category_label_must_have_only_255_characters_at_most()
    {
        $user = $this->createAdminUser();

        $category = Category::factory()->lessonType()->create();

        $this->updateRequest($user, $category, ['label' => Str::random(256)])
        ->assertUnprocessable()
        ->assertInvalid([
            'label' => __('validation.max.string', ['attribute' => 'label', 'max' => 255])
        ]);
    }

    public function test_should_return_404_when_updating_a_non_existing_category()
    {
        $nonExistingCategoryId = 0;

        $this->actingAs($this->createAdminUser())
            ->putJson($this->updateEndpoint($nonExistingCategoryId))
            ->assertNotFound();
    }

    private function updateRequest(
        User $user,
        Category $category,
        array $data = []
    ): \Illuminate\Testing\TestResponse {

        return $this->actingAs($user)
            ->putJson($this->updateEndpoint($category->getKey()), $data);
    }

    private function updateEndpoint(int $categoryId): string
    {
        return $this->endpoint . "/$categoryId";
    }
}
