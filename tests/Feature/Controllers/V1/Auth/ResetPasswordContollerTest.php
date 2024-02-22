<?php

namespace Tests\Feature\Controllers\V1\Auth;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\PasswordReset;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetPasswordContollerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function userCanResetPassword()
    {
        $user = User::factory()->create();
        $pr = $user->passwordReset()->create();

        $response = $this->json('POST', route('resetPassword'), [
            'email'              => $user->email,
            'token'                 => $pr->token,
            'password'              => $pw = 'new-password',
            'password_confirmation' => $pw,
        ]);

        $response->assertOk();

        $now = now();

        // password_reset token must be remove from password_resets table
        $this->assertDatabaseMissing('password_resets', [
            'user_id'    => $user->id,
            'token'      => $pr->token,
            'expires_at' => $now,
            'created_at' => $now
        ]);

        // test login with new password
        $res = $this->json('POST', route('auth.login'), [
            'email' => $user->email,
            'password' => $pw,
        ])->assertOk();
    }

    /** @test */
    public function emailMustBeValidatedOnPasswordReset()
    {
        $user = User::factory()->create();
        $pr = create(PasswordReset::class, ['user_id' => $user->id]);

        // not a valid email
        $this->json('POST', route('resetPassword'), [
            'email' => 'not_an_email',
            'token' => $pr->token,
            'password' => $pw = 'new-password',
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['email'],
            ]);

        // email does not exist on user table
        $this->json('POST', route('resetPassword'), [
            'email' => 'some.random@email.xxx',
            'token' => $pr->token,
            'password' => $pw = 'new-password',
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['email'],
            ]);
    }

    /** @test */
    public function tokenMustBeValidatedOnPasswordReset()
    {
        $user = User::factory()->create();
        $pr = create(PasswordReset::class, ['user_id' => $user->id]);

        // token is required
        $r = $this->json('POST', route('resetPassword'), [
            'email' => $user->email,
            'token' => '',
            'password' => $pw = 'new-password',
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['token'],
            ]);

        // invalid token
        $this->json('POST', route('resetPassword'), [
            'email' => $user->email,
            'token' => 'some_random_token',
            'password' => $pw = 'new-password',
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['token'],
            ]);

        // token and email not match
        $pr = create(PasswordReset::class);
        $this->json('POST', route('resetPassword'), [
            'email' => $user->email,
            'token' => $pr->token,
            'password' => $pw = 'new-password',
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['token'],
            ]);
    }

    /** @test */
    public function passwordMustBeValidatedOnPasswordPeset()
    {
        $user = User::factory()->create();
        $pr = create(PasswordReset::class, ['user_id' => $user->id]);
        $pw = 'new-password';

        // password is required
        $r = $this->json('POST', route('resetPassword'), [
            'email' => $user->email,
            'token' => $pr->token,
            'password' => '',
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['password'],
            ]);

        // password must be 8 char above
        $this->json('POST', route('resetPassword'), [
            'email' => $user->email,
            'token' => $pr->token,
            'password' => '123',
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['password'],
            ]);

        // password and password confirmation must be equal
        $pr = create(PasswordReset::class);
        $this->json('POST', route('resetPassword'), [
            'email' => $user->email,
            'token' => $pr->token,
            'password' => $pw,
            'password_confirmation' => 'some-random-password',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['password_confirmation'],
            ]);
    }
}
