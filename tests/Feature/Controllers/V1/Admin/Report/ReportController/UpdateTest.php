<?php

namespace Tests\Feature\Controllers\V1\Admin\Report\ReportController;

use App\Enums\ReportStatus;
use Tests\TestCase;
use App\Models\User;
use App\Models\Report;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate()
    {
        /** @var User */
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );
        $token = $user->createToken(config('app.name'))->plainTextToken;

        return [$user, $token];
    }


    /** @test */
    public function authenticatedUserCanUpdateReport()
    {
        $report = factory(Report::class)->state('lesson')->create();

        list($user, $token) = $this->authenticate();

        $status = ReportStatus::getRandomValue();
        $response = $this->putJson("/api/v1/admin/reports/$report->id", ["status" => $status], ['Authorization' => "Bearer $token"]);
        $response->assertOk();

        $response->assertJsonFragment(['status' => $status]);
    }
}
