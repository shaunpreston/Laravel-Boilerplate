<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->roles = Role::factory(2)->create();
        $this->roleNames = $this->roles->pluck('name')->toArray();
    }

    public function test_update_user_role_command(): void
    {
        $randomRole = $this->roles->pluck('name')->random();

        $this->artisan("app:set-user-role {$this->user->id}")
            ->expectsConfirmation("Do you wish to update roles for {$this->user->name}?", 'yes')
            ->expectsChoice('Which roles would you like to assign?', [$randomRole], $this->roleNames)
            ->expectsConfirmation('Do you wish to replace current roles?', 'yes')
            ->expectsOutput("{$this->user->name} assigned role {$randomRole}")
            ->assertExitCode(0);

        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->hasRole($randomRole));
    }

    public function test_change_user_roles(): void
    {
        $randomRole = $this->roles->random();
        $this->user->addRoles($this->roles);
        $this->assertCount(2, $this->user->roles);

        $this->artisan("app:set-user-role {$this->user->id}")
            ->expectsConfirmation("Do you wish to update roles for {$this->user->name}?", 'yes')
            ->expectsChoice('Which roles would you like to assign?', [$randomRole->name], $this->roleNames)
            ->expectsConfirmation('Do you wish to replace current roles?', 'yes')
            ->expectsOutput("{$this->user->name} assigned role {$randomRole->name}")
            ->assertExitCode(0);

        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->hasRole($randomRole->name));
        $this->assertCount(1, $this->user->roles);
    }
}
