<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private ?User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_assigned_role(): void
    {
        $this->assertEmpty($this->user->roles);

        $role = Role::factory()->create(['name' => 'admin']);
        $this->user->addRole($role);
        $this->user = $this->user->fresh();

        $this->assertNotEmpty($this->user->roles);
        $this->assertTrue($this->user->hasRole($role->name));
    }

    public function test_user_assigned_multiple_roles(): void
    {
        $this->assertEmpty($this->user->roles);

        $roles = Role::factory()->count(3)->create();
        $this->user->addRole($roles->first());
        $this->user->addRole($roles->last());
        $this->user = $this->user->fresh();

        $this->assertNotEmpty($this->user->roles);
        $this->assertCount(2, $this->user->roles);
        $this->assertTrue($this->user->hasRole($roles->first()->name));
        $this->assertTrue($this->user->hasRole($roles->last()->name));
    }

    public function test_user_has_admin_permission(): void
    {
        Route::get('test-manage-users', function () {
            return 'Super Admin';
        })->middleware('can:test_super_admin');

        $this->assertFalse($this->user->can('test_super_admin'));
        $response = $this->actingAs($this->user)->get('/test-manage-users');
        $response->assertStatus(403);

        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['name' => 'test_super_admin']);
        $role->addPermission($permission);
        $this->user->addRole($role);
        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->can('test_super_admin'));
        $response = $this->actingAs($this->user)->get('/test-manage-users');

        $response->assertStatus(200);
        $response->assertSee('Super Admin');
    }

    public function test_user_permissions_updated(): void
    {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();
        $this->user->addRole($role);

        $this->assertFalse($this->user->can($permission->name));

        $role->addPermission($permission);
        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->can($permission->name));
    }

    public function test_role_permissions_added(): void
    {
        $role = Role::factory()->create();
        $this->user->addRole($role);

        $permissions = Permission::factory()->count(3)->create();
        $role->addPermissions($permissions);
        $newPermissions = Permission::factory()->count(2)->create();

        $this->assertTrue($this->user->can($permissions->first()->name));
        $this->assertFalse($this->user->can($newPermissions->first()->name));
        $this->assertCount(3, $role->permissions);

        $role->addPermissions($newPermissions);
        $this->user = $this->user->fresh();
        $role = $role->fresh();

        $this->assertTrue($this->user->can($permissions->first()->name));
        $this->assertTrue($this->user->can($newPermissions->first()->name));
        $this->assertCount(5, $role->permissions);
    }

    public function test_role_permissions_replaced(): void
    {
        $role = Role::factory()->create();
        $this->user->addRole($role);

        $permissions = Permission::factory()->count(3)->create();
        $role->addPermissions($permissions);
        $newPermissions = Permission::factory()->count(2)->create();

        $this->assertTrue($this->user->can($permissions->first()->name));
        $this->assertFalse($this->user->can($newPermissions->first()->name));
        $this->assertCount(3, $role->permissions);

        $role->addPermissions($newPermissions, true);
        $this->user = $this->user->fresh();
        $role = $role->fresh();

        $this->assertFalse($this->user->can($permissions->first()->name));
        $this->assertTrue($this->user->can($newPermissions->first()->name));
        $this->assertCount(2, $role->permissions);
    }
}
