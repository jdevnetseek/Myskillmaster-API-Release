<?php

namespace Tests\Feature\Controllers\V1\Admin\CategoryController;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

abstract class BaseTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $endpoint = 'api/v1/admin/categories';

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('app:acl:sync');
    }

    protected function createAdminUser(array $attrs = []): User
    {
        return $this->createUser($attrs, true);
    }

    protected function createDefaultUser(array $attrs = []): User
    {
        return $this->createUser($attrs);
    }

    protected function createUser(array $attrs = [], bool $isAdmin = false): User
    {
        $user = User::factory()->create($attrs);

        if ($isAdmin) {
            $user->assignRole(Role::ADMIN);
        }

        return $user;
    }

    protected function createData(array $attrs = []): array
    {
        return array_merge([
            'label' => $this->faker->word(),
            'keywords' => $this->faker->words(),
        ], $attrs);
    }

    protected function expectedResponseDataType(): array
    {
        return [
            'id'    => 'integer',
            'slug'  => 'string',
            'label' => 'string',
            'type' => 'string',
            'icon_url'  => 'string|null',
        ];
    }

    protected function assertResponseDataType(AssertableJson $json)
    {
        $json->whereAllType($this->expectedResponseDataType());
    }
}
