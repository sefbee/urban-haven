<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'is_active', 'last_login_at'])]
#[Hidden(['password', 'remember_token', 'mfa_secret', 'mfa_recovery_codes'])]
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
            'mfa_secret' => 'encrypted',
            'mfa_recovery_codes' => 'encrypted:array',
            'mfa_enabled_at' => 'datetime',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
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

    /**
     * Sales users only ever see leads assigned to them; owners see everything.
     */
    public function canSeeAllLeads(): bool
    {
        return $this->isOwnerAdmin() || $this->hasPermission('lead.view_all');
    }

    public function hasMfaEnabled(): bool
    {
        return $this->mfa_enabled_at !== null && filled($this->mfa_secret);
    }

    public function requiresMfa(): bool
    {
        return false;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeSalesStaff(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('roles', fn (Builder $roles) => $roles->whereIn('key', [Role::SALES_USER, Role::OWNER_ADMIN]));
    }
}
