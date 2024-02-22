<?php

namespace Tests\Feature\Controllers\V1\UserReportController;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\ReportCategories;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvokeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function userCanReportAnotherUser()
    {
        $payload = [
            'reason_ids'  => [factory(ReportCategories::class)->create()->id],
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

        $reportedUser = User::factory()->create();
        $response = $this->actingAs($user)
            ->post(route('users.report', $reportedUser), $payload);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'description',
                'report_type',
                'reported_at',
                'reported_by',
                'reason_id',
                'reported',
                'attachments'
            ]
        ]);
    }
}
