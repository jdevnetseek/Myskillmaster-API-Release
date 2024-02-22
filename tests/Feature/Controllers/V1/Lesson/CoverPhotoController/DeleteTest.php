<?php

namespace Tests\Feature\Controllers\V1\Lesson\CoverPhotoController;

use Tests\TestCase;
use App\Models\MasterLesson;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    // - success deletion
    /** @test */
    public function master_should_be_able_to_remove_cover_photo()
    {
        $lesson = $this->createLessonWithCoverPhotos();

        $deletedMedia = $lesson->cover->first();

        $this->actingAs($lesson->user)
            ->deleteJson($this->endpoint($lesson->getKey(), $deletedMedia->getKey()))
            ->assertOk();

        $this->assertModelMissing($deletedMedia);
    }

    /** @test */
    public function test_deleting_non_existing_cover_photo()
    {
        $lesson = MasterLesson::factory()
            ->create();

        $this->actingAs($lesson->user)
            ->deleteJson($this->endpoint($lesson->getKey(), 0))
            ->assertNotFound();
    }

    /** @test */
    public function masters_should_not_be_able_to_remove_cover_photos_of_the_lessons_that_they_dont_own()
    {
        $lesson = $this->createLessonWithCoverPhotos();

        $mediaToBeDeleted = $lesson->cover->first();

        $this->actingAs(User::factory()->create())
            ->deleteJson($this->endpoint($lesson->getKey(), $mediaToBeDeleted->getKey()))
            ->assertForbidden();
    }

    private function createLessonWithCoverPhotos(): MasterLesson
    {
        $lesson = MasterLesson::factory()
            ->create();

        Storage::fake();

        $lesson->addCoverPhoto([
            UploadedFile::fake()->image('photo0.png'),
            UploadedFile::fake()->image('photo1.png')
        ]);

        $this->assertLessonHasCoverPhoto($lesson);

        return $lesson;
    }

    private function assertLessonHasCoverPhoto($lesson)
    {
        $this->assertTrue($lesson->cover()->exists());
    }

    private function endpoint($lessonId, $mediaId): string
    {
        return "api/v1/lessons/$lessonId/cover-photos/$mediaId";
    }
}
