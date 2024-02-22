<?php

namespace Tests\Feature\Controllers\V1\Admin\Report\ReportController;

use Tests\TestCase;
use App\Models\User;
use App\Models\Report;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IndexTest extends TestCase
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
    public function authenticatedUserCanListReports()
    {
        $count = 2;

        factory(Report::class, $count)->state('lesson')->create();

        list($user, $token) = $this->authenticate();

        $response = $this->json('GET', route('admin.reports.index'), [], ['Authorization' => "Bearer $token"]);
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'description',
                    'report_type',
                    'created_at',
                    'reported_by',
                ]
            ]
        ]);

        $result = $response->decodeResponseJson();
        $this->assertEquals(data_get($result, 'meta.total', 0), $count); // check if count is same.
    }

    /** @test */
    public function unauthenticatedUserCannotViewReportList()
    {
        User::factory()->create();

        $this->json('GET', route('admin.report.index'))
            ->assertStatus(401);
    }
}
