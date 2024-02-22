<?php

namespace Tests\Feature\Controllers\V1\Admin\Report\ReportController;

use Tests\TestCase;
use App\Models\User;
use App\Models\Report;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowTest extends TestCase
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
    public function authenticatedUserCanViewReportDetails()
    {
        $report = factory(Report::class)->state('lesson')->create();

        list($user, $token) = $this->authenticate();

        $response = $this->json('GET', "/api/v1/admin/reports/$report->id", [], ['Authorization' => "Bearer $token"]);
        $response->assertOk();
        $response->assertJsonStructure([
            'data' =>     [
                'id',
                'description',
                'report_type',
                'created_at',
                'reported_by',
            ]
        ]);
    }
}
