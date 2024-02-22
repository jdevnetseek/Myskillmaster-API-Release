<?php

namespace Tests\Feature\Controllers\V1\Lesson\LessonReportController;

use App\Models\MasterLesson;
use App\Models\ReportCategories;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvokeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function userCanReportLesson()
    {
        $reportCategories = factory(ReportCategories::class, 5)->state('lessonType')->create();

        $payload = [
            'reason_ids'  => $reportCategories->map(fn ($category) => $category->id)->toArray(),
            'description' => $this->faker->sentence,
            'attachments' => [
                UploadedFile::fake()->image('imagetest.png'),
                UploadedFile::fake()->image('anotherimage.png')
            ]
        ];

        /** @var User */
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $lesson = MasterLesson::factory()->create();

        $lessonId = $lesson->getKey();

        $response = $this->actingAs($user)
            ->postJson("/api/v1/lessons/$lessonId/report", $payload)
            ->dump();

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'description',
                'report_type',
                'reported_at',
                'reported_by',
                'reasons',
                'reported',
                'attachments'
            ]
        ]);
    }
}
