<?php

namespace Tests\Feature\Controllers\V1\Auth\VerificationController;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\VerifyPhoneNumber;

class ResendTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );
        $token = $user->createToken(config('app.name'))->plainTextToken;

        return [$user, $token];
    }

    /** @test */
    public function unverifiedUserCanResendEmailVerificationToken()
    {
        Notification::fake();
        Notification::assertNothingSent();

        list($user, $token) = $this->authenticate();

        $this->json(
            'POST',
            route('verification.resend'),
            ['via' => 'email'],
            ['Authorization' => "Bearer $token"]
        )->assertOk();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class,
            function ($notification) use ($user) {
                $notifier = $notification->toMail($user);
                return $notifier->user === $user;
            }
        );
    }

    /** @test */
    public function unverifiedUserCanResendPhoneNumberVerificationToken()
    {
        Notification::fake();
        Notification::assertNothingSent();

        list($user, $token) = $this->authenticate();

        $this->json(
            'POST',
            route('verification.resend'),
            ['via' => 'phone_number'],
            ['Authorization' => "Bearer $token"]
        )->assertOk();

        Notification::assertSentTo($user, VerifyPhoneNumber::class);
    }
}
