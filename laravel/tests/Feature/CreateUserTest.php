<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->make();
        $this->roles = Role::factory(2)->create();
        $this->roleNames = $this->roles->pluck('name')->toArray();
    }

    public function test_create_user_command(): void
    {
        $randomRole = $this->roles->pluck('name')->random();

        $this->artisan('app:create-user')
            ->expectsQuestion('What is the email address?', $this->user->email)
            ->expectsQuestion('What is the name?', $this->user->name)
            ->expectsConfirmation('Do you wish to add roles to the user?', 'yes')
            ->expectsChoice('Which roles would you like to assign?', [$randomRole], $this->roleNames)
            ->expectsOutput("{$this->user->email} assigned roles {$randomRole}")
            ->expectsOutputToContain("Password:")
            ->assertExitCode(0);

        $savedUser = User::where('email', $this->user->email)->first();

        $this->assertNotNull($savedUser);
        $this->assertEquals($this->user->email, $savedUser->email);
        $this->assertEquals($this->user->name, $savedUser->name);
        $this->assertTrue($savedUser->hasRole($randomRole));
    }

    public function test_create_user_command_without_role(): void
    {
        $randomRole = $this->roles->pluck('name')->random();

        $this->artisan('app:create-user')
            ->expectsQuestion('What is the email address?', $this->user->email)
            ->expectsQuestion('What is the name?', $this->user->name)
            ->expectsConfirmation('Do you wish to add roles to the user?', 'no')
            ->expectsOutputToContain("Password:")
            ->assertExitCode(0);

        $savedUser = User::where('email', $this->user->email)->first();

        $this->assertNotNull($savedUser);
        $this->assertEquals($this->user->email, $savedUser->email);
        $this->assertEquals($this->user->name, $savedUser->name);
        $this->assertFalse($savedUser->hasRole($randomRole));
    }
}
