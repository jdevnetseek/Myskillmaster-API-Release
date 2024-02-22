<?php

namespace Tests\Feature\Controllers\V1\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Mail\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\PasswordReset as PasswordResetModel;
use App\Notifications\PasswordReset as PasswordResetNotification;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function userCanRequestResetPasswordTokenViaEmail()
    {
        $user = User::factory()->create();

        Notification::fake();
        Notification::assertNothingSent();

        $response =  $this->json('POST', route('forgotPassword'), [
            'email' => $user->email,
        ])->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['email', 'expires_at', 'created_at'],
            ])
            ->assertJson([
                'data' => ['email' => $user->email],
            ]);

        $this->assertDatabaseHas('password_resets', [
            'user_id' => $user->id,
        ]);

        $passwordReset = PasswordResetModel::find($user->id);

        Notification::assertSentTo($user, PasswordResetNotification::class);
    }

    /** @skip */
    public function userCanRequestResetPasswordTokenViaSms()
    {
        $user = User::factory()->create();

        Notification::fake();
        Notification::assertNothingSent();

        $this->json('POST', route('forgotPassword'), [
            'username' => $user->phone_number,
        ])->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['username', 'expires_at', 'created_at'],
            ])
            ->assertJson([
                'data' => ['username' => $user->phone_number],
            ]);

        $this->assertDatabaseHas('password_resets', [
            'user_id' => $user->id,
        ]);

        $passwordReset = PasswordResetModel::find($user->id);

        Notification::assertSentTo($user, PasswordResetNotification::class);
    }

    /** @test */
    public function emailMustBeValidated()
    {
        // Email field is required
        $response =   $this->json('POST', route('forgotPassword'), [
            'email' => '',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['email'],
            ]);

        // Email must be valid
        $this->json('POST', route('forgotPassword'), [
            'email' => 'not_a_valid_email',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['email'],
            ]);

        // Email must exists
        $this->json('POST', route('forgotPassword'), [
            'email' => 'notexisting@email.xxx',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['email'],
            ]);
    }
}
