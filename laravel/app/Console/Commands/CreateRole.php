<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;

class CreateRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleName = $this->ask('What is the role name?');

        $role = Role::firstOrCreate(['name' => $roleName]);

        if ($this->confirm("Do you wish to add permissions to {$role->name}?")) {
            $permissions = Permission::all();
            $permissionNames = $permissions->map(function ($permission) {
                return $permission->name;
            })->toArray();

            $selectedPermissions = $this->choice(
                'Which permissions would you like to assign?',
                $permissionNames,
                multiple: true,
            );

            $permissionsToAdd = $permissions->filter(function ($permission) use ($selectedPermissions) {
                if (in_array($permission->name, $selectedPermissions)) {
                    return $permission;
                };
            });

            if ($this->confirm("Do you wish to add any new permissions?")) {
                $newPermissions = $this->ask("Enter the new permissions (csv)");
                if ($newPermissions) {
                    $newPermissions = explode(',', $newPermissions);
                    foreach ($newPermissions as $newPermission) {
                        $createdPermission = Permission::create(['name' => $newPermission]);
                        if ($createdPermission) {
                            $permissionsToAdd->add($createdPermission);
                        }
                    }
                }
            }

            if ($this->confirm("Do you wish to replace current permissions?")) {
                $role->addPermissions($permissionsToAdd, replace: true);
            } else {
                $role->addPermissions($permissionsToAdd);
            }

            $permissionsAdded = implode(',', $permissionsToAdd->pluck(['name'])->toArray());
            $this->info("{$role->name} assigned permissions {$permissionsAdded}");
        }
    }
}
