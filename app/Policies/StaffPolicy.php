<?php

namespace App\Policies;

use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('staff.view');
    }

    public function view(User $user, User $staff): bool
    {
        return $user->hasPermission('staff.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('staff.create');
    }

    public function update(User $user, User $staff): bool
    {
        return $user->hasPermission('staff.update');
    }

    public function deactivate(User $user, User $staff): bool
    {
        return $user->hasPermission('staff.deactivate');
    }
}
