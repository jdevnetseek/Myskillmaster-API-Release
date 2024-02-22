<?php

namespace Tests\Feature\Controllers\V1\Auth\AccountSettings;

use App\Models\Device;
use App\Models\LessonEnrollment;
use App\Models\PasswordReset;
use App\Models\PaymentHistory;
use App\Models\Rating;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_can_delete_own_account()
    {
        $user = User::factory()->create();

        factory(PasswordReset::class)->create(['user_id' => $user->id]);
        factory(Device::class)->create(['user_id' => $user->id]);
        factory(Report::class)->create(['reported_by' => $user->id]);

        Rating::factory()->create(['user_id' => $user->id]);
        LessonEnrollment::factory()->create(['student_id' =>  $user->id]);
        PaymentHistory::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->deleteJson('/api/v1/auth/account')
            ->assertSuccessful();

        $this->assertDatabaseMissing('password_resets', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('devices', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('reports', ['reported_by' => $user->id]);
        $this->assertDatabaseMissing('ratings', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('lesson_enrollments', ['student_id' => $user->id]);
        $this->assertDatabaseMissing('payment_histories', ['user_id' => $user->id]);
    }

    public function test_account_with_master_profile_cannot_be_deleted()
    {
        $master = User::factory()->hasMasterProfile()->create();

        $this->actingAs($master)
            ->deleteJson('/api/v1/auth/account')
            ->assertForbidden();
    }
}
