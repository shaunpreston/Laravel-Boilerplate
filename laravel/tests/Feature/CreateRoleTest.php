<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateRoleTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->permissions = Permission::factory(2)->create();
        $this->permissionNames = $this->permissions->pluck('name')->toArray();
    }

    public function test_create_new_role_command_with_permissions(): void
    {
        $roleName = 'test_super_admin';
        $randomPermission = $this->permissions->pluck('name')->random();
        $newPermission = 'test_manage_super';

        $this->artisan('app:create-role')
            ->expectsQuestion('What is the role name?', $roleName)
            ->expectsConfirmation("Do you wish to add permissions to {$roleName}?", 'yes')
            ->expectsChoice('Which permissions would you like to assign?', [$randomPermission], $this->permissionNames)
            ->expectsConfirmation('Do you wish to add any new permissions?', 'yes')
            ->expectsQuestion('Enter the new permissions (csv)', $newPermission)
            ->expectsConfirmation('Do you wish to replace current permissions?', 'yes')
            ->expectsOutput("{$roleName} assigned permissions {$randomPermission},$newPermission")
            ->assertExitCode(0);

        $savedRole = Role::where('name', $roleName)->first();
        $savedPermissions = $savedRole->permissions->pluck('name');

        $this->assertNotNull($savedRole);
        $this->assertTrue($savedPermissions->contains($randomPermission));
        $this->assertTrue($savedPermissions->contains($newPermission));
    }

    public function test_update_current_role_with_new_permissions(): void
    {
        $role = Role::create([
            'name' => 'test_new_admin_role'
        ]);
        $randomPermission = $this->permissions->random();
        $role->addPermission($randomPermission);
        $newPermission = 'test_manage_super';

        $this->artisan('app:create-role')
            ->expectsQuestion('What is the role name?', $role->name)
            ->expectsConfirmation("Do you wish to add permissions to {$role->name}?", 'yes')
            ->expectsChoice('Which permissions would you like to assign?', [], $this->permissionNames)
            ->expectsConfirmation('Do you wish to add any new permissions?', 'yes')
            ->expectsQuestion('Enter the new permissions (csv)', $newPermission)
            ->expectsConfirmation('Do you wish to replace current permissions?', 'no')
            ->expectsOutput("{$role->name} assigned permissions $newPermission")
            ->assertExitCode(0);

        $role = $role->fresh();

        $this->assertTrue($role->permissions->contains($randomPermission));
        $this->assertTrue($role->permissions->pluck('name')->contains($newPermission));
    }
}
