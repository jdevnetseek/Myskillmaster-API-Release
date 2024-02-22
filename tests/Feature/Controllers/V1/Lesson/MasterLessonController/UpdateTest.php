<?php

namespace Tests\Feature\Controllers\V1\Lesson\MasterLessonController;

use App\Models\Place;
use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Support\Str;
use App\Models\MasterLesson;

class UpdateTest extends BaseTest
{
    /** @test */
    public function master_should_be_able_to_update_title_and_description()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->create();

        $data = [
            'title' => $this->faker->word,
            'description' => $this->faker->sentence()
        ];

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'title' => data_get($data, 'title'),
                    'description' => data_get($data, 'description')
                ],
            ]);
    }

    /** @test */
    public function master_can_update_lesson_category()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->create();

        $categoryId = Category::type(CategoryType::LESSON)
            ->whereNot('id', $lesson->category_id)
            ->inRandomOrder()
            ->first()
            ->getKey();

        $data = [
            'category_id' => $categoryId,
        ];

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'category' => [
                        'id' => data_get($data, 'category_id')
                    ]
                ],
            ]);
    }

    /** @test */
    public function master_should_be_able_to_update_price_and_duration()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->create();

        $data = [
            'duration_in_hours' => $this->faker->randomDigitNotZero(),
            'lesson_price' => $this->faker->randomFloat(2)
        ];

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'duration_in_hours' => data_get($data, 'duration_in_hours'),
                    'lesson_price' => data_get($data, 'lesson_price'),
                ],
            ]);
    }

    /** @test */
    public function master_should_be_able_to_change_lesson_tags()
    {
        $user = $this->createUser();
        $lesson = MasterLesson::factory()->for($user)->create();

        $data = [
            'tags' => $this->faker->sentences(),
        ];

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'tags' => data_get($data, 'tags'),
                ]
            ]);
    }

    /** @test */
    public function master_should_be_able_to_change_location()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->create();

        $data = [
            'place_id' => Place::whereNot('id', $lesson->place_id)->inRandomOrder()->first()->getKey()
        ];

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'place' => [
                        'id' => data_get($data, 'place_id')
                    ]
                ],
            ]);
    }

    /** @test */
    public function master_should_be_able_to_change_address_or_link()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->create();

        $data = [
            'address_or_link' => $this->faker->url
        ];

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), $data)
            ->assertOk();

        $this->assertDatabaseHas('master_lessons', [
            'address_or_link' => data_get($data, 'address_or_link')
        ]);
    }

    /** @test */
    public function master_should_be_able_to_change_lesson_address()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->create();

        $data = [
            'suburb' => 'New Suburb',
            'state' => 'New State',
            'postcode' => 'New Postcode',
            'address_or_link' => 'New Address or Link'
        ];

        $response =   $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), $data)
            ->assertOk();

        $this->assertDatabaseHas('master_lessons', [
            'suburb' => data_get($data, 'suburb'),
            'state' => data_get($data, 'state'),
            'postcode' => data_get($data, 'postcode'),
            'address_or_link' => data_get($data, 'address_or_link')
        ]);
    }

    // master should be able to update remove availability

    /** @test */
    public function master_should_be_able_to_update_remote_availability()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->remoteNotSupported()->create();

        $data = [
            'is_remote_supported' => true
        ];

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'is_remote_supported' => data_get($data, 'is_remote_supported')
                ],
            ]);
    }

    /** @test */
    public function master_should_not_be_able_to_update_other_master_lesson()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->create();

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), ['title' => $this->faker->sentence()])
            ->assertForbidden();
    }

    /** @test */
    public function test_lesson_must_have_a_title_and_description()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->remoteNotSupported()->create();

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), ['title' => '', 'description' => ''])
            ->assertInvalid([
                'title' => __('validation.required', ['attribute' => 'title']),
                'description' => __('validation.required', ['attribute' => 'description']),
            ]);
    }

    public function test_lesson_title_must_have_only_have_255_characters_at_most()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->remoteNotSupported()->create();

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), ['title' => Str::random(256)])
            ->assertInvalid([
                'title' => __('validation.max.string', ['attribute' => 'title', 'max' => 255]),
            ]);
    }

    /** @test */
    public function test_lesson_description_must_have_only_have_500_characters_at_most()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->remoteNotSupported()->create();

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), ['description' => Str::random(501)])
            ->assertInvalid([
                'description' => __('validation.max.string', ['attribute' => 'description', 'max' => 500]),
            ]);
    }

    public function test_lesson_category_must_be_valid()
    {
        $user = $this->createUser();

        $lesson = MasterLesson::factory()->for($user)->remoteNotSupported()->create();

        $this->actingAs($user)
            ->putJson($this->endpoint($lesson->getKey()), ['category_id' => 0])
            ->assertInvalid([
                'category_id' => __('validation.exists', ['attribute' => 'category id'])
            ]);
    }

    protected function endpoint($lessonKey): string
    {
        return "api/v1/lessons/$lessonKey";
    }
}
