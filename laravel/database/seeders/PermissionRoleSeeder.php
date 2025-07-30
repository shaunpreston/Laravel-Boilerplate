<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userPermissions = collect([
            $userPermission = Permission::create(['name' => 'manage_account'])
        ]);

        $adminPermissions = collect([
            $userPermission,
            Permission::create(['name' => 'manage_users']),
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
        ]);
        $adminRole->addPermissions($adminPermissions);

        $userRole = Role::create([
            'name' => 'user',
        ]);
        $userRole->addPermissions($userPermissions);
    }
}
