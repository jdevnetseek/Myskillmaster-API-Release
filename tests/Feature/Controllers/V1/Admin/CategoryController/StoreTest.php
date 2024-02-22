<?php

namespace Tests\Feature\Controllers\V1\Admin\CategoryController;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;

class StoreTest extends BaseTest
{
    /** @test */
    public function admin_should_be_able_to_create_category_without_icon()
    {
        $user = $this->createAdminUser();

        $data = $this->createData();

        $this->actingAs($user)
            ->postJson($this->endpoint, $data)
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'label' => data_get($data, 'label'),
                ],
            ])
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', fn ($json) => $this->assertResponseDataType($json))
                    ->etc()
            );
    }

    /** @test */
    public function admin_should_be_able_to_create_category_with_icon()
    {
        $user = $this->createAdminUser();

        Storage::fake();

        $data = $this->createData([
            'icon' => UploadedFile::fake()->image('icon.png'),
        ]);

        $response = $this->actingAs($user)
            ->postJson($this->endpoint, $data)
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'label' => data_get($data, 'label')
                ],
            ]);

        $category = Category::find(data_get($response->getData(), 'data.id'));

        $this->assertNotNull($category->icon);
    }

    /** @test */
    public function default_user_should_not_be_able_to_create_category()
    {
        $this->actingAs($this->createDefaultUser())
            ->postJson($this->endpoint, $this->createData())
            ->assertForbidden();
    }

    /** @test */
    public function admin_should_not_be_able_to_create_category_without_label()
    {
        $user = $this->createAdminUser();

        // no label

        $this->postRequest($user, [])
            ->assertUnprocessable()
            ->assertInvalid([
                'label' => __('validation.required', ['attribute' => 'label'])
            ]);

        // empty label
        $this->postRequest($user, ['label' => ''])
            ->assertUnprocessable()
            ->assertInvalid([
                'label' => __('validation.required', ['attribute' => 'label'])
            ]);

        // label must not exceed 255 characters
        $this->postRequest($user, ['label' => Str::random(256)])
            ->assertUnprocessable()
            ->assertInvalid([
                'label' => __('validation.max.string', ['attribute' => 'label', 'max' => '255'])
            ]);
    }

    public function test_icon_must_be_an_image()
    {
        $user = $this->createAdminUser();

        Storage::fake();

        $data = $this->createData([
            'icon' => UploadedFile::fake()->create('sample.txt'),
        ]);

        $this->postRequest($user, $data)
            ->assertUnprocessable()
            ->assertInvalid([
                'icon' => __('validation.image', ['attribute' => 'icon'])
            ]);
    }

    private function postRequest($user, $data): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)
            ->postJson($this->endpoint, $data);
    }
}
