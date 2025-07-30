<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class SetUserRole extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-user-role {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the roles of a given user.';

    /**
     * Execute the console command.
     */
    public function handle(): mixed
    {
        $userId = $this->argument('user');
        $roles = Role::all();
        $roleNames = $roles->map(function ($role) {
            return $role->name;
        })->toArray();

        $user = User::find($userId);

        if (! $user) {
            $this->error("User not found with id {$userId}");

            return 1;
        }

        if ($this->confirm("Do you wish to update roles for {$user->name}?")) {
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

            if ($this->confirm("Do you wish to replace current roles?")) {
                $user->addRoles($rolesToAdd, replace: true);
            } else {
                $user->addRoles($rolesToAdd);
            }

            $rolesAdded = implode(',', $selectedRoles);
            $this->info("{$user->name} assigned role {$rolesAdded}");
        }

        return 0;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'user' => 'Please provide a user ID.',
        ];
    }
}
