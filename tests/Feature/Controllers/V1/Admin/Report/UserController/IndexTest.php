<?php

namespace Tests\Feature\Controllers\V1\Admin\Report\UserController;

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
    public function authenticatedUserCanViewReportList()
    {
        $count = 2;

        factory(Report::class, $count)->create();

        list($user, $token) = $this->authenticate();

        $response = $this->json('GET', route('admin.report.users.index'), [], ['Authorization' => "Bearer $token"]);
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'description',
                    'report_type',
                    'reported_at',
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

        $this->json('GET', route('admin.report.users.index'))
            ->assertStatus(401);
    }
}
