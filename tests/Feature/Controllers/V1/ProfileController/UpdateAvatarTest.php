<?php

namespace Tests\Feature\Controllers\V1\ProfileController;

use App\Models\User;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateAvatarTest extends BaseTest
{
    /** @test */
    public function user_should_be_able_upload_image_to_set_the_avatar()
    {
        $user = User::factory()->create();

        $this->assertUserDoesntHaveAnAvatar($user);

        Storage::fake();

        $this->actingAs($user)
            ->postJson(
                $this->updateAvatarRoute(),
                ['avatar' => UploadedFile::fake()->image('avatar.png')]
            )
            ->assertCreated();

        $this->assertUserHasAnAvatar($user);
    }

    /** @test */
    public function user_should_be_able_to_update_avatar_using_media_id()
    {
        $user = User::factory()->create();

        $this->assertUserDoesntHaveAnAvatar($user);
    }

    /** @test */
    public function user_should_not_be_able_to_use_invalid_file_as_avatar()
    {
        $user = User::factory()->create();

        Storage::fake();

        $this->actingAs($user)
            ->postJson(
                $this->updateAvatarRoute(),
                [
                    'avatar' => UploadedFile::fake()->create('invalid.txt')
                ]
            )
            ->assertInvalid(['avatar']);

        $this->actingAs($user)
            ->postJson(
                $this->updateAvatarRoute(),
                [
                    'avatar' => UploadedFile::fake()->create('invalid.exe')
                ]
            )
            ->assertInvalid(['avatar']);
    }

    private function assertUserDoesntHaveAnAvatar($user): void
    {
        $this->assertTrue($user->avatar()->doesntExist());
    }

    private function assertUserHasAnAvatar($user): void
    {
        $this->assertTrue($user->avatar()->exists());
    }
}
