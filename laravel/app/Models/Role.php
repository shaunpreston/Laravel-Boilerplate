<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => strtolower($value),
        );
    }

    public function permissions(): BelongsToMany
    {
        return $this->BelongsToMany(Permission::class)->using(PermissionRole::class)->withTimestamps();
    }

    public function addPermission(Permission $permission): void
    {
        $this->permissions()->attach([
            $permission->id,
        ]);
    }

    public function addPermissions(iterable $permissions, bool $replace = false): void
    {
        $this->permissions()->sync($permissions->pluck('id'), $replace);
    }
}
