<?php

namespace Tests\Feature\Controllers\V1\Auth\ResetPasswordController;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetVerifiedAccountTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     * should be able to search for a verified email or phone number
     *
     * @return void
     */
    public function shouldBeAbleToSearchForEmailOrPhoneNumber()
    {
        $user = User::factory()->create();

        $response = $this->json(
            'POST',
            route('resetPassword.get-verified-account'),
            ['email' => $user->email]
        )
            ->assertOk()
            ->assertJsonStructure([
                'is_email_verified',
                'is_phone_verified',
                'verified_account'
            ]);
    }
}
