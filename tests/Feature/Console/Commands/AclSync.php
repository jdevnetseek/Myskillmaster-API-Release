<?php

namespace Tests\Feature\Console\Commands;

use App\Enums\Permission;
use App\Enums\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as SpatieRole;

class AclSync extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itShouldSeedRolesAndPermissionToDatabase()
    {
        $roles = Role::getValues();
        $permissions = Permission::getValues();

        $this->artisan('app:acl:sync')
            ->assertExitCode(0);

        // All roles should be added to database
        foreach ($roles as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role]);
        }

        // All permissions should be added to database
        foreach ($permissions as $permission) {
            $this->assertDatabaseHas('permissions', ['name' => $permission]);
        }

        // Every roles should be given with the default defined permissions
        foreach ($roles as $role) {
            $roleModel = SpatieRole::whereName($role)->first();

            $rolePermissions = Role::getPermissions($role);
            if (in_array('ALL', $rolePermissions)) {
                $rolePermissions = Permission::getValues();
            }

            // Check if the role has been given the default permission
            foreach ($rolePermissions as $permission) {
                $this->assertTrue($roleModel->hasPermissionTo($permission));
            }
        }
    }
}
