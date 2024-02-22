<?php

namespace Tests\Feature\Controllers\V1\MyLessonPreferenceController;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private $route = 'api/v1/auth/lesson-preferences';

    /** @test */
    public function user_should_be_able_to_get_their_lesson_preferences()
    {
        $user = User::factory()
            ->has(Category::factory()->lessonType()->count(3), 'lessonPreferences')
            ->create();

        $this->actingAs($user)
            ->get($this->route)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'category_id',
                        'label',
                    ]
                ]
            ]);
    }
}
