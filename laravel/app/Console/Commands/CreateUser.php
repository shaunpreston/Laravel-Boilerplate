<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userEmail = $this->ask('What is the email address?');
        $userName = $this->ask('What is the name?');

        if (! $userEmail) {
            $this->error("No email was entered.");

            return 1;
        }

        $user = User::where('email', $userEmail)->first();
        if ($user) {
            $this->error("User already exists.");

            return 1;
        }

        $roles = Role::all();
        $roleNames = $roles->map(function ($role) {
            return $role->name;
        })->toArray();

        $password = Str::random(32);

        $user = User::create([
            'email' => $userEmail,
            'name' => $userName,
            'password' => Hash::make($password),
        ]);

        if ($this->confirm("Do you wish to add roles to the user?")) {
            $selectedRoles = $this->choice(
                'Which roles would you like to assign?',
                $roleNames,
                multiple: true,
            );

            $rolesToAdd = $roles->filter(function ($role) use ($selectedRoles) {
                if (in_array($role->name, $selectedRoles)) {
                    return $role;
                };
            });
            $user->addRoles($rolesToAdd);

            $rolesAdded = implode(',', $selectedRoles);
            $this->info("{$user->email} assigned roles {$rolesAdded}");
        }

        $this->info("Password: {$password}");

        return 0;
    }
}
