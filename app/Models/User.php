<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_active', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $key): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn (Role $role): bool => $role->key === $key);
        }

        return $this->roles()->where('key', $key)->exists();
    }

    public function hasPermission(string $key): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles
                ->loadMissing('permissions')
                ->contains(fn (Role $role): bool => $role->permissions->contains(fn (Permission $permission): bool => $permission->key === $key));
        }

        return $this->roles()->whereHas('permissions', fn ($query) => $query->where('key', $key))->exists();
    }

    public function isOwnerAdmin(): bool
    {
        return $this->hasRole(Role::OWNER_ADMIN);
    }
}
