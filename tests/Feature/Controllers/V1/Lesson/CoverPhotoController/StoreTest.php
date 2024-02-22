<?php

namespace Tests\Feature\Controllers\V1\Lesson\CoverPhotoController;

use App\Models\MasterLesson;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function master_can_add_cover_photos()
    {
        $user = User::factory()
            ->hasMasterProfile()
            ->hasLessons(1)
            ->create();

        $lesson = $user->lessons()->first();

        $this->assertLessonHasNoCoverPhotos($lesson);

        Storage::fake();

        $images = [
            UploadedFile::fake()->image('photo0.jpg'),
            UploadedFile::fake()->image('photo1.png'),
        ];

        $response = $this->actingAs($user)
            ->postJson($this->endpoint($lesson->getKey()), ['images' => $images])
            ->assertOk();

        $this->assertCount(count($images), $response->getData()->data);
    }

    // - cover photos must be a valid image
    /** @test */
    public function cover_photos_must_be_valid_image()
    {
        $user = User::factory()
            ->hasMasterProfile()
            ->hasLessons(1)
            ->create();

        $lesson = $user->lessons()->first();

        Storage::fake();

        $data = [
            'images' => [
                'invalid_data',
                UploadedFile::fake()->create('invalid.txt'),
                UploadedFile::fake()->create('invalid.sh'),
                UploadedFile::fake()->create('invalid.exe')
            ],
        ];

        $validationMessage = __('validation.image', ['attribute' => 'images.0']);

        $this->actingAs($user)
            ->postJson(
                $this->endpoint($lesson->getKey()),
                ['images' => ['invalid_data']]
            )
            ->assertInvalid([
                'images.0' => $validationMessage
            ]);

        $fileExtentionToTests = ['txt', 'sh', 'exe'];
        foreach ($fileExtentionToTests as $extensionToTest) {
            $this->actingAs($user)
                ->postJson(
                    $this->endpoint($lesson->getKey()),
                    ['images' => [UploadedFile::fake()->create("invalid.$extensionToTest")]]
                )
                ->assertInvalid([
                    'images.0' => $validationMessage
                ]);
        }
    }

    // - other master should not be able to add cover photos to the lessons that they dont own

    /** @test */
    public function master_should_not_be_able_to_add_cover_photos_to_other_master_lesson()
    {
        $user = User::factory()->hasMasterProfile()->create();

        $lesson = MasterLesson::factory()->create();

        Storage::fake();

        $this->actingAs($user)
            ->postJson(
                $this->endpoint($lesson->getKey()),
                ['images' => UploadedFile::fake()->image('photo.png')]
            )
            ->assertForbidden();
    }

    private function assertLessonHasNoCoverPhotos(MasterLesson $lesson)
    {
        $this->assertTrue($lesson->cover()->doesntExist());
    }

    private function endpoint($lessonId): string
    {
        return "api/v1/lessons/$lessonId/cover-photos";
    }
}
