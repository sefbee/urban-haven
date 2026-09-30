<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('lead.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->hasPermission('lead.view');
    }

    public function assign(User $user, Lead $lead): bool
    {
        return $user->hasPermission('lead.assign');
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($user->hasRole(Role::SALES_USER) && $lead->assigned_to !== $user->id) {
            return false;
        }

        return $user->hasPermission('lead.update') || $user->hasPermission('lead.view');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('lead.export');
    }
}
