<?php

namespace Tests\Feature\Controllers\V1\UserAvatarController;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itShouldBeAbleToUploadUserAvatar()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $res = $this->actingAs($user)
            ->json('POST', route('user.avatar.store', ['id' => $user->id]), [
                'avatar' => UploadedFile::fake()->image('avatar.png')
            ]);

        $res->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'file_name', 'collection_name', 'url', 'thumb_url']
            ]);

        $this->assertDatabaseHas('media', [
            'model_type' => (new User)->getMorphClass(),
            'model_id' => $user->id,
            'collection_name' => 'avatar'
        ]);
    }

    /** @test */
    public function itShouldRedirectToUserAvatarImage()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $this->actingAs($user)
            ->json('POST', route('user.avatar.store', ['id' => $user->id]), [
                'avatar' => UploadedFile::fake()->image('avatar.png')
            ]);

        $res = $this->actingAs($user)
            ->getJson(route('user.avatar.show', ['id' => $user->id]));

        $res->assertStatus(302);
    }

    /** @test */
    public function itShouldReturnAvatarResource()
    {
        $user = Sanctum::actingAs(
            User::factory()->create(['email_verified_at' => null]),
            ['*']
        );

        $this->actingAs($user)
            ->json('POST', route('user.avatar.store', ['id' => $user->id]), [
                'avatar' => UploadedFile::fake()->image('avatar.png')
            ]);

        $res = $this->actingAs($user)
            ->getJson(route('user.avatar.show', ['id' => $user->id, 'redirect' => false]));

        $res->assertOk()->assertJsonStructure([
            'data' => ['id', 'name', 'file_name', 'collection_name', 'url', 'thumb_url']
        ]);
    }
}
