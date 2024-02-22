<?php

namespace Tests\Feature\Controllers\V1\MyLessonPreferenceController;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private $route = 'api/v1/auth/lesson-preferences';

    public function setUp(): void
    {
        parent::setUp();

        $this->createLessonCategories();
    }

    /** @test */
    public function user_should_be_able_to_store_their_lesson_preferences()
    {
        $user = User::factory()->create();

        // assert user has no lesson categories
        $this->assertTrue($user->lessonPreferences()->count() == 0);

        $totalPreferedLessonCategories = 2;
        $categoryIds = Category::inRandomOrder()
            ->take($totalPreferedLessonCategories)
            ->get()
            ->pluck('id')
            ->toArray();

        $this->actingAs($user)
            ->postJson($this->route, [
                'category_ids' => $categoryIds
            ])
            ->assertOk()
            ->assertJsonCount($totalPreferedLessonCategories, 'data');
    }

    /** @test */
    public function user_should_not_able_to_include_invalid_lesson_categories()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson($this->route, [
                'category_ids' => ['not_existing', -1],
            ])
            ->assertUnprocessable()
            ->assertInvalid(['category_ids.0', 'category_ids.1']);
    }

    /** @test */
    public function user_should_be_able_to_change_their_lesson_preferences()
    {
        $user = User::factory()
            ->create();

        $categories = Category::get();

        $preferedLessonCategories = $categories->take(2)->pluck('id')->toArray();

        $user->setLessonPreferences($preferedLessonCategories);
        $this->assertLessonPreferencesWereSet($user, $preferedLessonCategories);

        // set new category
        $totalNewLessonPreferences = 3;
        $newPreferedLessonCategories = $categories->sortByDesc('id')
            ->take($totalNewLessonPreferences)
            ->pluck('id')
            ->toArray();

        $this->actingAs($user)
            ->postJson($this->route, [
                'category_ids' => $newPreferedLessonCategories
            ])
            ->assertOk()
            ->assertJsonCount($totalNewLessonPreferences, 'data');

        $this->assertLessonPreferencesWereSet($user, $newPreferedLessonCategories);
    }

    protected function createLessonCategories(int $n = 5): void
    {
        Category::factory()->lessonType()->count($n)->create();
    }

    private function assertLessonPreferencesWereSet(User $user, array $expectedLessonCategoryIds)
    {
        $user->lessonPreferences()->get()->each(
            fn ($category) => $this->assertTrue(in_array($category->id, $expectedLessonCategoryIds))
        );
    }
}
