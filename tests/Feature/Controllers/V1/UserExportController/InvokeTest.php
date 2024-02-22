<?php

namespace Tests\Feature\Controllers\V1\UserExportController;

use Tests\TestCase;
use App\Models\User;
use App\Exports\UserExport;
use Laravel\Sanctum\Sanctum;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function authenticatedUserShouldBeAbleToExportUserListToExcel()
    {
        Excel::fake();
        $users = User::factory()->times(10)->create();

        /** @var User */
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );
        $token = $user->createToken(config('app.name'))->plainTextToken;

        $this->json('GET', route('users.export'), [], ['Authorization' => "Bearer $token"])
            ->assertOk();

        Excel::assertDownloaded('users.xlsx', function (UserExport $export) use ($users) {

            $count = $export->collection()->count();
            return  $count === ($users->count() + 1); // with authenticated user
        });
    }

    /** @test */
    public function unauthenticatedUserShouldNotBeAbleToExportUserListToExcel()
    {
        $users = User::factory()->times(10)->create();

        $this->getJson(route('users.export'))->assertStatus(401);
    }
}
