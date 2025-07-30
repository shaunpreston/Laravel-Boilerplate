<?php

namespace App\Models\Traits;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasPermissions
{
    protected function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->with('permissions')
            ->withTimestamps();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->map(function ($item) {
            return $item->name;
        })->contains($role);
    }

    public function addRole(Role $role): void
    {
        $this->roles()->attach([
            $role->id,
        ]);
    }

    public function addRoles(iterable $roles, bool $replace = false): void
    {
        $this->roles()->sync(
            $roles->pluck('id'),
            $replace,
        );
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->roles->map(function ($role) {
            return $role->permissions->map(function ($per) {
                return $per->name;
            });
        })
            ->flatten()
            ->unique();

        return $permissions->contains($permission);
    }
}
